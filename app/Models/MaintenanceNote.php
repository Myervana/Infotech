<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MaintenanceNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'fullset_id',
        'note',
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
}