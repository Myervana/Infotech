<?php

namespace App\Models;
use App\Models\users;


use Illuminate\Foundation\Auth\User as Authenticatable;

class Account extends Authenticatable
{
    protected $table = 'users'; // <- This line is critical

    protected $fillable = [
        'full_name', 'email', 'password', 'age', 'address', 'photo'
    ];

    protected $hidden = ['password'];
}
