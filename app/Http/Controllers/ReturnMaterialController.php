<?php

namespace App\Http\Controllers;

use App\Models\ReturnMaterial;
use App\Models\RequestMaterial;
use App\Models\MaterialConsumption;
use App\Models\Stock;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReturnMaterialController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware('permission:view-returns')->only(['index', 'show']);
        $this->middleware('permission:create-returns')->only(['create', 'store']);
        $this->middleware('permission:approve-returns')->only(['approvals', 'approve', 'reject']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = ReturnMaterial::with(['request.material.category', 'returner', 'approver'])
            ->when(auth()->user()->hasRole('produksi'), function($query) {
                // Produksi can only see their own returns
                $query->where('returned_by', auth()->id());
            });
            
        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('return_number', 'like', "%{$request->search}%")
                  ->orWhereHas('request.material', function($q) use ($request) {
                      $q->where('name', 'like', "%{$request->search}%");
                  });
            });
        }
        
        if ($request->filled('category')) {
            $query->whereHas('request.material', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $returns = $query->latest()->paginate(10);
        $categories = \App\Models\Category::orderBy('name')->get();
        
        return view('admin.returns.index', compact('returns', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $isProduksi = auth()->user()->hasRole('produksi');

        // Kumpulkan pengembalian pada level MATERIAL. Barang yang sudah dipakai
        // tidak bisa dikembalikan, jadi patokannya stok fisik yang masih dipegang
        // (permintaan approved+received - retur - pemakaian - retur pending).
        // Tiap material diwakili satu permintaan (untuk relasi request_id).
        $requests = RequestMaterial::with(['material.category'])
            ->where('status', 'approved')
            ->whereNotNull('received_at')
            ->when($isProduksi, fn($q) => $q->where('requested_by', auth()->id()))
            ->latest()
            ->get();

        $seen = [];
        $eligibleMaterials = collect();
        foreach ($requests as $req) {
            $key = $req->material_id . '-' . $req->requested_by;
            if (isset($seen[$key])) {
                continue; // material ini (untuk user ini) sudah diwakili
            }
            $seen[$key] = true;

            $returnable = $this->materialReturnable($req->material_id, $req->requested_by);
            if ($returnable <= 0) {
                continue;
            }

            $eligibleMaterials->push((object) [
                'representative_request_id' => $req->id,
                'material'                  => $req->material,
                'returnable_quantity'       => $returnable,
                'requester_name'            => $req->requester->name ?? null,
            ]);
        }

        // Pra-pilih material bila datang dari tombol "Buat Pengembalian" pada permintaan.
        $selectedRequestId = null;
        $sourceMaterialId = null;
        if ($request->filled('request_id')) {
            $sourceMaterialId = optional(RequestMaterial::find($request->request_id))->material_id;
        } elseif ($request->filled('material_id')) {
            $sourceMaterialId = $request->material_id;
        }
        if ($sourceMaterialId) {
            $match = $eligibleMaterials->firstWhere(fn($m) => $m->material->id == $sourceMaterialId);
            $selectedRequestId = $match->representative_request_id ?? null;
        }

        return view('admin.returns.create', compact('eligibleMaterials', 'selectedRequestId', 'isProduksi'));
    }

    /**
     * Sisa material yang benar-benar bisa dikembalikan pada level material:
     * stok fisik yang dipegang user dikurangi retur yang masih pending.
     */
    private function materialReturnable($materialId, $userId)
    {
        $available = $this->availableProductionStock($materialId, $userId);

        $pending = ReturnMaterial::whereHas('request', function ($q) use ($materialId, $userId) {
                $q->where('material_id', $materialId)->where('requested_by', $userId);
            })
            ->where('status', 'pending')
            ->sum('quantity');

        return max(0, $available - $pending);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_id' => 'required|exists:request_materials,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);
        
        $requestMaterial = RequestMaterial::findOrFail($validated['request_id']);
        
        // Check if user can return this material
        if (auth()->user()->hasRole('produksi') && $requestMaterial->requested_by != auth()->id()) {
            abort(403, 'Anda hanya dapat mengembalikan material yang Anda minta.');
        }
        
        // Check if material was approved
        if ($requestMaterial->status !== 'approved') {
            return redirect()->back()->withInput()->withErrors([
                'request_id' => 'Anda hanya dapat mengembalikan material dari permintaan yang telah disetujui.'
            ]);
        }
        
        // Patokan pengembalian pada level material: stok fisik yang masih dipegang
        // user (sudah dikurangi retur disetujui, pemakaian, dan retur pending).
        $returnableQuantity = $this->materialReturnable($requestMaterial->material_id, $requestMaterial->requested_by);

        if ($returnableQuantity <= 0) {
            return redirect()->back()->withInput()->withErrors([
                'request_id' => 'Tidak ada sisa stok material ini yang dapat dikembalikan.'
            ]);
        }

        if ($validated['quantity'] > $returnableQuantity) {
            return redirect()->back()->withInput()->withErrors([
                'quantity' => "Jumlah pengembalian tidak boleh lebih dari sisa stok yang tersedia ({$returnableQuantity} {$requestMaterial->material->unit})."
            ]);
        }

        // Generate unique return number
        $returnNumber = 'RET-' . strtoupper(Str::random(8));
        while (ReturnMaterial::where('return_number', $returnNumber)->exists()) {
            $returnNumber = 'RET-' . strtoupper(Str::random(8));
        }
        
        ReturnMaterial::create([
            'return_number' => $returnNumber,
            'request_id' => $validated['request_id'],
            'returned_by' => auth()->id(),
            'quantity' => $validated['quantity'],
            'notes' => $validated['notes'],
            'status' => 'pending'
        ]);
        
        return redirect()->route('admin.returns.index')
            ->with('success', 'Pengembalian material berhasil dibuat dan sedang menunggu persetujuan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(ReturnMaterial $return)
    {
        $return->load(['request.material.category', 'returner', 'approver', 'handedOverBy', 'receivedBy', 'activities.causer']);
        
        // Check if user can view this return
        if (auth()->user()->hasRole('produksi') && $return->returned_by != auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk melihat pengembalian ini.');
        }
        
        return view('admin.returns.show', compact('return'));
    }

    /**
     * Display list of returns waiting for approval
     */
    public function approvals(Request $request)
    {
        // Only users with approve-returns permission can view this
        if (!auth()->user()->can('approve-returns')) {
            abort(403);
        }
        
        $query = ReturnMaterial::where('status', 'pending')
            ->with(['request.material.category', 'returner', 'request']);
            
        // Filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('return_number', 'like', "%{$request->search}%")
                  ->orWhereHas('request.material', function($q) use ($request) {
                      $q->where('name', 'like', "%{$request->search}%");
                  });
            });
        }
        
        if ($request->filled('category')) {
            $query->whereHas('request.material', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }
        
        $pendingReturns = $query->latest()->paginate(10);
        $categories = \App\Models\Category::orderBy('name')->get();
        
        return view('admin.returns.approvals', compact('pendingReturns', 'categories'));
    }

    /**
     * Approve a return
     */
    public function approve(Request $request, ReturnMaterial $returnMaterial)
    {
        // Only users with approve-returns permission and NOT store role can approve
        if (!auth()->user()->can('approve-returns') || auth()->user()->hasRole('store')) {
            abort(403, 'Anda tidak memiliki izin untuk menyetujui pengembalian material.');
        }
        
        // Return must be in 'pending' status
        if ($returnMaterial->status !== 'pending') {
            return redirect()->back()->with('error', 'Pengembalian ini sudah diproses sebelumnya.');
        }

        // Persetujuan TIDAK menambah stok. Stok utama baru bertambah saat barang
        // benar-benar diterima oleh store (lihat receive()).
        $returnMaterial->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);

        return redirect()->route('admin.returns.approvals')
            ->with('success', 'Pengembalian material berhasil disetujui. Menunggu penyerahan barang oleh produksi.');
    }

    /**
     * Serah terima: produksi menyerahkan/mengembalikan barang.
     */
    public function handover(Request $request, ReturnMaterial $returnMaterial)
    {
        // Hanya produksi peminta yang dapat mengembalikan barangnya sendiri
        if (!auth()->user()->hasRole('produksi') || $returnMaterial->returned_by != auth()->id()) {
            abort(403, 'Anda hanya dapat menyerahkan pengembalian milik Anda sendiri.');
        }

        // Pengembalian harus sudah disetujui dan belum diserahkan
        if ($returnMaterial->status !== 'approved') {
            return redirect()->back()->with('error', 'Barang hanya dapat diserahkan untuk pengembalian yang sudah disetujui.');
        }
        if ($returnMaterial->handed_over_at) {
            return redirect()->back()->with('error', 'Barang untuk pengembalian ini sudah diserahkan sebelumnya.');
        }

        $returnMaterial->update([
            'handed_over_by' => auth()->id(),
            'handed_over_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Barang berhasil diserahkan. Menunggu konfirmasi penerimaan oleh store.');
    }

    /**
     * Serah terima: store menerima barang. Stok utama bertambah otomatis.
     */
    public function receive(Request $request, ReturnMaterial $returnMaterial)
    {
        // Hanya role store yang menerima barang kembali
        if (!auth()->user()->hasRole('store')) {
            abort(403, 'Hanya store yang dapat menerima barang pengembalian.');
        }

        // Barang harus sudah diserahkan dan belum diterima
        if ($returnMaterial->status !== 'approved' || !$returnMaterial->handed_over_at) {
            return redirect()->back()->with('error', 'Barang belum diserahkan oleh produksi.');
        }
        if ($returnMaterial->received_at) {
            return redirect()->back()->with('error', 'Barang untuk pengembalian ini sudah diterima sebelumnya.');
        }

        DB::transaction(function() use ($returnMaterial) {
            $material = $returnMaterial->request->material;
            $stock = $material->stock ?? Stock::create(['material_id' => $material->id, 'quantity' => 0]);

            $quantityBefore = $stock->quantity;
            $newQuantity = $quantityBefore + $returnMaterial->quantity;

            $stock->update(['quantity' => $newQuantity]);

            StockAdjustment::create([
                'material_id' => $material->id,
                'user_id' => auth()->id(),
                'quantity_before' => $quantityBefore,
                'adjustment_quantity' => $returnMaterial->quantity,
                'quantity_after' => $newQuantity,
                'type' => 'return',
                'notes' => "Pengembalian #{$returnMaterial->return_number} diterima store"
            ]);

            $returnMaterial->update([
                'received_by' => auth()->id(),
                'received_at' => now(),
            ]);
        });

        return redirect()->back()->with('success', 'Barang pengembalian berhasil diterima dan stok telah bertambah.');
    }

    /**
     * Reject a return
     */
    public function reject(Request $request, ReturnMaterial $returnMaterial)
    {
        // Only users with approve-returns permission and NOT store role can reject
        if (!auth()->user()->can('approve-returns') || auth()->user()->hasRole('store')) {
            abort(403, 'Anda tidak memiliki izin untuk menolak pengembalian material.');
        }
        
        // Return must be in 'pending' status
        if ($returnMaterial->status !== 'pending') {
            return redirect()->back()->with('error', 'Pengembalian ini sudah diproses sebelumnya.');
        }
        
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:255'
        ]);
        
        // Update return status
        $returnMaterial->update([
            'status' => 'rejected',
            'notes' => ($returnMaterial->notes ? $returnMaterial->notes . "\n\n" : '') . 
                      "Alasan penolakan: {$validated['rejection_reason']}",
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);
        
        return redirect()->route('admin.returns.approvals')
            ->with('success', 'Pengembalian material berhasil ditolak.');
    }

    /**
     * Physical production stock a user still holds for a material:
     * approved requests - approved returns - recorded consumption.
     */
    private function availableProductionStock($materialId, $userId)
    {
        $totalRequested = RequestMaterial::where('material_id', $materialId)
            ->where('requested_by', $userId)
            ->where('status', 'approved')
            ->whereNotNull('received_at') // hanya barang yang sudah diterima produksi
            ->sum('quantity');

        $totalReturned = ReturnMaterial::whereHas('request', function ($q) use ($materialId, $userId) {
                $q->where('material_id', $materialId)
                  ->where('requested_by', $userId);
            })
            ->where('status', 'approved')
            ->sum('quantity');

        $totalConsumed = MaterialConsumption::where('material_id', $materialId)
            ->where('consumed_by', $userId)
            ->sum('quantity');

        return max(0, $totalRequested - $totalReturned - $totalConsumed);
    }
}