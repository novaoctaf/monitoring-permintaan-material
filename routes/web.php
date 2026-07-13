<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\RequestMaterialController;
use App\Http\Controllers\ReturnMaterialController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MaterialConsumptionController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::redirect('/', '/admin/dashboard', 301);

Auth::routes();

// Admin route group
Route::prefix('admin')->as('admin.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Category management routes
    Route::resource('categories', CategoryController::class);

    // Material management routes
    Route::resource('materials', MaterialController::class);
    
    // Stock management routes
    Route::get('stocks/adjust', [StockController::class, 'adjustForm'])->name('stocks.adjust');
    Route::post('stocks/adjust', [StockController::class, 'adjust'])->name('stocks.adjust.submit');
    Route::get('stocks/history', [StockController::class, 'history'])->name('stocks.history');
    Route::get('stocks/my-history', [MaterialConsumptionController::class, 'history'])
        ->name('stocks.my-history')
        ->middleware('role:produksi');
    Route::get('stocks/{material}', [StockController::class, 'show'])->name('stocks.show');
    Route::resource('stocks', StockController::class)->except(['create', 'store', 'edit', 'update', 'destroy', 'show']);

    Route::get('consumptions/create', [MaterialConsumptionController::class, 'create'])
        ->name('consumptions.create')
        ->middleware('role:produksi');
    Route::post('consumptions', [MaterialConsumptionController::class, 'store'])
        ->name('consumptions.store')
        ->middleware('role:produksi');
    
    // Request management routes
    Route::get('requests/approvals', [RequestMaterialController::class, 'approvals'])->name('requests.approvals');
    Route::post('requests/{requestMaterial}/approve', [RequestMaterialController::class, 'approve'])->name('requests.approve');
    Route::post('requests/{requestMaterial}/reject', [RequestMaterialController::class, 'reject'])->name('requests.reject');
    // Serah terima barang
    Route::post('requests/{requestMaterial}/handover', [RequestMaterialController::class, 'handover'])->name('requests.handover');
    Route::post('requests/{requestMaterial}/receive', [RequestMaterialController::class, 'receive'])->name('requests.receive');
    Route::resource('requests', RequestMaterialController::class);
    
    // Return management routes
    Route::get('returns/approvals', [ReturnMaterialController::class, 'approvals'])->name('returns.approvals');
    Route::post('returns/{returnMaterial}/approve', [ReturnMaterialController::class, 'approve'])->name('returns.approve');
    Route::post('returns/{returnMaterial}/reject', [ReturnMaterialController::class, 'reject'])->name('returns.reject');
    Route::resource('returns', ReturnMaterialController::class);
    
    // Reports (Staff only)
    Route::get('/reports/stock', [ReportController::class, 'stock'])->name('reports.stock');
    Route::post('/reports/stock/export', [ReportController::class, 'exportStock'])->name('reports.stock.export');
    Route::get('/reports/production-stock', [ReportController::class, 'productionStock'])->name('reports.production.stock');
    Route::post('/reports/production-stock/export', [ReportController::class, 'exportProductionStock'])->name('reports.production.stock.export');
    Route::get('/reports/requests', [ReportController::class, 'requests'])->name('reports.requests');
    Route::post('/reports/requests/export', [ReportController::class, 'exportRequests'])->name('reports.requests.export');
    Route::get('/reports/returns', [ReportController::class, 'returns'])->name('reports.returns');
    Route::post('/reports/returns/export', [ReportController::class, 'exportReturns'])->name('reports.returns.export');
    Route::get('/reports/consumptions', [ReportController::class, 'consumptions'])->name('reports.consumptions');
    Route::post('/reports/consumptions/export', [ReportController::class, 'exportConsumptions'])->name('reports.consumptions.export');

    // User management routes
    Route::resource('users', UserController::class);
    
    // Role management routes
    Route::resource('roles', RoleController::class);

    // Permission management routes
    Route::resource('permissions', PermissionController::class);
    
    // Profile routes (staff only)
    Route::middleware('role:staff')->group(function () {
        Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
        Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    });
});
