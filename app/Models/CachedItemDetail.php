<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CachedItemDetail extends Model {
    protected $fillable = ['asset_id','name','creator_name','category','is_limited','rap','raw_details','cached_at'];
    protected $casts = ['raw_details' => 'array','is_limited' => 'boolean','cached_at' => 'datetime'];
}