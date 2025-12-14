<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Borrowing extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'peminjam_id',
        'item_id',
        'foto_ktm',
        'tanggal_pinjam',
        'tanggal_kembali',
        'lama_hari',
        'tanggal_pengembalian_aktual',
        'status', // 'pending', 'approved', 'rejected', 'returned', 'cancelled', 'return_pending'
        'catatan',
        'kondisi',
        'foto_kondisi',
        'rating',
        'alasan_penolakan',
    ];

    /**
     * The attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_pinjam' => 'date',
            'tanggal_kembali' => 'date',
            'tanggal_pengembalian_aktual' => 'date',
            'rating' => 'integer',
        ];
    }

    /**
     * Get the peminjam (borrower) that made the borrowing.
     */
    public function peminjam()
    {
        return $this->belongsTo(User::class, 'peminjam_id');
    }

    /**
     * Get the item being borrowed.
     */
    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    /**
     * Get the costumer (owner) through the item.
     */
    public function costumer()
    {
        return $this->hasOneThrough(
            User::class,
            Item::class,
            'id', // Foreign key on items table
            'id', // Foreign key on users table
            'item_id', // Local key on borrowings table
            'user_id' // Local key on items table
        );
    }

    /**
     * Check if the borrowing is overdue.
     */
    public function isOverdue()
    {
        if ($this->status !== 'approved') {
            return false;
        }

        return Carbon::now()->isAfter($this->tanggal_kembali) && 
               is_null($this->tanggal_pengembalian_aktual);
    }

    /**
     * Get the number of days late.
     */
    public function daysLate()
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        $returnDate = $this->tanggal_pengembalian_aktual ?? Carbon::now();
        return $this->tanggal_kembali->diffInDays($returnDate);
    }

    /**
     * Scope a query to only include pending borrowings.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include approved borrowings.
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope a query to only include returned borrowings.
     */
    public function scopeReturned($query)
    {
        return $query->where('status', 'returned');
    }
}

