<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use Illuminate\Support\Facades\Route;

// Halaman utama
Route::get('/', function () {
    return view('index');
})->name('home');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register'])->name('register.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Protected Routes (require authentication)
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Panel Pemilik
    Route::get('/panel-pemilik', function () {
        return view('panel-pemilik');
    })->name('panel-pemilik');

    // Upload
    Route::get('/upload', function () {
        return view('upload');
    })->name('upload');

    // Form Peminjaman
    Route::get('/form-peminjaman', function () {
        return view('form-peminjaman');
    })->name('form-peminjaman');

    // Item API Routes
    Route::get('/api/items', [ItemController::class, 'index'])->name('items.index');
    Route::get('/api/items/my-items', [ItemController::class, 'myItems'])->name('items.my');
    Route::post('/api/items', [ItemController::class, 'store'])->name('items.store');
    Route::get('/api/items/{id}', [ItemController::class, 'show'])->name('items.show');
    Route::put('/api/items/{id}', [ItemController::class, 'update'])->name('items.update');
    Route::delete('/api/items/{id}', [ItemController::class, 'destroy'])->name('items.destroy');
});
