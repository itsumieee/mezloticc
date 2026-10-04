<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Watchlist extends Model
{
    protected $fillable = [
        'roblox_user_id',
        'username',
        'display_name',
        'note',
        'last_checked_at',
    ];

    protected function casts(): array
    {
        return ['last_checked_at' => 'datetime'];
    }
}