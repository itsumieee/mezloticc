<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CachedInventory extends Model {
    protected $fillable = ['roblox_user_id','category','items','total_count','cached_at'];
    protected $casts = ['items' => 'array','cached_at' => 'datetime'];
}