<?php
// app/Model/UserSharedPost.php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserSharedPost extends Model
{
    protected $fillable = ['user_id', 'post_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }
}

