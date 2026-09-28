<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CachedUser extends Model {
    protected $fillable = ['roblox_user_id','username','display_name','description',
        'account_created_at','avatar_url','raw_profile','cached_at'];
    protected $casts = ['raw_profile' => 'array','account_created_at' => 'datetime','cached_at' => 'datetime'];
}