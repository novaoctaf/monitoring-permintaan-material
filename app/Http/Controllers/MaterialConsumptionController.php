<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Material;
use App\Models\MaterialConsumption;
use App\Models\RequestMaterial;
use App\Models\ReturnMaterial;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class MaterialConsumptionController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:produksi']);
    }

    public function create(Request $request)
    {
        $material = Material::with('category')->findOrFail($request->material_id);
        $available = $this->getProductionStockForCurrentUser($material);

        return view('admin.consumptions.create', compact('material', 'available'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_id' => 'required|exists:materials,id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string|max:1000',
        ]);

        $material = Material::findOrFail($validated['material_id']);
        $available = $this->getProductionStockForCurrentUser($material);

        if ($validated['quantity'] > $available) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['quantity' => "Jumlah pemakaian tidak boleh melebihi stok produksi tersedia ({$available} {$material->unit})."]);
        }

        MaterialConsumption::create([
            'material_id' => $validated['material_id'],
            'consumed_by' => auth()->id(),
            'quantity' => $validated['quantity'],
            'notes' => $validated['notes'] ?? null,
        ]);

        return redirect()->route('admin.stocks.index', ['view' => 'produksi'])
            ->with('success', 'Pemakaian material berhasil dicatat.');
    }

    /**
     * Show the current produksi user's stock movement history:
     * material they used (consumption) and material they returned.
     */
    public function history(Request $request)
    {
        $userId = auth()->id();

        // Material the user has used
        $consumptions = MaterialConsumption::with('material.category')
            ->where('consumed_by', $userId)
            ->get()
            ->map(function ($c) {
                return (object) [
                    'date' => $c->created_at,
                    'type' => 'consumption',
                    'material' => $c->material,
                    'quantity' => $c->quantity,
                    'status' => null,
                    'notes' => $c->notes,
                ];
            });

        // Material the user has returned
        $returns = ReturnMaterial::with('request.material.category')
            ->where('returned_by', $userId)
            ->get()
            ->map(function ($r) {
                return (object) [
                    'date' => $r->created_at,
                    'type' => 'return',
                    'material' => $r->request->material ?? null,
                    'quantity' => $r->quantity,
                    'status' => $r->status,
                    'received_at' => $r->received_at,
                    'notes' => $r->notes,
                ];
            });

        $history = $consumptions->concat($returns);

        // Filters
        if ($request->filled('type')) {
            $wanted = $request->type === 'pakai' ? 'consumption' : 'return';
            $history = $history->where('type', $wanted);
        }

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $history = $history->filter(fn ($h) => $h->material && str_contains(strtolower($h->material->name), $search));
        }

        if ($request->filled('category')) {
            $history = $history->filter(fn ($h) => $h->material && $h->material->category_id == $request->category);
        }

        if ($request->filled('date_from')) {
            $history = $history->filter(fn ($h) => $h->date->toDateString() >= $request->date_from);
        }

        if ($request->filled('date_to')) {
            $history = $history->filter(fn ($h) => $h->date->toDateString() <= $request->date_to);
        }

        $history = $history->sortByDesc('date')->values();

        $summary = [
            'total_used' => $consumptions->sum('quantity'),
            'total_returned' => $returns->where('status', 'approved')->whereNotNull('received_at')->sum('quantity'),
            'total_records' => $history->count(),
            'distinct_materials' => $history->filter(fn ($h) => $h->material)
                ->pluck('material.id')->unique()->count(),
        ];

        // Manual pagination over the merged collection
        $perPage = 15;
        $currentPage = (int) $request->get('page', 1);
        $total = $history->count();
        $items = $history->slice(($currentPage - 1) * $perPage, $perPage)->values();

        $history = new LengthAwarePaginator(
            $items,
            $total,
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $categories = Category::orderBy('name')->get();

        return view('admin.stocks.my_history', compact('history', 'categories', 'summary'));
    }

    private function getProductionStockForCurrentUser(Material $material): int
    {
        $totalRequested = RequestMaterial::where('material_id', $material->id)
            ->where('status', 'approved')
            ->whereNotNull('received_at') // hanya barang yang sudah diterima produksi
            ->where('requested_by', auth()->id())
            ->sum('quantity');

        $totalReturned = ReturnMaterial::whereHas('request', function ($q) use ($material) {
                $q->where('material_id', $material->id)
                  ->where('requested_by', auth()->id());
            })
            ->where('status', 'approved')
            ->whereNotNull('received_at') // baru mengurangi stok produksi setelah diterima store
            ->sum('quantity');

        $totalConsumed = MaterialConsumption::where('material_id', $material->id)
            ->where('consumed_by', auth()->id())
            ->sum('quantity');

        return max(0, $totalRequested - $totalReturned - $totalConsumed);
    }
}
