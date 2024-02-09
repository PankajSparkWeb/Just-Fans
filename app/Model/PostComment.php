<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class PostComment extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'post_id', 'message', 'comment_parent_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
    ];

    /*
     * Relationships
     */

    public function author()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function post()
    {
        return $this->belongsTo('App\Model\Post');
    }
 

    // In your PostComment model

    public function replies()
    {
        return $this->hasMany(PostComment::class, 'comment_parent_id');
    }

    public function parentComment()
    {
        return $this->belongsTo(PostComment::class, 'comment_parent_id');
    }


    public function reactions()
    {
        return $this->hasMany('App\Model\Reaction');
    }   
        
    public function getCountReactionsAttribute()
    {
        // Ensure that 'reactions' relationship is loaded
        if ($this->relationLoaded('reactions')) {
            $postReactions = $this->reactions;
    
            $likeCount = $postReactions->where('reaction_type', 'like')->count();
            $dislikeCount = $postReactions->where('reaction_type', 'dislike')->count();
    
            // You may further filter based on 'post_id' and 'id' columns if needed
            // For example:
            // $likeCount = $postReactions->where('reaction_type', 'like')->where('post_id', $this->id)->count();
            // $dislikeCount = $postReactions->where('reaction_type', 'dislike')->where('post_id', $this->id)->count();
            $total_votes = $likeCount - $dislikeCount;
            return formatReactionCount($total_votes);
            //return $total_votes > 0 ? $total_votes : 0;
        } else {
            // If 'reactions' relationship is not loaded, you may choose to load it or return a default value
            return 0; // Default value when the relationship is not loaded
        }
    }
}
