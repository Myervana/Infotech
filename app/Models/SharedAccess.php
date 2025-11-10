<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SharedAccess extends Model
{
    use HasFactory;

    protected $table = 'shared_access';

    protected $fillable = [
        'owner_user_id',
        'shared_user_id',
        'share_token_id',
        'revoked_at',
    ];

    protected $casts = [
        'revoked_at' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(user::class, 'owner_user_id');
    }

    public function sharedUser(): BelongsTo
    {
        return $this->belongsTo(user::class, 'shared_user_id');
    }

    public function shareToken(): BelongsTo
    {
        return $this->belongsTo(ShareToken::class);
    }
}


