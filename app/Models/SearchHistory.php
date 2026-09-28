<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class SearchHistory extends Model {
    protected $fillable = ['query','resolved_user_id','ip_address'];
}