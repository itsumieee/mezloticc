<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OgImage extends Model
{
    protected $fillable = ['roblox_user_id', 'path', 'generated_at'];

    protected function casts(): array
    {
        return ['generated_at' => 'datetime'];
    }
}