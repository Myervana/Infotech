<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BorrowExtension extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrow_id',
        'extension_duration',
        'days_added',
        'reason',
        'previous_due_date',
        'new_due_date',
        'extended_at',
    ];

    protected $casts = [
        'previous_due_date' => 'datetime',
        'new_due_date' => 'datetime',
        'extended_at' => 'datetime',
    ];

    /**
     * Relationship: This extension belongs to a Borrow
     */
    public function borrow()
    {
        return $this->belongsTo(Borrow::class);
    }
}

