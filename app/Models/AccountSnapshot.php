<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountSnapshot extends Model
{
    protected $fillable = [
        'roblox_user_id',
        'limited_count',
        'total_rap',
        'average_rap',
        'wearing_count',
        'bundles_count',
        'snapshot_date',
    ];

    protected function casts(): array
    {
        return ['snapshot_date' => 'date'];
    }
}