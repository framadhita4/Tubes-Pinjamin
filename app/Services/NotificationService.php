<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;

class NotificationService
{
    /**
     * Send notification for new borrowing request
     *
     * @param \App\Models\Borrowing $borrowing
     * @return \App\Models\Notification
     */
    public function sendBorrowingRequestNotification($borrowing)
    {
        $item = $borrowing->item;
        $peminjam = $borrowing->peminjam;
        
        return Notification::create([
            'user_id' => $item->user_id, // Notify the item owner
            'borrowing_id' => $borrowing->id,
            'type' => 'borrowing_request',
            'title' => 'Permintaan Peminjaman Baru',
            'message' => "{$peminjam->name} ingin meminjam {$item->nama}",
            'is_read' => false,
        ]);
    }

    /**
     * Send notification when borrowing is approved
     *
     * @param \App\Models\Borrowing $borrowing
     * @return \App\Models\Notification
     */
    public function sendBorrowingApprovedNotification($borrowing)
    {
        $item = $borrowing->item;
        
        return Notification::create([
            'user_id' => $borrowing->peminjam_id, // Notify the borrower
            'borrowing_id' => $borrowing->id,
            'type' => 'borrowing_approved',
            'title' => 'Peminjaman Disetujui',
            'message' => "Peminjaman {$item->nama} Anda telah disetujui oleh pemilik",
            'is_read' => false,
        ]);
    }

    /**
     * Send notification when borrowing is rejected
     *
     * @param \App\Models\Borrowing $borrowing
     * @param string|null $reason
     * @return \App\Models\Notification
     */
    public function sendBorrowingRejectedNotification($borrowing, $reason = null)
    {
        $item = $borrowing->item;
        $message = "Peminjaman {$item->nama} Anda ditolak oleh pemilik";
        
        if ($reason) {
            $message .= ". Alasan: {$reason}";
        }
        
        return Notification::create([
            'user_id' => $borrowing->peminjam_id, // Notify the borrower
            'borrowing_id' => $borrowing->id,
            'type' => 'borrowing_rejected',
            'title' => 'Peminjaman Ditolak',
            'message' => $message,
            'is_read' => false,
        ]);
    }

    /**
     * Send notification for return request
     *
     * @param \App\Models\Borrowing $borrowing
     * @return \App\Models\Notification
     */
    public function sendReturnRequestNotification($borrowing)
    {
        $item = $borrowing->item;
        $peminjam = $borrowing->peminjam;
        
        return Notification::create([
            'user_id' => $item->user_id, // Notify the item owner
            'borrowing_id' => $borrowing->id,
            'type' => 'return_request',
            'title' => 'Permintaan Pengembalian',
            'message' => "{$peminjam->name} mengajukan pengembalian {$item->nama}",
            'is_read' => false,
        ]);
    }

    /**
     * Send notification when return is approved
     *
     * @param \App\Models\Borrowing $borrowing
     * @return \App\Models\Notification
     */
    public function sendReturnApprovedNotification($borrowing)
    {
        $item = $borrowing->item;
        
        return Notification::create([
            'user_id' => $borrowing->peminjam_id, // Notify the borrower
            'borrowing_id' => $borrowing->id,
            'type' => 'return_approved',
            'title' => 'Pengembalian Disetujui',
            'message' => "Pengembalian {$item->nama} telah disetujui. Terima kasih!",
            'is_read' => false,
        ]);
    }

    /**
     * Send overdue notification
     *
     * @param \App\Models\Borrowing $borrowing
     * @return void
     */
    public function sendOverdueNotification($borrowing)
    {
        $item = $borrowing->item;
        $daysLate = $borrowing->daysLate();
        
        // Notify borrower
        Notification::create([
            'user_id' => $borrowing->peminjam_id,
            'borrowing_id' => $borrowing->id,
            'type' => 'borrowing_reminder',
            'title' => 'Peminjaman Terlambat',
            'message' => "Peminjaman {$item->nama} Anda sudah terlambat {$daysLate} hari. Segera kembalikan!",
            'is_read' => false,
        ]);
        
        // Notify owner
        Notification::create([
            'user_id' => $item->user_id,
            'borrowing_id' => $borrowing->id,
            'type' => 'borrowing_reminder',
            'title' => 'Peminjaman Terlambat',
            'message' => "Peminjaman {$item->nama} oleh {$borrowing->peminjam->name} terlambat {$daysLate} hari",
            'is_read' => false,
        ]);
    }

    /**
     * Get unread notifications count for a user
     *
     * @param int $userId
     * @return int
     */
    public function getUnreadCount(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->count();
    }

    /**
     * Mark notification as read
     *
     * @param int $notificationId
     * @return bool
     */
    public function markAsRead(int $notificationId): bool
    {
        $notification = Notification::find($notificationId);
        
        if ($notification) {
            return $notification->update(['is_read' => true]);
        }
        
        return false;
    }

    /**
     * Mark all notifications as read for a user
     *
     * @param int $userId
     * @return int Number of notifications updated
     */
    public function markAllAsRead(int $userId): int
    {
        return Notification::where('user_id', $userId)
            ->where('is_read', false)
            ->update(['is_read' => true]);
    }
}

