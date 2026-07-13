<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Stock;
use App\Models\Material;
use App\Models\MaterialConsumption;
use App\Models\StockAdjustment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware('permission:view-stocks')->only(['index', 'show']);
        $this->middleware('permission:edit-stocks')->only(['adjustForm', 'adjust']);
    }

    public function index(Request $request)
    {
        // Check if user is produksi role
        $isProduksi = auth()->user()->hasRole('produksi');
        
        if ($isProduksi) {
            // For produksi: show all materials with personalized stock (based on their requests/returns)
            $query = Material::with('category')
                ->when($request->filled('search'), function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                })
                ->when($request->filled('category'), function($q) use ($request) {
                    $q->where('category_id', $request->category);
                });

            $materials = $query->orderBy('name')->get();
            
            // Calculate personal stock for each material
            $stocks = $materials->map(function($material) {
                $userId = auth()->id();
                
                // Total approved requests
                $totalRequested = \App\Models\RequestMaterial::where('material_id', $material->id)
                    ->where('requested_by', $userId)
                    ->where('status', 'approved')
                    ->whereNotNull('received_at') // hanya barang yang sudah diterima produksi
                    ->sum('quantity');
                
                // Total approved returns
                $totalReturned = \App\Models\ReturnMaterial::whereHas('request', function($q) use ($material, $userId) {
                        $q->where('material_id', $material->id)
                          ->where('requested_by', $userId);
                    })
                    ->where('status', 'approved')
                    ->sum('quantity');

                // Total consumption recorded by the user
                $totalConsumed = MaterialConsumption::where('material_id', $material->id)
                    ->where('consumed_by', $userId)
                    ->sum('quantity');
                
                // Personal stock = requested - returned - consumed
                $personalStock = max(0, $totalRequested - $totalReturned - $totalConsumed);
                
                return (object)[
                    'id' => $material->id,
                    'material_id' => $material->id,
                    'material' => $material,
                    'quantity' => $personalStock,
                ];
            });
            
            // Filter out materials with 0 stock (only show materials user has)
            $stocks = $stocks->filter(function($stock) {
                return $stock->quantity > 0;
            });
            
            // Apply stock filter
            if ($request->filled('stock')) {
                if ($request->stock === 'low') {
                    $stocks = $stocks->filter(function($stock) {
                        return $stock->quantity <= 10 && $stock->quantity > 0;
                    });
                } elseif ($request->stock === 'out') {
                    $stocks = $stocks->filter(function($stock) {
                        return $stock->quantity == 0;
                    });
                }
            }
            
            // Sort by quantity
            $stocks = $stocks->sortBy('quantity')->values();
            
            // Manual pagination
            $perPage = 10;
            $currentPage = $request->get('page', 1);
            $total = $stocks->count();
            $stocks = $stocks->slice(($currentPage - 1) * $perPage, $perPage);
            
            $stocks = new \Illuminate\Pagination\LengthAwarePaginator(
                $stocks,
                $total,
                $perPage,
                $currentPage,
                ['path' => $request->url(), 'query' => $request->query()]
            );
            
        } else {
            $isWarehouseView = $request->input('view') !== 'produksi';

            if ($isWarehouseView) {
                // For staff/store: show actual warehouse stock
                $query = Stock::with('material.category')
                    ->whereHas('material', function($q) use ($request) {
                        if ($request->filled('search')) {
                            $q->where('name', 'like', '%' . $request->search . '%');
                        }
                        if ($request->filled('category')) {
                            $q->where('category_id', $request->category);
                        }
                    });

                if ($request->filled('stock')) {
                    if ($request->stock === 'low') {
                        $query->where('quantity', '<=', 10)->where('quantity', '>', 0);
                    } elseif ($request->stock === 'out') {
                        $query->where('quantity', '=', 0);
                    }
                }

                $stocks = $query->orderBy('quantity')->paginate(10);
            } else {
                // For staff/store: show production stock across approved requests and returns
                $materials = Material::with('category')
                    ->when($request->filled('search'), function($q) use ($request) {
                        $q->where('name', 'like', '%' . $request->search . '%');
                    })
                    ->when($request->filled('category'), function($q) use ($request) {
                        $q->where('category_id', $request->category);
                    })
                    ->orderBy('name')
                    ->get();

                $stocks = $materials->map(function($material) {
                    $totalRequested = \App\Models\RequestMaterial::where('material_id', $material->id)
                        ->where('status', 'approved')
                        ->whereNotNull('received_at') // hanya barang yang sudah diterima produksi
                        ->sum('quantity');

                    $totalReturned = \App\Models\ReturnMaterial::whereHas('request', function($q) use ($material) {
                            $q->where('material_id', $material->id);
                        })
                        ->where('status', 'approved')
                        ->sum('quantity');

                    $totalConsumed = MaterialConsumption::where('material_id', $material->id)
                        ->sum('quantity');

                    return (object)[
                        'id' => $material->id,
                        'material_id' => $material->id,
                        'material' => $material,
                        'quantity' => max(0, $totalRequested - $totalReturned - $totalConsumed),
                    ];
                });

                if ($request->filled('stock')) {
                    if ($request->stock === 'low') {
                        $stocks = $stocks->filter(function($stock) {
                            return $stock->quantity <= 10 && $stock->quantity > 0;
                        });
                    } elseif ($request->stock === 'out') {
                        $stocks = $stocks->filter(function($stock) {
                            return $stock->quantity == 0;
                        });
                    }
                }

                $stocks = $stocks->filter(function($stock) {
                    return $stock->quantity > 0;
                })->sortBy('quantity')->values();

                $perPage = 10;
                $currentPage = $request->get('page', 1);
                $total = $stocks->count();
                $stocks = $stocks->slice(($currentPage - 1) * $perPage, $perPage);

                $stocks = new \Illuminate\Pagination\LengthAwarePaginator(
                    $stocks,
                    $total,
                    $perPage,
                    $currentPage,
                    ['path' => $request->url(), 'query' => $request->query()]
                );
            }
        }
        
        $categories = Category::orderBy('name')->get();
        
        return view('admin.stocks.index', compact('stocks', 'categories'));
    }

    public function show(Material $material)
    {
        $stock = $material->stock;
        $adjustments = StockAdjustment::with(['user'])
            ->where('material_id', $material->id)
            ->latest()
            ->paginate(10);
            
        return view('admin.stocks.show', compact('material', 'stock', 'adjustments'));
    }

    public function adjustForm(Request $request)
    {
        $materials = Material::with(['stock', 'category'])
            ->when($request->material_id, function($query) use ($request) {
                $query->where('id', $request->material_id);
            })
            ->orderBy('name')
            ->get();
            
        return view('admin.stocks.adjust', compact('materials'));
    }

    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'adjustment' => 'required|numeric|not_in:0',
            'notes' => 'nullable|string'
        ]);

        DB::transaction(function () use ($validated) {
            $stock = Stock::firstOrCreate(
                ['material_id' => $validated['material_id']],
                ['quantity' => 0]
            );

            // Record current quantity
            $quantityBefore = $stock->quantity;

            // Calculate new quantity
            $newQuantity = $quantityBefore + $validated['adjustment'];
            
            // Prevent negative stock
            if ($newQuantity < 0) {
                throw ValidationException::withMessages([
                    'adjustment' => ['Stok tidak boleh kurang dari 0']
                ]);
            }

            // Update stock
            $stock->update(['quantity' => $newQuantity]);

            // Record adjustment history
            StockAdjustment::create([
                'material_id' => $validated['material_id'],
                'user_id' => auth()->id(),
                'quantity_before' => $quantityBefore,
                'adjustment_quantity' => $validated['adjustment'],
                'quantity_after' => $newQuantity,
                'type' => 'manual',
                'notes' => $validated['notes']
            ]);
        });

        return redirect()->route('admin.stocks.show', $validated['material_id'])
            ->with('success', 'Stok berhasil disesuaikan.');
    }

    public function history(Request $request)
    {
        $query = StockAdjustment::with(['material.category', 'user'])
            ->when($request->filled('search'), function($q) use ($request) {
                $q->whereHas('material', function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                });
            })
            ->when($request->filled('category'), function($q) use ($request) {
                $q->whereHas('material', function($q) use ($request) {
                    $q->where('category_id', $request->category);
                });
            })
            ->when($request->filled('type'), function($q) use ($request) {
                $q->where('type', $request->type);
            });

        $adjustments = $query->latest()->paginate(15);
        $categories = Category::orderBy('name')->get();
        
        return view('admin.stocks.history', compact('adjustments', 'categories'));
    }
}