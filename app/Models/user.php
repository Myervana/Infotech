<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class user extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'photo',
        'mobile',
        'is_approved',
        'is_new_user',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_approved' => 'boolean',
        'is_new_user' => 'boolean',
    ];

    public function roomItems()
    {
        return $this->hasMany(RoomItem::class);
    }

    public function deviceBindings()
    {
        return $this->hasMany(DeviceBinding::class);
    }

    public function primaryDevice()
    {
        return $this->hasOne(DeviceBinding::class)->where('is_primary', true);
    }
}