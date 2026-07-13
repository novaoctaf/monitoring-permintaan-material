<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Material;
use App\Models\Stock;
use App\Models\StockAdjustment;
use App\Models\RequestMaterial;
use App\Models\ReturnMaterial;
use App\Models\MaterialConsumption;
use App\Models\User;
use App\Exports\StockExport;
use App\Exports\RequestsExport;
use App\Exports\ReturnsExport;
use App\Exports\MaterialConsumptionsExport;
use App\Exports\ProductionStockExport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class ReportController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth']);
        $this->middleware('role:staff'); // Only staff can access reports
    }

    /**
     * Display stock report page with filters
     */
    public function stock(Request $request)
    {
        $query = Stock::with(['material.category'])->whereHas('material');

        // Apply filters
        if ($request->filled('search')) {
            $query->whereHas('material', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('material', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->where('quantity', '<=', 10)->where('quantity', '>', 0);
            } elseif ($request->stock_status === 'out') {
                $query->where('quantity', '=', 0);
            }
        }

        $stocks = $query->orderBy('quantity', 'asc')->paginate(15);
        $categories = Category::orderBy('name')->get();

        // Calculate summary (exclude stock of soft-deleted materials)
        $summary = [
            'total_materials' => Material::count(),
            'total_stock_value' => Stock::whereHas('material')->sum('quantity'),
            'low_stock_count' => Stock::whereHas('material')
                ->where('quantity', '<=', 10)->where('quantity', '>', 0)->count(),
            'out_of_stock_count' => Stock::whereHas('material')
                ->where('quantity', '=', 0)->count(),
        ];

        return view('admin.reports.stock', compact('stocks', 'categories', 'summary'));
    }

    /**
     * Export stock report to Excel/CSV/PDF
     */
    public function exportStock(Request $request)
    {
        $query = Stock::with(['material.category'])->whereHas('material');

        // Apply same filters as stock() method
        if ($request->filled('search')) {
            $query->whereHas('material', function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('material', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->where('quantity', '<=', 10)->where('quantity', '>', 0);
            } elseif ($request->stock_status === 'out') {
                $query->where('quantity', '=', 0);
            }
        }

        $query->orderBy('quantity', 'asc');

        // Generate filename
        $format = $request->input('format', 'xlsx');
        $timestamp = now()->format('Y-m-d_His');
        $filename = "laporan_stok_{$timestamp}.{$format}";

        // Handle PDF export
        if ($format === 'pdf') {
            $stocks = $query->get();
            $category = $request->filled('category') 
                ? Category::find($request->category)->name 
                : null;
            $search = $request->input('search');

            $pdf = Pdf::loadView('admin.reports.pdf.stock', compact('stocks', 'category', 'search'))
                ->setPaper('a4', 'landscape');
            
            return $pdf->download($filename);
        }

        // Export based on format (Excel/CSV)
        if ($format === 'csv') {
            return Excel::download(new StockExport($query), $filename, \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new StockExport($query), $filename);
    }

    /**
     * Display production stock report page with filters
     */
    public function productionStock(Request $request)
    {
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
            $totalRequested = RequestMaterial::where('material_id', $material->id)
                ->where('status', 'approved')
                ->whereNotNull('received_at') // hanya barang yang sudah diterima produksi
                ->sum('quantity');

            $totalReturned = ReturnMaterial::whereHas('request', function($q) use ($material) {
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

        // Hanya tampilkan material yang masih punya stok produksi tersedia (> 0)
        $stocks = $stocks->filter(function($stock) {
            return $stock->quantity > 0;
        });

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $stocks = $stocks->filter(function($stock) {
                    return $stock->quantity <= 10 && $stock->quantity > 0;
                });
            }
        }

        $stocks = $stocks->sortBy('quantity')->values();

        $perPage = 15;
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

        $categories = Category::orderBy('name')->get();

        $summary = [
            'total_materials' => Material::count(),
            'total_production_stock' => $stocks->sum('quantity'),
            'low_stock_count' => collect($materials)->filter(function($material) {
                $totalRequested = RequestMaterial::where('material_id', $material->id)->where('status', 'approved')->whereNotNull('received_at')->sum('quantity');
                $totalReturned = ReturnMaterial::whereHas('request', function($q) use ($material) {
                        $q->where('material_id', $material->id);
                    })->where('status', 'approved')->sum('quantity');
                $totalConsumed = MaterialConsumption::where('material_id', $material->id)->sum('quantity');
                $quantity = max(0, $totalRequested - $totalReturned - $totalConsumed);
                return $quantity <= 10 && $quantity > 0;
            })->count(),
            'available_count' => collect($materials)->filter(function($material) {
                $totalRequested = RequestMaterial::where('material_id', $material->id)->where('status', 'approved')->whereNotNull('received_at')->sum('quantity');
                $totalReturned = ReturnMaterial::whereHas('request', function($q) use ($material) {
                        $q->where('material_id', $material->id);
                    })->where('status', 'approved')->sum('quantity');
                $totalConsumed = MaterialConsumption::where('material_id', $material->id)->sum('quantity');
                $quantity = max(0, $totalRequested - $totalReturned - $totalConsumed);
                return $quantity > 0;
            })->count(),
        ];

        return view('admin.reports.production_stock', compact('stocks', 'categories', 'summary'));
    }

    public function exportProductionStock(Request $request)
    {
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
            $totalRequested = RequestMaterial::where('material_id', $material->id)
                ->where('status', 'approved')
                ->whereNotNull('received_at') // hanya barang yang sudah diterima produksi
                ->sum('quantity');

            $totalReturned = ReturnMaterial::whereHas('request', function($q) use ($material) {
                    $q->where('material_id', $material->id);
                })
                ->where('status', 'approved')
                ->sum('quantity');

            $totalConsumed = MaterialConsumption::where('material_id', $material->id)
                ->sum('quantity');

            return (object)[
                'material' => $material,
                'quantity' => max(0, $totalRequested - $totalReturned - $totalConsumed),
            ];
        });

        // Hanya tampilkan material yang masih punya stok produksi tersedia (> 0)
        $stocks = $stocks->filter(function($stock) {
            return $stock->quantity > 0;
        });

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $stocks = $stocks->filter(function($stock) {
                    return $stock->quantity <= 10 && $stock->quantity > 0;
                });
            }
        }

        $format = $request->input('format', 'xlsx');
        $timestamp = now()->format('Y-m-d_His');
        $filename = "laporan_stok_produksi_{$timestamp}.{$format}";

        if ($format === 'csv') {
            return Excel::download(new ProductionStockExport($stocks), $filename, \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new ProductionStockExport($stocks), $filename);
    }

    /**
     * Display requests report page with filters
     */
    public function requests(Request $request)
    {
        $query = RequestMaterial::with(['material.category', 'requester', 'approver']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('request_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('material', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->whereHas('material', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        if ($request->filled('requester_id')) {
            $query->where('requested_by', $request->requester_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $requests = $query->latest()->paginate(15);
        $categories = Category::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        // Calculate summary
        $summary = [
            'total_requests' => RequestMaterial::count(),
            'approved' => RequestMaterial::where('status', 'approved')->count(),
            'pending' => RequestMaterial::where('status', 'pending')->count(),
            'rejected' => RequestMaterial::where('status', 'rejected')->count(),
            'total_quantity_approved' => RequestMaterial::where('status', 'approved')->sum('quantity'),
        ];

        return view('admin.reports.requests', compact('requests', 'categories', 'users', 'summary'));
    }

    /**
     * Export requests report to Excel/CSV/PDF
     */
    public function exportRequests(Request $request)
    {
        $query = RequestMaterial::with(['material.category', 'requester', 'approver']);

        // Apply same filters as requests() method
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('request_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('material', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->whereHas('material', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        if ($request->filled('requester_id')) {
            $query->where('requested_by', $request->requester_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $query->latest();

        // Generate filename with date range if applicable
        $format = $request->input('format', 'xlsx');
        $timestamp = now()->format('Y-m-d_His');
        $dateRange = '';
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $dateRange = '_' . $request->date_from . '_to_' . $request->date_to;
        }
        $filename = "laporan_permintaan{$dateRange}_{$timestamp}.{$format}";

        // Handle PDF export
        if ($format === 'pdf') {
            $requests = $query->get();
            $date_from = $request->input('date_from');
            $date_to = $request->input('date_to');
            $status = $request->input('status');
            $requestor = $request->filled('requester_id') 
                ? User::find($request->requester_id)->name 
                : null;

            $pdf = Pdf::loadView('admin.reports.pdf.requests', compact('requests', 'date_from', 'date_to', 'status', 'requestor'))
                ->setPaper('a4', 'landscape');
            
            return $pdf->download($filename);
        }

        // Export based on format (Excel/CSV)
        if ($format === 'csv') {
            return Excel::download(new RequestsExport($query), $filename, \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new RequestsExport($query), $filename);
    }

    /**
     * Display material consumption report page with filters
     */
    public function consumptions(Request $request)
    {
        $query = MaterialConsumption::with(['material.category', 'consumer']);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('material', function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                })->orWhereHas('consumer', function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                })->orWhere('notes', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('material', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        if ($request->filled('consumer_id')) {
            $query->where('consumed_by', $request->consumer_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $consumptions = $query->latest()->paginate(15);
        $categories = Category::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        $summary = [
            'total_consumptions' => MaterialConsumption::count(),
            'total_quantity' => MaterialConsumption::sum('quantity'),
            'distinct_materials' => MaterialConsumption::distinct('material_id')->count('material_id'),
            'unique_consumers' => MaterialConsumption::distinct('consumed_by')->count('consumed_by'),
        ];

        return view('admin.reports.consumptions', compact('consumptions', 'categories', 'users', 'summary'));
    }

    /**
     * Export material consumption report to Excel/CSV
     */
    public function exportConsumptions(Request $request)
    {
        $query = MaterialConsumption::with(['material.category', 'consumer']);

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('material', function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                })->orWhereHas('consumer', function($q) use ($request) {
                    $q->where('name', 'like', '%' . $request->search . '%');
                })->orWhere('notes', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('category')) {
            $query->whereHas('material', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        if ($request->filled('consumer_id')) {
            $query->where('consumed_by', $request->consumer_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $query->latest();

        $format = $request->input('format', 'xlsx');
        $timestamp = now()->format('Y-m-d_His');
        $dateRange = '';
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $dateRange = '_' . $request->date_from . '_to_' . $request->date_to;
        }
        $filename = "laporan_pemakaian{$dateRange}_{$timestamp}.{$format}";

        if ($format === 'csv') {
            return Excel::download(new MaterialConsumptionsExport($query), $filename, \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new MaterialConsumptionsExport($query), $filename);
    }

    /**
     * Display returns report page with filters
     */
    public function returns(Request $request)
    {
        $query = ReturnMaterial::with(['request.material.category', 'returner', 'approver']);

        // Apply filters
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('return_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('request.material', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->whereHas('request.material', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        if ($request->filled('returner_id')) {
            $query->where('returned_by', $request->returner_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $returns = $query->latest()->paginate(15);
        $categories = Category::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        // Calculate summary
        $summary = [
            'total_returns' => ReturnMaterial::count(),
            'approved' => ReturnMaterial::where('status', 'approved')->count(),
            'pending' => ReturnMaterial::where('status', 'pending')->count(),
            'rejected' => ReturnMaterial::where('status', 'rejected')->count(),
            'total_quantity_returned' => ReturnMaterial::where('status', 'approved')->sum('quantity'),
        ];

        return view('admin.reports.returns', compact('returns', 'categories', 'users', 'summary'));
    }

    /**
     * Export returns report to Excel/CSV/PDF
     */
    public function exportReturns(Request $request)
    {
        $query = ReturnMaterial::with(['request.material.category', 'returner', 'approver']);

        // Apply same filters as returns() method
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('return_number', 'like', '%' . $request->search . '%')
                  ->orWhereHas('material', function($q) use ($request) {
                      $q->where('name', 'like', '%' . $request->search . '%');
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('category')) {
            $query->whereHas('material', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }

        if ($request->filled('returner_id')) {
            $query->where('returned_by', $request->returner_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $query->latest();

        // Generate filename with date range if applicable
        $format = $request->input('format', 'xlsx');
        $timestamp = now()->format('Y-m-d_His');
        $dateRange = '';
        if ($request->filled('date_from') && $request->filled('date_to')) {
            $dateRange = '_' . $request->date_from . '_to_' . $request->date_to;
        }
        $filename = "laporan_pengembalian{$dateRange}_{$timestamp}.{$format}";

        // Handle PDF export
        if ($format === 'pdf') {
            $returns = $query->get();
            $date_from = $request->input('date_from');
            $date_to = $request->input('date_to');
            $status = $request->input('status');
            $returnor = $request->filled('returner_id') 
                ? User::find($request->returner_id)->name 
                : null;

            $pdf = Pdf::loadView('admin.reports.pdf.returns', compact('returns', 'date_from', 'date_to', 'status', 'returnor'))
                ->setPaper('a4', 'landscape');
            
            return $pdf->download($filename);
        }

        // Export based on format (Excel/CSV)
        if ($format === 'csv') {
            return Excel::download(new ReturnsExport($query), $filename, \Maatwebsite\Excel\Excel::CSV);
        }

        return Excel::download(new ReturnsExport($query), $filename);
    }
}
