<?php

namespace App\Http\Controllers;

use App\Models\RequestMaterial;
use App\Models\Material;
use App\Models\Stock;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class RequestMaterialController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware('permission:view-requests')->only(['index', 'show']);
        $this->middleware('permission:create-requests')->only(['create', 'store']);
        $this->middleware('permission:approve-requests')->only(['approvals', 'approve', 'reject']);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = RequestMaterial::with(['material.category', 'requester', 'approver'])
            ->when(auth()->user()->hasRole('produksi'), function($query) {
                // Produksi can only see their own requests
                $query->where('requested_by', auth()->id());
            });
            
        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('request_number', 'like', "%{$request->search}%")
                  ->orWhereHas('material', function($q) use ($request) {
                      $q->where('name', 'like', "%{$request->search}%");
                  });
            });
        }
        
        if ($request->filled('category')) {
            $query->whereHas('material', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }
        
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests = $query->latest()->paginate(10);
        $categories = \App\Models\Category::orderBy('name')->get();
        
        return view('admin.requests.index', compact('requests', 'categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $materials = Material::with(['stock', 'category'])
            ->orderBy('name')
            ->get();
            
        return view('admin.requests.create', compact('materials'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string'
        ]);
        
        $material = Material::with('stock')->findOrFail($validated['material_id']);
        
        // Check if stock is sufficient
        if (!$material->stock || $material->stock->quantity < $validated['quantity']) {
            $availableStock = $material->stock ? $material->stock->quantity : 0;
            return redirect()->back()->withInput()->with('error', 
                "❌ Permintaan jumlah melebihi stok yang tersedia!\n" .
                "Jumlah diminta: {$validated['quantity']} {$material->unit}\n" .
                "Stok tersedia: {$availableStock} {$material->unit}"
            )->withErrors(['quantity' => "Stok material tidak mencukupi"]);
        }
        
        // Generate unique request number
        $requestNumber = 'REQ-' . strtoupper(Str::random(8));
        while (RequestMaterial::where('request_number', $requestNumber)->exists()) {
            $requestNumber = 'REQ-' . strtoupper(Str::random(8));
        }
        
        RequestMaterial::create([
            'request_number' => $requestNumber,
            'requested_by' => auth()->id(),
            'material_id' => $validated['material_id'],
            'quantity' => $validated['quantity'],
            'notes' => $validated['notes'],
            'status' => 'pending'
        ]);
        
        return redirect()->route('admin.requests.index')
            ->with('success', 'Permintaan material berhasil dibuat dan sedang menunggu persetujuan.');
    }

    /**
     * Display the specified resource.
     */
    public function show(RequestMaterial $request)
    {
        $request->load(['material.category', 'requester', 'approver', 'returns']);
        
        // Check if user can view this request
        if (auth()->user()->hasRole('produksi') && $request->requested_by != auth()->id()) {
            abort(403, 'Anda tidak memiliki akses untuk melihat permintaan ini.');
        }
        
        return view('admin.requests.show', compact('request'));
    }

    /**
     * Display list of requests waiting for approval
     */
    public function approvals(Request $request)
    {
        // Only users with approve-requests permission can view this
        if (!auth()->user()->can('approve-requests')) {
            abort(403);
        }
        
        $query = RequestMaterial::where('status', 'pending')
            ->with(['material.category', 'requester']);
            
        // Filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('request_number', 'like', "%{$request->search}%")
                  ->orWhereHas('material', function($q) use ($request) {
                      $q->where('name', 'like', "%{$request->search}%");
                  });
            });
        }
        
        if ($request->filled('category')) {
            $query->whereHas('material', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }
        
        $pendingRequests = $query->latest()->paginate(10);
        $categories = \App\Models\Category::orderBy('name')->get();
        
        return view('admin.requests.approvals', compact('pendingRequests', 'categories'));
    }

    /**
     * Approve a request
     */
    public function approve(Request $request, RequestMaterial $requestMaterial)
    {
        // Only users with approve-requests permission and NOT store role can approve
        if (!auth()->user()->can('approve-requests') || auth()->user()->hasRole('store')) {
            abort(403, 'Anda tidak memiliki izin untuk menyetujui permintaan material.');
        }
        
        // Request must be in 'pending' status
        if ($requestMaterial->status !== 'pending') {
            return redirect()->back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }
        
        DB::transaction(function() use ($requestMaterial) {
            // Get material and stock
            $material = $requestMaterial->material;
            $stock = $material->stock ?? Stock::create(['material_id' => $material->id, 'quantity' => 0]);
            
            // Record current quantity
            $quantityBefore = $stock->quantity;
            
            // Calculate new quantity
            $newQuantity = $quantityBefore - $requestMaterial->quantity;
            
            // Check if sufficient stock
            if ($newQuantity < 0) {
                throw new \Exception('Stok tidak mencukupi untuk menyetujui permintaan ini.');
            }
            
            // Update stock
            $stock->update(['quantity' => $newQuantity]);
            
            // Record stock adjustment
            StockAdjustment::create([
                'material_id' => $material->id,
                'user_id' => auth()->id(),
                'quantity_before' => $quantityBefore,
                'adjustment_quantity' => -$requestMaterial->quantity,
                'quantity_after' => $newQuantity,
                'type' => 'request',
                'notes' => "Permintaan #{$requestMaterial->request_number} disetujui"
            ]);
            
            // Update request status
            $requestMaterial->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now()
            ]);
        });
        
        return redirect()->route('admin.requests.approvals')
            ->with('success', 'Permintaan material berhasil disetujui.');
    }

    /**
     * Reject a request
     */
    public function reject(Request $request, RequestMaterial $requestMaterial)
    {
        // Only users with approve-requests permission and NOT store role can reject
        if (!auth()->user()->can('approve-requests') || auth()->user()->hasRole('store')) {
            abort(403, 'Anda tidak memiliki izin untuk menolak permintaan material.');
        }
        
        // Request must be in 'pending' status
        if ($requestMaterial->status !== 'pending') {
            return redirect()->back()->with('error', 'Permintaan ini sudah diproses sebelumnya.');
        }
        
        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:255'
        ]);
        
        // Update request status
        $requestMaterial->update([
            'status' => 'rejected',
            'notes' => ($requestMaterial->notes ? $requestMaterial->notes . "\n\n" : '') . 
                      "Alasan penolakan: {$validated['rejection_reason']}",
            'approved_by' => auth()->id(),
            'approved_at' => now()
        ]);
        
        return redirect()->route('admin.requests.approvals')
            ->with('success', 'Permintaan material berhasil ditolak.');
    }
}