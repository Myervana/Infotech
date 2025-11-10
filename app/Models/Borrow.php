<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Borrow extends Model
{
    use HasFactory;

    protected $fillable = [
        'room_item_id',
        'borrower_name',
        'borrower_photo',
        'position',
        'department',
        'latitude',
        'longitude',
        'borrow_date',
        'return_date',
        'status', // 'Borrowed' or 'Returned'
    ];

    /**
     * Relationship: This borrow record belongs to a RoomItem
     */
    public function roomItem()
    {
        return $this->belongsTo(RoomItem::class, 'room_item_id');
    }

    /**
     * Scope: Only currently borrowed items
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'Borrowed');
    }

    /**
     * Scope: Only returned items
     */
    public function scopeReturned($query)
    {
        return $query->where('status', 'Returned');
    }

    /**
     * Accessor: Check if the item is returned
     */
    public function getIsReturnedAttribute()
    {
        return $this->status === 'Returned';
    }

    /**
     * Accessor: Check if the item is currently borrowed
     */
    public function getIsBorrowedAttribute()
    {
        return $this->status === 'Borrowed';
    }
}
