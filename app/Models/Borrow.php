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
        'reason',
        'borrow_duration',
        'due_date',
    ];

    protected $casts = [
        'borrow_date' => 'datetime',
        'return_date' => 'datetime',
        'due_date' => 'datetime',
    ];

    /**
     * Relationship: This borrow record belongs to a RoomItem
     */
    public function roomItem()
    {
        return $this->belongsTo(RoomItem::class, 'room_item_id');
    }

    /**
     * Relationship: This borrow has many extensions
     */
    public function extensions()
    {
        return $this->hasMany(BorrowExtension::class);
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

    /**
     * Accessor: Check if the item is overdue
     */
    public function getIsOverdueAttribute()
    {
        if ($this->status !== 'Borrowed' || !$this->due_date) {
            return false;
        }
        return now()->greaterThan($this->due_date);
    }

    /**
     * Scope: Only overdue items
     */
    public function scopeOverdue($query)
    {
        return $query->where('status', 'Borrowed')
            ->whereNotNull('due_date')
            ->where('due_date', '<', now());
    }
}
