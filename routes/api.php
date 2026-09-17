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

    // ── Products ──
    Route::get('/products/low-stock', [ProductController::class, 'lowStock'])->middleware('permission:view-product');
    Route::get('/products', [ProductController::class, 'index'])->middleware('permission:view-product');
    Route::post('/products', [ProductController::class, 'store'])->middleware('permission:create-product');
    Route::get('/products/{product}', [ProductController::class, 'show'])->middleware('permission:view-product');
    Route::put('/products/{product}', [ProductController::class, 'update'])->middleware('permission:edit-product');
    Route::patch('/products/{product}', [ProductController::class, 'update'])->middleware('permission:edit-product');
    Route::delete('/products/{product}', [ProductController::class, 'destroy'])->middleware('permission:delete-product');

    // ── Categories ──
    Route::get('/categories', [CategoryController::class, 'index'])->middleware('permission:view-category');
    Route::post('/categories', [CategoryController::class, 'store'])->middleware('permission:create-category');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->middleware('permission:view-category');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->middleware('permission:edit-category');
    Route::patch('/categories/{category}', [CategoryController::class, 'update'])->middleware('permission:edit-category');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->middleware('permission:delete-category');

    // ── Suppliers ──
    Route::get('/suppliers', [SupplierController::class, 'index'])->middleware('permission:view-supplier');
    Route::post('/suppliers', [SupplierController::class, 'store'])->middleware('permission:create-supplier');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->middleware('permission:view-supplier');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->middleware('permission:edit-supplier');
    Route::patch('/suppliers/{supplier}', [SupplierController::class, 'update'])->middleware('permission:edit-supplier');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->middleware('permission:delete-supplier');

    // ── Customers ──
    Route::get('/customers', [CustomerController::class, 'index'])->middleware('permission:view-customer');
    Route::post('/customers', [CustomerController::class, 'store'])->middleware('permission:create-customer');
    Route::get('/customers/{customer}', [CustomerController::class, 'show'])->middleware('permission:view-customer');
    Route::put('/customers/{customer}', [CustomerController::class, 'update'])->middleware('permission:edit-customer');
    Route::patch('/customers/{customer}', [CustomerController::class, 'update'])->middleware('permission:edit-customer');
    Route::delete('/customers/{customer}', [CustomerController::class, 'destroy'])->middleware('permission:delete-customer');

    // ── Purchases ──
    Route::get('/purchases', [PurchaseController::class, 'index'])->middleware('permission:view-purchase');
    Route::post('/purchases', [PurchaseController::class, 'store'])->middleware('permission:create-purchase');
    Route::get('/purchases/{purchase}', [PurchaseController::class, 'show'])->middleware('permission:view-purchase');
    Route::put('/purchases/{purchase}', [PurchaseController::class, 'update'])->middleware('permission:edit-purchase');
    Route::put('/purchases/{purchase}/status', [PurchaseController::class, 'updateStatus'])->middleware('permission:edit-purchase');
    Route::delete('/purchases/{purchase}', [PurchaseController::class, 'destroy'])->middleware('permission:delete-purchase');

    // ── Sales ──
    Route::get('/sales', [SaleController::class, 'index'])->middleware('permission:view-sale');
    Route::post('/sales', [SaleController::class, 'store'])->middleware('permission:create-sale');
    Route::get('/sales/{sale}', [SaleController::class, 'show'])->middleware('permission:view-sale');
    Route::put('/sales/{sale}', [SaleController::class, 'update'])->middleware('permission:edit-sale');
    Route::put('/sales/{sale}/status', [SaleController::class, 'updateStatus'])->middleware('permission:edit-sale');
    Route::delete('/sales/{sale}', [SaleController::class, 'destroy'])->middleware('permission:delete-sale');

    // ── Stock adjustments ──
    Route::get('/stock-adjustments', [StockAdjustmentController::class, 'index'])->middleware('permission:view-stock');
    Route::post('/stock-adjustments', [StockAdjustmentController::class, 'store'])->middleware('permission:manage-stock');

    // ── Dashboard (morning totals) — view-sale so reception can see today's sales ──
    Route::get('/reports/dashboard', [ReportController::class, 'dashboard'])->middleware('permission:view-sale');
    Route::get('/recent-activity', [ReportController::class, 'recentActivity'])->middleware('permission:view-sale');
    Route::get('/alerts', [ReportController::class, 'alerts'])->middleware('permission:view-sale');
    Route::patch('/alerts/{id}/read', [ReportController::class, 'markAlertRead'])->middleware('permission:view-sale');

    // ── Accountant reports ──
    Route::middleware('permission:view-report')->prefix('reports')->group(function () {
        Route::get('/sales', [ReportController::class, 'sales']);
        Route::get('/purchases', [ReportController::class, 'purchases']);
        Route::get('/stock-levels', [ReportController::class, 'stockLevels']);
        Route::get('/inventory-valuation', [ReportController::class, 'inventoryValuation']);
        Route::get('/profit-loss', [ReportController::class, 'profitLoss']);
        Route::get('/stock-movement', [ReportController::class, 'stockMovement']);
    });

    // ── Users ──
    Route::get('/users/roles', [UserController::class, 'roles'])->middleware('permission:view-user');
    Route::get('/users', [UserController::class, 'index'])->middleware('permission:view-user');
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:create-user');
    Route::get('/users/{user}', [UserController::class, 'show'])->middleware('permission:view-user');
    Route::put('/users/{user}', [UserController::class, 'update'])->middleware('permission:edit-user');
    Route::patch('/users/{user}', [UserController::class, 'update'])->middleware('permission:edit-user');
    Route::delete('/users/{user}', [UserController::class, 'destroy'])->middleware('permission:delete-user');

    Route::post('/verify-admin', [AuthController::class, 'verifyAdmin'])->middleware('permission:edit-sale');
});
