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
        // If request_id is provided in the URL, pre-select that request
        $selectedRequest = null;
        if ($request->filled('request_id')) {
            $selectedRequest = RequestMaterial::with(['material'])
                ->where('status', 'approved')
                ->findOrFail($request->request_id);
                
            // Check if return is allowed
            if (auth()->user()->hasRole('produksi') && $selectedRequest->requested_by != auth()->id()) {
                abort(403, 'Anda hanya dapat mengembalikan material yang Anda minta.');
            }
        }
        
        // If material_id is provided, get the latest approved request for that material
        if (!$selectedRequest && $request->filled('material_id')) {
            $selectedRequest = RequestMaterial::with(['material'])
                ->where('material_id', $request->material_id)
                ->where('status', 'approved')
                ->when(auth()->user()->hasRole('produksi'), function($query) {
                    $query->where('requested_by', auth()->id());
                })
                ->latest()
                ->first();
        }
        
        // Get all eligible requests for this user (only approved requests)
        $eligibleRequests = RequestMaterial::with([
                'material',
                'returns' => fn($q) => $q->where('status', 'approved'),
            ])
            ->where('status', 'approved')
            ->when(auth()->user()->hasRole('produksi'), function($query) {
                $query->where('requested_by', auth()->id());
            })
            ->whereDoesntHave('returns', function($query) {
                $query->where('status', 'pending');
            })
            ->get()
            ->map(function($req) {
                // Remaining for THIS request (original minus its approved returns)
                $perRequestRemaining = $req->quantity - $req->returns->sum('quantity');

                // Physical stock the user still holds for this material
                // (requested - returned - consumed). Cannot return what was already used.
                $available = $this->availableProductionStock($req->material_id, $req->requested_by);

                $req->returnable_quantity = min($perRequestRemaining, $available);
                return $req;
            })
            ->filter(fn($req) => $req->returnable_quantity > 0)
            ->values();

        return view('admin.returns.create', compact('eligibleRequests', 'selectedRequest'));
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
        
        // Returnable = min(remaining for this request, physical stock still held).
        // Physical stock subtracts what was already consumed, so used material cannot be returned.
        $alreadyReturned = $requestMaterial->returns()->where('status', 'approved')->sum('quantity');
        $perRequestRemaining = $requestMaterial->quantity - $alreadyReturned;
        $available = $this->availableProductionStock($requestMaterial->material_id, $requestMaterial->requested_by);
        $returnableQuantity = min($perRequestRemaining, $available);

        if ($validated['quantity'] > $returnableQuantity) {
            return redirect()->back()->withInput()->withErrors([
                'quantity' => "Jumlah pengembalian tidak boleh lebih dari sisa stok yang tersedia ({$returnableQuantity} {$requestMaterial->material->unit})."
            ]);
        }
        
        // Check if there's already a pending return for this request
        if ($requestMaterial->returns()->where('status', 'pending')->exists()) {
            return redirect()->back()->withInput()->withErrors([
                'request_id' => 'Sudah ada pengembalian yang menunggu persetujuan untuk permintaan ini.'
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
        $return->load(['request.material.category', 'returner', 'approver']);
        
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
        
        DB::transaction(function() use ($returnMaterial) {
            // Get material and stock
            $material = $returnMaterial->request->material;
            $stock = $material->stock ?? Stock::create(['material_id' => $material->id, 'quantity' => 0]);
            
            // Record current quantity
            $quantityBefore = $stock->quantity;
            
            // Calculate new quantity
            $newQuantity = $quantityBefore + $returnMaterial->quantity;
            
            // Update stock
            $stock->update(['quantity' => $newQuantity]);
            
            // Record stock adjustment
            StockAdjustment::create([
                'material_id' => $material->id,
                'user_id' => auth()->id(),
                'quantity_before' => $quantityBefore,
                'adjustment_quantity' => $returnMaterial->quantity,
                'quantity_after' => $newQuantity,
                'type' => 'return',
                'notes' => "Pengembalian #{$returnMaterial->return_number} disetujui"
            ]);
            
            // Update return status
            $returnMaterial->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);
        });
        
        return redirect()->route('admin.returns.approvals')
            ->with('success', 'Pengembalian material berhasil disetujui.');
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