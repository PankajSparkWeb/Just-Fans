<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class UserList extends Model
{
    const FOLLOWERS_TYPE = 'followers';
    const FOLLOWING_TYPE = 'following';
    const BLOCKED_TYPE = 'blocked';
    const CUSTOM_TYPE = 'custom';

    public $notificationTypes = [
        self::FOLLOWERS_TYPE,
        self::FOLLOWING_TYPE,
        self::BLOCKED_TYPE,
        self::CUSTOM_TYPE,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'name', 'type',
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

    public function user()
    {
        return $this->hasOne('App\User', 'id', 'user_id')->with(['posts']);
    }

    public function members()
    {
        return $this->hasMany('App\Model\UserListMember', 'list_id');
    }

    public function getMembersUsers()
    {
        $filteredUsers = [];
        foreach ($this->members as $member) {
            $filteredUsers[] = $member->user;
        }
        return collect($filteredUsers);
    }

}
