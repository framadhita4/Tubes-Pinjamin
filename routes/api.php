<?php

use App\Http\Controllers\ItemController;
use App\Http\Controllers\BorrowingController;
use App\Http\Controllers\NotificationController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Protected API routes (Passport authentication)
Route::middleware('auth:api')->group(function () {
    // Get authenticated user
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Items API
    Route::prefix('items')->group(function () {
        Route::get('/', [ItemController::class, 'index']);
        Route::get('/my-items', [ItemController::class, 'myItems']);
        Route::get('/search', [ItemController::class, 'search']);
        Route::get('/{id}', [ItemController::class, 'show']);
        Route::post('/', [ItemController::class, 'store'])->middleware('role:costumer');
        Route::put('/{id}', [ItemController::class, 'update'])->middleware('item.owner');
        Route::delete('/{id}', [ItemController::class, 'destroy'])->middleware('item.owner');
    });

    // Borrowings API
    Route::prefix('borrowings')->group(function () {
        Route::get('/', [BorrowingController::class, 'index']);
        Route::post('/', [BorrowingController::class, 'store']);
        Route::get('/history', [BorrowingController::class, 'history']);
        Route::get('/{id}', [BorrowingController::class, 'show']);
        Route::post('/{id}/cancel', [BorrowingController::class, 'cancel']);
        Route::post('/{id}/return', [BorrowingController::class, 'requestReturn']);
        Route::post('/{id}/approve', [BorrowingController::class, 'approve']);
        Route::post('/{id}/reject', [BorrowingController::class, 'reject']);
        Route::post('/{id}/approve-return', [BorrowingController::class, 'approveReturn']);
    });

    // Notifications API
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('/mark-all-read', [NotificationController::class, 'markAllAsRead']);
        Route::post('/{id}/mark-read', [NotificationController::class, 'markAsRead']);
        Route::delete('/{id}', [NotificationController::class, 'destroy']);
    });
});

