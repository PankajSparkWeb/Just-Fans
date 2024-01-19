<?php

// History.php
namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    protected $fillable = ['user_id', 'post_id', 'comment_id', 'action'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function postComment()
    {
        return $this->belongsTo(PostComment::class, 'comment_id');
    }
}

