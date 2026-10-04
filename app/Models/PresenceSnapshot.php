<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PresenceSnapshot extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'roblox_user_id',
        'status_key',
        'game_name',
        'place_id',
        'universe_id',
        'observed_at',
    ];

    protected function casts(): array
    {
        return [
            'observed_at' => 'datetime',
        ];
    }
}
