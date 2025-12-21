<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\NotificationController;
use Illuminate\Support\Facades\Route;

// Halaman utama
Route::get('/', function () {
    return view('index');
})->name('home');

// Authentication Routes (Guest only)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.post');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.post');
});

// Logout (Authenticated only)
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// Protected Routes (require authentication)
Route::middleware(['auth'])->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // My Borrowings (Peminjam only)
    Route::middleware('role:peminjam')->group(function () {
        Route::get('/my-borrowings', function () {
            return view('my-borrowings');
        })->name('my-borrowings');
    });

    // Borrowing Routes
    Route::prefix('borrowings')->group(function () {
        Route::get('/', [BorrowingController::class, 'index'])->name('borrowings.index');
        Route::post('/', [BorrowingController::class, 'store'])->name('borrowings.store');
        Route::get('/history', [BorrowingController::class, 'history'])->name('borrowings.history');
        Route::get('/{id}', [BorrowingController::class, 'show'])->name('borrowings.show');
        Route::post('/{id}/cancel', [BorrowingController::class, 'cancel'])->name('borrowings.cancel');
        
        // Return routes (Peminjam)
        Route::post('/{id}/return', [BorrowingController::class, 'requestReturn'])->name('borrowings.return');
        
        // Approval routes (Costumer only)
        Route::post('/{id}/approve', [BorrowingController::class, 'approve'])->name('borrowings.approve');
        Route::post('/{id}/reject', [BorrowingController::class, 'reject'])->name('borrowings.reject');
        Route::post('/{id}/approve-return', [BorrowingController::class, 'approveReturn'])->name('borrowings.approve-return');
    });

    // Notification Routes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('/unread-count', [NotificationController::class, 'unreadCount'])->name('notifications.unread-count');
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::delete('/{id}', [NotificationController::class, 'destroy'])->name('notifications.destroy');
    });

    // Items Routes  
    Route::get('/items', [ItemController::class, 'index'])->name('items.index');
    Route::get('/items/my-items', [ItemController::class, 'myItems'])->name('items.my');
    Route::get('/items/search', [ItemController::class, 'search'])->name('items.search');
    Route::get('/items/{id}', [ItemController::class, 'show'])->name('items.show');
    
    // Item management (Costumer only - they list items for peminjam to borrow)
    Route::middleware('role:costumer')->group(function () {
        Route::get('/upload', function () {
            return view('upload');
        })->name('upload');
        
        Route::get('/my-items', function () {
            return view('my-items');
        })->name('my-items');
        
        Route::post('/items', [ItemController::class, 'store'])->name('items.store');
        Route::post('/items/{id}', [ItemController::class, 'update'])->name('items.update');
        Route::delete('/items/{id}', [ItemController::class, 'destroy'])->name('items.destroy');
    });

    // Panel Pemilik (Costumer only) - for managing borrowing requests
    Route::middleware('role:costumer')->group(function () {
        Route::get('/panel-pemilik', function () {
            return view('panel-pemilik');
        })->name('panel-pemilik');
    });
});
