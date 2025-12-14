<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama',
        'stok',
        'deskripsi',
        'max_hari',
        'gambar',
        'user_id',
    ];

    /**
     * Get the user that owns the item.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the borrowings for the item.
     */
    public function borrowings()
    {
        return $this->hasMany(Borrowing::class);
    }

    /**
     * Check if item is available for borrowing.
     */
    public function isAvailable()
    {
        $currentBorrowings = $this->borrowings()
            ->whereIn('status', ['pending', 'approved'])
            ->count();
        
        return $this->stok > $currentBorrowings;
    }
}
