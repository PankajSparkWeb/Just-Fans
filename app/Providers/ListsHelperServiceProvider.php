<?php

namespace App\Providers;

use App\Model\UserList;
use App\Model\UserListMember;
use App\Model\UserReport;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;

class ListsHelperServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Check if an user is part of a list.
     *
     * @param $lists
     * @param $user_id
     * @return bool
     */
    public static function isMemberList($lists, $user_id)
    {
        foreach ($lists as $list) {
            if ($user_id == $list->id) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns list details (posts and members counts).
     *
     * @param $list_id
     * @param $user_id
     * @return array
     */
    public static function getListDetails($list_id, $user_id)
    {
        $list = UserList::with(['members', 'members.user', 'members.userPosts'])->where('id', $list_id)->first();
        $postsCount = 0;
        foreach ($list->members as $member) {
            $postsCount += count($member->userPosts->posts);
        }

        return [
            'posts_count' => $postsCount,
            'members_count' => count($list->members),
        ];
    }

    /**
     * Returns users within a list.
     * @param $listID
     * @return mixed
     */
    public static function getListMembers($listID)
    {
        $users = UserListMember::where('list_id', $listID)->select('user_id')->get()->pluck('user_id')->toArray();

        return $users;
    }

    /**
     * Returns all the lists of an user.
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public static function getUserLists()
    {
        $lists = UserList::with(['members', 'members.user', 'members.userPosts'])->where('user_id', Auth::user()->id)->get()->each(function ($item, $key) {
            $item->posts_count = 0;
            foreach ($item->members as $member) {
                $item->posts_count += count($member->userPosts->posts);
            }
            $item->members = self::getUsersForListMembers($item->members);
            return $item;
        });
        return $lists;
    }

    /**
     * Creates a "virtual" list, holding all of the user followers
     * @return UserList
     */
    public static function getUserFollowersList(){
        $followersList = new UserList();
        $followersList->name = __("Followers");
        $followersList->type = UserList::FOLLOWERS_TYPE;
        $followersList->user_id = Auth::user()->id;
        $followersList->posts_count = 0;
        $followers = ListsHelperServiceProvider::getUserFollowers(Auth::user()->id);
        $followers = collect($followers)->pluck('user_id');
        $followers = User::whereIn('id',$followers)->withCount('posts')->get();
        $followersList->posts_count = 0;
        foreach($followers as $follower){
            $followersList->posts_count += $follower->posts_count;
        }
        $followersList->members = $followers;
        return $followersList;
    }

    public static function getUsersForListMembers($members){
        $filteredUsers = [];
        foreach ($members as $member) {
            $filteredUsers[] = $member->user;
        }
        $members = collect($filteredUsers);
        return $members;
    }

    /**
     * Creates default followers and blocked lists for an user.
     *
     * @param $user_id
     */
    public static function createUserDefaultLists($user_id)
    {
        UserList::insert([
            [
                'user_id' => $user_id,
                'type' => 'following',
                'name' => 'Following',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
            [
                'user_id' => $user_id,
                'type' => 'blocked',
                'name' => 'Blocked',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ],
        ]);
    }

    /**
     * Add/Remove users out of following/block lists easily.
     *
     * @param $user_id
     * @param $recipient_id
     * @param $type
     * @return \Illuminate\Http\JsonResponse
     */
    public static function managePredefinedUserMemberList($user_id, $recipient_id, $type)
    {
        $listFollowID = UserList::where('user_id', $user_id);
        if ($type == 'follow') {
            $listFollowID = $listFollowID->where('type', 'following')->select('id')->first();
            return self::addListMember($listFollowID->id, $recipient_id, false);
        } elseif ($type == 'unfollow') {
            $listFollowID = $listFollowID->where('type', 'following')->select('id')->first();
            return self::deleteListMember($listFollowID->id, $recipient_id, false);
        }
    }

    /**
     * Adds a member to a list.
     *
     * @param $listID
     * @param $userID
     * @param bool $returnData
     * @return \Illuminate\Http\JsonResponse
     */
    public static function addListMember($listID, $userID, $returnData = true)
    {
        try {
            $data = [
                'list_id' => $listID,
                'user_id' => $userID,
            ];
            UserListMember::create($data);
            if ($returnData) {
                return response()->json(['success' => true, 'message' => __('Member added to list.'), 'data' => self::getListDetails($listID, $userID)]);
            } else {
                return response()->json(['success' => true, 'message' => __('Member added to list.')]);
            }
        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'errors' => [__('An internal error has occurred.')], 'message'=>$exception->getMessage()]);
        }
    }

    /**
     * Deletes a member out a list.
     *
     * @param $listID
     * @param $userID
     * @param bool $returnData
     * @return \Illuminate\Http\JsonResponse
     */
    public static function deleteListMember($listID, $userID, $returnData = true)
    {
        try {
            $data = [
                'list_id' => $listID,
                'user_id' => $userID,
            ];
            UserListMember::where($data)->first()->delete();
            if ($returnData) {
                return response()->json(['success' => true, 'message' => __('Member removed from list.'), 'data' => self::getListDetails($listID, $userID)]);
            } else {
                return response()->json(['success' => true, 'message' => __('Member removed from list.')]);
            }
        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'errors' => [__('An internal error has occurred.')], 'message'=>$exception->getMessage()]);
        }
    }

    /**
     * Returns list of report types.
     * @return array
     */
    public static function getReportTypes()
    {
        return UserReport::$typesMap;
    }

    /**
     * Check if current logged user is following specific user
     * @param $userId
     * @return bool
     */
    public static function loggedUserIsFollowingUser($userId)
    {
        if(Auth::user()){
            $loggedUserId = Auth::user()->id;
            $userFollowersListId = UserList::query()->where(['user_id' => $loggedUserId, 'type' => 'following'])->select('id')->first();
            if ($userFollowersListId != null) {
                $userListMember = UserListMember::query()->where(['user_id' => $userId, 'list_id' => $userFollowersListId->id])->select('id')->first();
                if ($userListMember != null) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Checks follow relation between two users
     * @param $fromUserID
     * @param $toUserID
     * @return bool
     */
    public static function isUserFollowing($fromUserID, $toUserID){
        $loggedUserId = $fromUserID;
        $userId = $toUserID;
        $userFollowersListId = UserList::query()->where(['user_id' => $loggedUserId, 'type' => 'following'])->select('id')->first();
        if ($userFollowersListId != null) {
            $userListMember = UserListMember::query()->where(['user_id' => $userId, 'list_id' => $userFollowersListId->id])->select('id')->first();
            if ($userListMember != null) {
                return true;
            }
        }
        return false;
    }

    /**
     * Check current user following type
     * @param $userId
     * @param bool $getTranslated
     * @return array|\Illuminate\Contracts\Translation\Translator|string|null
     */
    public static function getUserFollowingType($userId, $getTranslated = false)
    {
        if (self::loggedUserIsFollowingUser($userId)) {
            if($getTranslated){
                return __('Unfollow');
            }
            return 'unfollow';
        } else {
            if($getTranslated){
                return __('Follow');
            }
            return 'follow';
        }
    }

    public static function getUserFollowers($userID){
        $followers = UserListMember::select('user_lists.user_id','users.email', 'users.settings', 'users.name')
            ->join('user_lists', 'user_list_members.list_id', '=', 'user_lists.id')
            ->join('users', 'users.id', '=', 'user_lists.user_id')
            ->where('user_list_members.user_id', $userID)
            ->where('user_lists.type', 'following')
            ->get()
            ->toArray();
        return $followers;
    }

}
