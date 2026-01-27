<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Home/Dashboard Route
Route::get('/', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Breeze Profile Routes
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// User Management Routes - Protected by role and permissions
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('users', UserController::class)->except(['show']);
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show');
});

// Example protected routes for other modules
Route::middleware(['auth'])->group(function () {
    // Products - Example route
    Route::get('/products', function () {
        return view('products.index');
    })->name('products.index')->middleware('permission:view-product');
    
    // Categories - Example route
    Route::get('/categories', function () {
        return view('categories.index');
    })->name('categories.index')->middleware('permission:view-category');
    
    // Sales - Example route
    Route::get('/sales', function () {
        return view('sales.index');
    })->name('sales.index')->middleware('permission:view-sale');
    
    // Purchases - Example route
    Route::get('/purchases', function () {
        return view('purchases.index');
    })->name('purchases.index')->middleware('permission:view-purchase');
});

// Include Breeze Authentication Routes
require __DIR__.'/auth.php';