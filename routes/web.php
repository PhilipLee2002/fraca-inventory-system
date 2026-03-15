<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Home/Dashboard Route
Route::get('/', function () {
    return view('dashboard.index');
})->middleware(['auth', 'verified'])->name('dashboard');

// Breeze Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// User Management Routes - Admin only
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('users', [UserController::class, 'index'])->name('users.index')->middleware('permission:view-user');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create')->middleware('permission:create-user');
    Route::post('users', [UserController::class, 'store'])->name('users.store')->middleware('permission:create-user');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show')->middleware('permission:view-user');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('permission:edit-user');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('permission:edit-user');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:delete-user');
});

// Protected module routes
Route::middleware(['auth'])->group(function () {
    Route::get('/products', fn() => view('products.index'))
        ->name('page.products')->middleware('permission:view-product');

    Route::get('/categories', fn() => view('categories.index'))
        ->name('page.categories')->middleware('permission:view-category');

    Route::get('/suppliers', fn() => view('suppliers.index'))
        ->name('page.suppliers')->middleware('permission:view-supplier');

    Route::get('/customers', fn() => view('customers.index'))
        ->name('page.customers')->middleware('permission:view-customer');

    Route::get('/sales', fn() => view('sales.index'))
        ->name('page.sales')->middleware('permission:view-sale');

    Route::get('/purchases', fn() => view('purchases.index'))
        ->name('page.purchases')->middleware('permission:view-purchase');

    Route::get('/stock-adjustments', fn() => view('stock-adjustments.index'))
        ->name('page.stock-adjustments')->middleware('permission:manage-stock');

    Route::get('/reports', fn() => view('reports.index'))
        ->name('page.reports')->middleware('permission:view-report');
});

// Include Breeze Authentication Routes
require __DIR__.'/auth.php';
