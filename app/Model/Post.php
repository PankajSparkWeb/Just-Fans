<?php

namespace App\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class Post extends Model
{


    const PENDING_STATUS = 0;
    const APPROVED_STATUS = 1;
    const DISAPPROVED_STATUS = 2;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'text',
        'price',
        'status',
        'external_post_link',
        'release_date',
        'expire_date',
        'is_pinned'
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


    public function getIsExpiredAttribute(){
        if($this->expire_date > Carbon::now()){
            return false;
        }
        return true;
    }

    public function getIsScheduledAttribute(){
        if($this->release_date > Carbon::now()){
            return true;
        }
        return false;
    }

    /*
     * Relationships
     */

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id');
    }

    public function comments()
    {
        return $this->hasMany('App\Model\PostComment');
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
        } else {
            // If 'reactions' relationship is not loaded, you may choose to load it or return a default value
            return 0; // Default value when the relationship is not loaded
        }
    }
     

    public function bookmarks()
    {
        return $this->hasMany('App\Model\UserBookmark');
    }

    public function attachments()
    {
        return $this->hasMany('App\Model\Attachment');
    }

    public function transactions()
    {
        return $this->hasMany('App\Model\Transaction');
    }

    public function postPurchases()
    {
        return $this->hasMany('App\Model\Transaction', 'post_id', 'id')->where('status', 'approved')->where('type', 'post-unlock');
    }

    public function tips()
    {
        return $this->hasMany('App\Model\Transaction')->where('type', 'tip')->where('status', 'approved');
    }

    public static function getStatusName($status){
        switch ($status){
            case self::PENDING_STATUS:
                return __("pending");
                break;
            case self::APPROVED_STATUS:
                return __("approved");
                break;
            case self::DISAPPROVED_STATUS:
                return __("disapproved");
                break;
        }
    }

    // Scopes
    public function scopeNotExpiredAndReleased($query){
        $query->where(function($query) {
            $query->where('release_date', '<', Carbon::now());
            $query->orWhere('release_date',null);
        });
        $query->where(function($query) {
            $query->where('expire_date', '>', Carbon::now());
            $query->orWhere('expire_date', null);
        });
    }

    public function interests(): BelongsToMany
    {
        return $this->belongsToMany(Newinterest::class, 'post_interest', 'post_id', 'newinterest_id');
    }

    
    public function sharedByUsers()
    {
        return $this->hasMany(UserSharedPost::class);
    }

    public function getShareCountAttribute()
    {
        return $this->sharedByUsers()->count();
    }
    public function is_visited_post(){
        // Assuming you have access to the current user's ID (replace 'auth()->id()' with the actual way you get the user's ID)
        $currentUserId = auth()->id();    
        return $this->hasOne(History::class, 'post_id')->where('user_id', $currentUserId);
    }
}
