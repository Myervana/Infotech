<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Item extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'room_title',
        'device_category',
        'device_type',
        'brand',
        'model',
        'serial_number',
        'description',
        'status',
        'photo',
        'barcode',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Scopes for filtering
    public function scopeByRoom($query, $room)
    {
        return $query->where('room_title', $room);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('device_category', $category);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeUsable($query)
    {
        return $query->where('status', 'Usable');
    }

    public function scopeUnusable($query)
    {
        return $query->where('status', 'Unusable');
    }

    // Accessors
    public function getPhotoUrlAttribute()
    {
        if ($this->photo) {
            return asset('storage/' . $this->photo);
        }
        return null;
    }

    public function getIsUsableAttribute()
    {
        return $this->status === 'Usable';
    }

    public function getStatusBadgeClassAttribute()
    {
        return $this->status === 'Usable' ? 'badge-usable' : 'badge-unusable';
    }

    // Static methods for getting distinct values
    public static function getDistinctRooms()
    {
        return self::select('room_title')
            ->distinct()
            ->whereNotNull('room_title')
            ->orderBy('room_title')
            ->pluck('room_title');
    }

    public static function getDistinctCategories()
    {
        return self::select('device_category')
            ->distinct()
            ->whereNotNull('device_category')
            ->orderBy('device_category')
            ->pluck('device_category');
    }

    public static function getDistinctBrands()
    {
        return self::select('brand')
            ->distinct()
            ->whereNotNull('brand')
            ->orderBy('brand')
            ->pluck('brand');
    }

    // Helper methods
    public function getShortDescription($limit = 30)
    {
        return \Illuminate\Support\Str::limit($this->description, $limit);
    }

    public function hasPhoto()
    {
        return !empty($this->photo);
    }

    public function getFormattedCreatedAt()
    {
        return $this->created_at->format('M d, Y');
    }

    public function getFormattedUpdatedAt()
    {
        return $this->updated_at->format('M d, Y g:i A');
    }
}