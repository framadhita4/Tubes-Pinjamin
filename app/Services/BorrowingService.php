<?php

namespace App\Services;

use App\Models\Borrowing;
use App\Models\Item;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BorrowingService
{
    protected $notificationService;
    protected $fileUploadService;

    public function __construct(NotificationService $notificationService, FileUploadService $fileUploadService)
    {
        $this->notificationService = $notificationService;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Create a new borrowing request
     *
     * @param array $data
     * @return Borrowing
     */
    public function createBorrowingRequest(array $data): Borrowing
    {
        DB::beginTransaction();
        
        try {
            // Upload KTM photo if provided
            if (isset($data['foto_ktm'])) {
                $data['foto_ktm'] = $this->fileUploadService->uploadKTMPhoto($data['foto_ktm']);
            }

            // Calculate return date - convert lama_hari to integer
            $lamaDays = (int) $data['lama_hari'];
            $data['tanggal_pinjam'] = Carbon::now();
            $data['tanggal_kembali'] = Carbon::now()->addDays($lamaDays);
            $data['status'] = 'pending';

            $borrowing = Borrowing::create($data);

            // Send notification to item owner
            $this->notificationService->sendBorrowingRequestNotification($borrowing);

            DB::commit();
            return $borrowing;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve a borrowing request
     *
     * @param int $borrowingId
     * @return Borrowing
     */
    public function approveBorrowing(int $borrowingId): Borrowing
    {
        DB::beginTransaction();
        
        try {
            $borrowing = Borrowing::findOrFail($borrowingId);
            $item = $borrowing->item;

            // Check if item is still available
            if ($item->stok <= 0) {
                throw new \Exception('Stok barang tidak tersedia');
            }

            // Decrease item stock
            $item->decrement('stok');

            // Update borrowing status
            $borrowing->update(['status' => 'approved']);

            // Send notification to borrower
            $this->notificationService->sendBorrowingApprovedNotification($borrowing);

            DB::commit();
            return $borrowing;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Reject a borrowing request
     *
     * @param int $borrowingId
     * @param string|null $reason
     * @return Borrowing
     */
    public function rejectBorrowing(int $borrowingId, ?string $reason = null): Borrowing
    {
        DB::beginTransaction();
        
        try {
            $borrowing = Borrowing::findOrFail($borrowingId);

            // Update borrowing status
            $borrowing->update([
                'status' => 'rejected',
                'alasan_penolakan' => $reason,
            ]);

            // Send notification to borrower
            $this->notificationService->sendBorrowingRejectedNotification($borrowing, $reason);

            DB::commit();
            return $borrowing;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Request return for a borrowing
     *
     * @param int $borrowingId
     * @param array $data
     * @return Borrowing
     */
    public function requestReturn(int $borrowingId, array $data): Borrowing
    {
        DB::beginTransaction();
        
        try {
            $borrowing = Borrowing::findOrFail($borrowingId);

            // Upload return photo if provided
            if (isset($data['foto_kondisi'])) {
                $data['foto_kondisi'] = $this->fileUploadService->uploadReturnPhoto($data['foto_kondisi']);
            }

            // Update borrowing with return details
            $borrowing->update([
                'kondisi' => $data['kondisi'] ?? null,
                'foto_kondisi' => $data['foto_kondisi'] ?? null,
                'status' => 'return_pending',
            ]);

            // Send notification to item owner
            $this->notificationService->sendReturnRequestNotification($borrowing);

            DB::commit();
            return $borrowing;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Approve a return request
     *
     * @param int $borrowingId
     * @param int|null $rating
     * @return Borrowing
     */
    public function approveReturn(int $borrowingId, ?int $rating = null): Borrowing
    {
        DB::beginTransaction();
        
        try {
            $borrowing = Borrowing::findOrFail($borrowingId);
            $item = $borrowing->item;

            // Increase item stock
            $item->increment('stok');

            // Update borrowing status
            $borrowing->update([
                'status' => 'returned',
                'tanggal_pengembalian_aktual' => Carbon::now(),
                'rating' => $rating,
            ]);

            // Send notification to borrower
            $this->notificationService->sendReturnApprovedNotification($borrowing);

            DB::commit();
            return $borrowing;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Check for overdue borrowings and send notifications
     *
     * @return array Count of overdue borrowings
     */
    public function checkOverdue(): array
    {
        $overdrueBorrowings = Borrowing::where('status', 'approved')
            ->where('tanggal_kembali', '<', Carbon::now())
            ->get();

        $count = 0;
        foreach ($overdrueBorrowings as $borrowing) {
            $this->notificationService->sendOverdueNotification($borrowing);
            $count++;
        }

        return ['count' => $count, 'borrowings' => $overdrueBorrowings];
    }

    /**
     * Get borrowings for a user based on their role
     *
     * @param int $userId
     * @param string $role
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getUserBorrowings(int $userId, string $role)
    {
        if ($role === 'peminjam') {
            // Get borrowings made by the user
            return Borrowing::where('peminjam_id', $userId)
                ->with(['item', 'item.user'])
                ->orderBy('created_at', 'desc')
                ->get();
        } else {
            // Get borrowings for items owned by the user
            return Borrowing::whereHas('item', function($query) use ($userId) {
                $query->where('user_id', $userId);
            })
            ->with(['item', 'peminjam'])
            ->orderBy('created_at', 'desc')
            ->get();
        }
    }

    /**
     * Cancel a borrowing request
     *
     * @param int $borrowingId
     * @return Borrowing
     */
    public function cancelBorrowing(int $borrowingId): Borrowing
    {
        $borrowing = Borrowing::findOrFail($borrowingId);
        
        $borrowing->update(['status' => 'cancelled']);
        
        return $borrowing;
    }
}

