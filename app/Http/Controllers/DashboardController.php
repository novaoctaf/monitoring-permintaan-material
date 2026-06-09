<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Stock;
use App\Models\RequestMaterial;
use App\Models\ReturnMaterial;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $user = Auth::user();
        
        // Common dashboard data
        $data = [
            'totalMaterials' => Material::count(),
            'lowStockCount' => Stock::where('quantity', '<=', 10)
                ->whereHas('material')->count(),
        ];
        
        // Get the last 7 days for charts
        $lastWeek = Carbon::now()->subDays(6);
        $dates = collect(range(0, 6))->map(function ($days) {
            return Carbon::now()->subDays($days)->format('Y-m-d');
        })->reverse();
        
        if ($user->hasRole('staff')) {
            // Staff sees everything
            $data = array_merge($data, $this->getStaffDashboardData($dates));
            return view('dashboard.staff', $data);
        } 
        elseif ($user->hasRole('store')) {
            // Store sees stock and pending requests/returns
            $data = array_merge($data, $this->getStoreDashboardData($dates));
            return view('dashboard.store', $data);
        } 
        elseif ($user->hasRole('produksi')) {
            // Produksi sees their requests and returns
            $data = array_merge($data, $this->getProduksiDashboardData($dates, $user->id));
            return view('dashboard.produksi', $data);
        }
        
        return view('dashboard', $data);
    }

    /**
     * Get dashboard data for staff role
     */
    private function getStaffDashboardData($dates)
    {
        $data = [
            'totalUsers' => User::count(),
            'pendingRequests' => RequestMaterial::where('status', 'pending')->count(),
            'pendingReturns' => ReturnMaterial::where('status', 'pending')->count(),
            'recentRequests' => RequestMaterial::with(['requester', 'material'])
                ->latest()->take(5)->get(),
            'recentReturns' => ReturnMaterial::with(['returner', 'request.material'])
                ->latest()->take(5)->get(),
        ];

        // Materials by category
        $data['materialsByCategory'] = Material::select('categories.name', DB::raw('count(*) as total'))
            ->join('categories', 'materials.category_id', '=', 'categories.id')
            ->groupBy('categories.name')
            ->orderBy('total', 'desc')
            ->get();

        // Daily requests and returns for the last 7 days
        $data['dailyStats'] = collect($dates)->map(function ($date) {
            return [
                'date' => Carbon::parse($date)->format('d M'),
                'requests' => RequestMaterial::whereDate('created_at', $date)->count(),
                'returns' => ReturnMaterial::whereDate('created_at', $date)->count(),
            ];
        });

        // Request status distribution
        $data['requestStats'] = [
            'pending' => RequestMaterial::where('status', 'pending')->count(),
            'approved' => RequestMaterial::where('status', 'approved')->count(),
            'rejected' => RequestMaterial::where('status', 'rejected')->count(),
        ];

        return $data;
    }

    /**
     * Get dashboard data for store role
     */
    private function getStoreDashboardData($dates)
    {
        $data = [
            'pendingRequests' => RequestMaterial::where('status', 'pending')->count(),
            'pendingReturns' => ReturnMaterial::where('status', 'pending')->count(),
            'recentRequests' => RequestMaterial::with(['requester', 'material'])
                ->latest()->take(5)->get(),
            'recentReturns' => ReturnMaterial::with(['returner', 'request.material'])
                ->latest()->take(5)->get(),
        ];

        // Low stock materials with status classification
        $lowStockRaw = Stock::with('material')
            ->join('materials as m', 'stocks.material_id', '=', 'm.id')
            ->whereNull('m.deleted_at')
            ->where(function ($q) {
                $q->where('stocks.quantity', '<=', 10)
                  ->orWhereRaw('m.critical_threshold IS NOT NULL AND stocks.quantity <= m.critical_threshold');
            })
            ->select('stocks.*', 'm.critical_threshold as material_critical_threshold')
            ->orderBy('stocks.quantity')
            ->take(15)
            ->get();

        $data['lowStockMaterials'] = $lowStockRaw->map(function ($stock) {
            $qty = (float) $stock->quantity;
            $threshold = $stock->material_critical_threshold !== null ? (float) $stock->material_critical_threshold : null;

            if ($qty == 0) {
                $status = 'habis';
                $color = '#d63939';
            } elseif ($threshold !== null && $qty <= $threshold) {
                $status = 'kritis';
                $color = '#f76707';
            } else {
                $status = 'menipis';
                $color = '#f59f00';
            }

            return [
                'name'      => $stock->material->name ?? '-',
                'quantity'  => $qty,
                'unit'      => $stock->material->unit ?? '',
                'threshold' => $threshold,
                'status'    => $status,
                'color'     => $color,
            ];
        });

        $data['emptyCount']    = $data['lowStockMaterials']->where('status', 'habis')->count();
        $data['criticalCount'] = $data['lowStockMaterials']->where('status', 'kritis')->count();

        // Daily material movements
        $data['materialMovements'] = collect($dates)->map(function ($date) {
            return [
                'date' => Carbon::parse($date)->format('d M'),
                'in' => ReturnMaterial::whereDate('created_at', $date)
                    ->where('status', 'approved')
                    ->sum('quantity'),
                'out' => RequestMaterial::whereDate('created_at', $date)
                    ->where('status', 'approved')
                    ->sum('quantity'),
            ];
        });

        // Most requested materials
        $data['topMaterials'] = RequestMaterial::select('material_id', DB::raw('count(*) as total'))
            ->with('material:id,name')
            ->where('status', 'approved')
            ->groupBy('material_id')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        return $data;
    }

    /**
     * Get dashboard data for produksi role
     */
    private function getProduksiDashboardData($dates, $userId)
    {
        $data = [
            'myPendingRequests' => RequestMaterial::where('requested_by', $userId)
                ->where('status', 'pending')->count(),
            'myApprovedRequests' => RequestMaterial::where('requested_by', $userId)
                ->where('status', 'approved')->count(),
            'myPendingReturns' => ReturnMaterial::where('returned_by', $userId)
                ->where('status', 'pending')->count(),
            'recentRequests' => RequestMaterial::where('requested_by', $userId)
                ->with(['material'])->latest()->take(5)->get(),
        ];

        // User's request history
        $data['myRequestHistory'] = collect($dates)->map(function ($date) use ($userId) {
            return [
                'date' => Carbon::parse($date)->format('d M'),
                'requests' => RequestMaterial::where('requested_by', $userId)
                    ->whereDate('created_at', $date)->count(),
                'returns' => ReturnMaterial::where('returned_by', $userId)
                    ->whereDate('created_at', $date)->count(),
            ];
        });

        // Request status distribution
        $data['myRequestStats'] = [
            'pending' => RequestMaterial::where('requested_by', $userId)
                ->where('status', 'pending')->count(),
            'approved' => RequestMaterial::where('requested_by', $userId)
                ->where('status', 'approved')->count(),
            'rejected' => RequestMaterial::where('requested_by', $userId)
                ->where('status', 'rejected')->count(),
        ];

        // Most requested materials by user
        $data['myTopMaterials'] = RequestMaterial::select('material_id', DB::raw('count(*) as total'))
            ->with('material:id,name')
            ->where('requested_by', $userId)
            ->groupBy('material_id')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        return $data;
    }
}
