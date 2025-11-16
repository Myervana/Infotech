<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'fullset_id',
        'room_item_id',
        'note',
        'reason',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the room items that belong to this fullset
     */
    public function roomItems()
    {
        return $this->hasMany(RoomItem::class, 'full_set_id', 'fullset_id');
    }

    /**
     * Get the specific room item for item-level notes
     */
    public function roomItem()
    {
        return $this->belongsTo(RoomItem::class, 'room_item_id');
    }
}