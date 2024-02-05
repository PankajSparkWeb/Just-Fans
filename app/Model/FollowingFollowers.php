<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FollowingFollowers extends Model
{
    use HasFactory;
    protected $table = 'user_lists';
    protected $fillable = ['column1', 'column2'];
    // Other model configurations or relationships
}
