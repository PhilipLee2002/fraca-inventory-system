<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\SupplierController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\PurchaseController;
use App\Http\Controllers\Api\SaleController;
use App\Http\Controllers\Api\StockAdjustmentController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\UserController;

// Public authentication endpoints
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // ── Products (low-stock MUST come before apiResource to avoid route conflict) ──
    Route::get('/products/low-stock', [ProductController::class, 'lowStock']);
    Route::apiResource('products', ProductController::class);

    // ── Core resources ──
    Route::apiResource('categories', CategoryController::class);
    Route::apiResource('suppliers',  SupplierController::class);
    Route::apiResource('customers',  CustomerController::class);

    // ── Purchases ──
    Route::apiResource('purchases', PurchaseController::class)->except(['update', 'destroy']);
    Route::put('/purchases/{purchase}',        [PurchaseController::class, 'update']);
    Route::put('/purchases/{purchase}/status', [PurchaseController::class, 'updateStatus']);
    Route::delete('/purchases/{purchase}',     [PurchaseController::class, 'destroy']);

    // ── Sales ──
    Route::apiResource('sales', SaleController::class)->except(['update', 'destroy']);
    Route::put('/sales/{sale}',        [SaleController::class, 'update']);
    Route::put('/sales/{sale}/status', [SaleController::class, 'updateStatus']);
    Route::delete('/sales/{sale}',     [SaleController::class, 'destroy']);

    // ── Stock adjustments ──
    Route::get('/stock-adjustments',  [StockAdjustmentController::class, 'index']);
    Route::post('/stock-adjustments', [StockAdjustmentController::class, 'store']);

    // ── Reports ──
    Route::prefix('reports')->group(function () {
        Route::get('/dashboard',           [ReportController::class, 'dashboard']);
        Route::get('/sales',               [ReportController::class, 'sales']);
        Route::get('/purchases',           [ReportController::class, 'purchases']);
        Route::get('/stock-levels',        [ReportController::class, 'stockLevels']);
        Route::get('/inventory-valuation', [ReportController::class, 'inventoryValuation']);
        Route::get('/profit-loss',         [ReportController::class, 'profitLoss']);
        Route::get('/stock-movement',      [ReportController::class, 'stockMovement']);
    });

    // ── Users (roles MUST come before apiResource) ──
    Route::get('/users/roles', [UserController::class, 'roles']);
    Route::apiResource('users', UserController::class);

    // ── Admin verification ──
    Route::post('/verify-admin', [AuthController::class, 'verifyAdmin']);

    // ── Activity & Alerts ──
    Route::get('/recent-activity',        [ReportController::class, 'recentActivity']);
    Route::get('/alerts',                 [ReportController::class, 'alerts']);
    Route::patch('/alerts/{id}/read',     [ReportController::class, 'markAlertRead']);
});
