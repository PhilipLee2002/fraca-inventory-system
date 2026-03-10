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
    Route::get('users', [UserController::class, 'index'])->name('users.index')->middleware('permission:view-user');
    Route::get('users/create', [UserController::class, 'create'])->name('users.create')->middleware('permission:create-user');
    Route::post('users', [UserController::class, 'store'])->name('users.store')->middleware('permission:create-user');
    Route::get('users/{user}', [UserController::class, 'show'])->name('users.show')->middleware('permission:view-user');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit')->middleware('permission:edit-user');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update')->middleware('permission:edit-user');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy')->middleware('permission:delete-user');
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