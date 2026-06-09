<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Stock;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MaterialController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware('permission:view-inventory')->only(['index', 'show']);
        $this->middleware('permission:create-inventory')->only(['create', 'store']);
        $this->middleware('permission:edit-inventory')->only(['edit', 'update']);
        $this->middleware('permission:delete-inventory')->only(['destroy']);
    }

    /**
     * Display a listing of the materials.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $query = Material::with(['stock', 'category']);

        // Apply search filter
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        // Apply unit filter
        if ($request->filled('unit')) {
            $query->where('unit', $request->unit);
        }

        // Apply category filter
        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        // Apply stock filter
        if ($request->filled('stock')) {
            if ($request->stock === 'low') {
                $query->whereHas('stock', function($q) {
                    $q->where('quantity', '<=', 10)->where('quantity', '>', 0);
                });
            } elseif ($request->stock === 'out') {
                $query->whereHas('stock', function($q) {
                    $q->where('quantity', '=', 0);
                });
            }
        }

        $materials = $query->orderBy('name')->paginate(10);
        $categories = Category::orderBy('name')->get();
        
        return view('admin.materials.index', compact('materials', 'categories'));
    }

    /**
     * Show the form for creating a new material.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.materials.create', compact('categories'));
    }

    /**
     * Store a newly created material in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'required|string|max:50',
            'critical_threshold' => 'nullable|numeric|min:0',
            'initial_stock' => 'required|numeric|min:0'
        ]);

        DB::transaction(function () use ($validated) {
            $material = Material::create([
                'name' => $validated['name'],
                'description' => $validated['description'],
                'category_id' => $validated['category_id'],
                'unit' => $validated['unit'],
                'critical_threshold' => $validated['critical_threshold'] ?? null,
            ]);

            Stock::create([
                'material_id' => $material->id,
                'quantity' => $validated['initial_stock']
            ]);
        });

        return redirect()->route('admin.materials.index')
            ->with('success', 'Material berhasil ditambahkan.');
    }

    /**
     * Display the specified material.
     *
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function show(Material $material)
    {
        // Load the stock and recent adjustments
        $material->load(['stock', 'category']);
        
        // Get the latest 5 stock adjustments
        $recentActivities = \App\Models\StockAdjustment::where('material_id', $material->id)
            ->with('user')
            ->latest()
            ->take(5)
            ->get();
        
        return view('admin.materials.show', compact('material', 'recentActivities'));
    }

    /**
     * Show the form for editing the specified material.
     *
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function edit(Material $material)
    {
        $categories = Category::orderBy('name')->get();
        return view('admin.materials.edit', compact('material', 'categories'));
    }

    /**
     * Update the specified material in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Material $material)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'required|string|max:50',
            'critical_threshold' => 'nullable|numeric|min:0',
        ]);

        $material->update($validated);

        return redirect()->route('admin.materials.index')
            ->with('success', 'Material berhasil diperbarui.');
    }

    /**
     * Remove the specified material from storage.
     *
     * @param  \App\Models\Material  $material
     * @return \Illuminate\Http\Response
     */
    public function destroy(Material $material)
    {
        // Soft delete: material disembunyikan dari daftar aktif,
        // namun data historis (permintaan, pengembalian, konsumsi) tetap utuh.
        $material->delete();

        return redirect()->route('admin.materials.index')
            ->with('success', 'Material berhasil dihapus.');
    }
}