<?php

namespace App\Providers;

use App\Model\Attachment;
use App\Model\Post;
use App\Model\PostComment;
use App\Model\Stream;
use App\Model\Subscription;
use App\Model\Transaction;
use App\Model\UserList;
use App\Model\PostHide;
use App\Model\UserSharedPost;
use App\Model\SavePost;
use App\Model\UserlearnedPost;
use App\User;
use Carbon\Carbon;
use Cookie;
use DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use View;
use Illuminate\Support\Facades\Session;

class PostsHelperServiceProvider extends ServiceProvider
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
     * Get latest user attachments.
     *
     * @param bool $userID
     * @param bool $type
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     * @throws \Exception
     */
    public static function getLatestUserAttachments($userID = false, $type = false)
    {
        if (! $userID) {
            if (Auth::check()) {
                $userID = Auth::user()->id;
            } else {
                throw new \Exception(__('Can not fetch latest post attachments for this profile.'));
            }
        }
        $attachments = Attachment::with(['post'])->where('attachments.post_id', '<>', null)->where('attachments.user_id', $userID);

        if ($type) {
            $extensions = AttachmentServiceProvider::getTypeByExtension('image');
            $attachments->whereIn('attachments.type', $extensions);
        }
        // validate access for paid posts attachments
        if(Auth::check() && Auth::user()->role_id !== 1 && Auth::user()->id !== $userID) {
            $attachments->leftJoin('posts', 'posts.id', '=', 'attachments.post_id')
                ->leftJoin('transactions', 'transactions.post_id', '=', 'posts.id')
                ->where(function ($query) {
                    $query->where('posts.price', '=', floatval(0))
                        ->orWhere(function ($query) {
                            $query->where('transactions.id', '<>', null)
                                ->where('transactions.type', '=', Transaction::POST_UNLOCK)
                                ->where('transactions.status', '=', Transaction::APPROVED_STATUS)
                                ->where('transactions.sender_user_id', '=', Auth::user()->id);
                        });
                })
                ->where(function($query) {
                    $query->where('posts.expire_date', '>', Carbon::now());
                    $query->orWhere('posts.expire_date', null);
                })
                ->where(function($query) {
                    $query->where('posts.release_date', '<', Carbon::now());
                    $query->orWhere('posts.release_date',null);
                })
                ->where('posts.status', 1);
        }
        $attachments = $attachments->limit(3)->orderByDesc('attachments.created_at')->get();

        return $attachments;
    }

    /**
     * Get user by it's username.
     *
     * @param $username
     * @return mixed
     */
    public static function getUserByUsername($username)
    {
        return User::where('username', $username)->first();
    }

    /**
     * Get user's all active subs.
     *
     * @param $userID
     * @return mixed
     */
    public static function getUserActiveSubs($userID)
    {
        $activeSubs = Subscription::where('sender_user_id', $userID)
            ->where(function ($query) {
                $query->where('status', 'completed')
                    ->orwhere([
                        ['status', '=', 'canceled'],
                        ['expires_at', '>', Carbon::now()->toDateTimeString()],
                    ]);
            })
            ->get()
            ->pluck('recipient_user_id')->toArray();

        return $activeSubs;
    }

    /**
     * Get following users with free profiles
     * @param $userId
     * @return mixed
     */
    public static function getFreeFollowingProfiles($userId){
        $followingList = UserList::where('user_id', $userId)->where('type', 'following')->with(['members','members.user'])->first();
        $followingUserIds = [];
        foreach($followingList->members as $member){
            if(!$member->user->paid_profile || (getSetting('profiles.allow_users_enabling_open_profiles') && $member->user->open_profile)){
                $followingUserIds[] =  $member->user->id;
            }
        }
        return $followingUserIds;
    }

    /**
     * Check if user has active sub to another.
     *
     * @param $sender_id
     * @param $recipient_id
     * @return bool
     */
    public static function hasActiveSub($sender_id, $recipient_id)
    {
        $hasSub = Subscription::where('sender_user_id', $sender_id)
            ->where('recipient_user_id', $recipient_id)
            ->where(function ($query) {
                $query->where('status', 'completed')
                    ->orwhere([
                        ['status', '=', 'canceled'],
                        ['expires_at', '>', Carbon::now()->toDateTimeString()],
                    ]);
            })
            ->count();
        if ($hasSub > 0) {
            return true;
        }

        return false;
    }

    /**
     * Gets list of posts for feed.
     * @param $userID
     * @param bool $encodePostsToHtml
     * @param bool $pageNumber
     * @param bool $mediaType
     * @return array
     */
    public static function getFeedPosts($userID, $encodePostsToHtml = false, $pageNumber = false, $mediaType = false, $sortOrder = false, $searchTerm = '', $feed_type = '')
    {
        $sortOrder = $feed_type == 'hot' ? 'hot' : $sortOrder;

        return self::getFilteredPosts($userID, $encodePostsToHtml, $pageNumber, $mediaType, false, false, false, $sortOrder, $searchTerm, $feed_type);
    }


    /**
     * Gets list of posts for profile.
     * @param $userID
     * @param bool $encodePostsToHtml
     * @param bool $pageNumber
     * @param bool $mediaType
     * @return array
     */
    public static function getUserPosts($userID, $encodePostsToHtml = false, $pageNumber = false, $mediaType = false, $hasSub = false)
    {
        return self::getFilteredPosts($userID, $encodePostsToHtml, $pageNumber, $mediaType, true, $hasSub, false);
    }

    /**
     * Gets list of posts for the bookmarks page.
     * @param $userID
     * @param bool $encodePostsToHtml
     * @param bool $pageNumber
     * @param bool $mediaType
     * @return array
     */
    public static function getUserBookmarks($userID, $encodePostsToHtml = false, $pageNumber = false, $mediaType = false, $hasSub = false)
    {
        return self::getFilteredPosts($userID, $encodePostsToHtml, $pageNumber, $mediaType, false, $hasSub, true);
    }

    
  /**
     * Check if a post is hidden by a user.
     *
     * @param  int  $postId
     * @return bool
     */
    public static function isPostHidden($postId)
    {
        return PostHide::where('post_id', $postId)->where('user_id', auth()->id())->exists();
    }

    /**
     * Check if a post is saved by a user.
     *
     * @param  int  $postId
     * @return bool
     */
    public static function isPostSaved($postId)
    {
        return SavePost::where('post_id', $postId)->where('user_id', auth()->id())->exists();
    }
    /**
     * Check if a post is saved by a user.
     *
     * @param  int  $postId
     * @return bool
     */
    public static function isPostShared($postId)
    {
        return UserSharedPost::where('post_id', $postId)->where('user_id', auth()->id())->exists();
    }
/**
 * Check if a post is learned by a user.
 *
 * @param  int  $postId
 * @return bool
 */
public static function isPostLearned($postId)
{
    return UserlearnedPost::where('post_id', $postId)->where('user_id', auth()->id())->exists();
}


    /**
     * Returns lists of posts, conditioned by different filters.
     * TODO: This one should get refactored a little bit - eg: remove all un-necessary params to differ between these feed pages:
     * feed - profile (logged in/not logged in) - search - bookmarks
     * @param $userID
     * @param bool $encodePostsToHtml
     * @param bool $pageNumber
     * @param bool $mediaType
     * @return array
     */
    public static function getFilteredPosts($userID, $encodePostsToHtml, $pageNumber, $mediaType, $ownPosts, $hasSub, $bookMarksOnly, $sortOrder = false, $searchTerm = '', $feed_type = '')
    {
        $relations = ['user', 'reactions', 'attachments', 'bookmarks', 'postPurchases'];
    
        $posts = Post::withCount('tips')
            ->with($relations);
    
        if ($ownPosts) {
            $posts->where('user_id', $userID);
            if(Auth::check() && Auth::user()->id !== $userID) {
                $posts = self::filterPosts($posts, $userID, 'scheduled');
                $posts = self::filterPosts($posts, $userID, 'approvedPostsOnly');
            }
            elseif (!Auth::check()){
                $posts = self::filterPosts($posts, $userID, 'scheduled');
                $posts = self::filterPosts($posts, $userID, 'approvedPostsOnly');
            }
            $posts = self::filterPosts($posts, $userID, 'pinned');
        }
        elseif ($bookMarksOnly) {
            $posts = self::filterPosts($posts, $userID, 'bookmarks');
            $posts = self::filterPosts($posts, $userID, 'blocked');
        }
        else {            
            $posts = self::filterPosts($posts, $userID, 'all', false, false, '', $feed_type);
        }
    
        if (!$ownPosts) {
            $posts = self::filterPosts($posts, $userID, 'scheduled');
            $posts = self::filterPosts($posts, $userID, 'approvedPostsOnly');
        }
    
        if ($mediaType) {
            $posts = self::filterPosts($posts, $userID, 'media', $mediaType);
        }
    
        if($searchTerm){
            $posts = self::filterPosts($posts, $userID, 'search',false,false,$searchTerm);
        }
    
        $selectedPostId = Session::get('mypostId');
        $perPage = $selectedPostId ? $posts->count() : getSetting('feed.feed_posts_per_page');

        // Paginate the results
        if ($pageNumber) {
            $posts = $posts->paginate($perPage, ['*'], 'page', $pageNumber)->appends(request()->query());
        } else {
            $posts = $posts->paginate($perPage)->appends(request()->query());
        }
    
        if(Auth::check() && Auth::user()->role_id === 1){
            $hasSub = true;
        }
    
        if ($encodePostsToHtml) {
            $data = [
                'total' => $posts->total(),
                'currentPage' => $posts->currentPage(),
                'last_page' => $posts->lastPage(),
                'prev_page_url' => $posts->previousPageUrl(),
                'next_page_url' => $posts->nextPageUrl(),
                'first_page_url' => $posts->nextPageUrl(),
                'hasMore' => $posts->hasMorePages(),
            ];
            $postsData = $posts->map(function ($post) use ($hasSub, $ownPosts, $data) {
                if ($ownPosts) {
                    $post->setAttribute('isSubbed', $hasSub);
                } else {
                    $post->setAttribute('isSubbed', true);
                }
                $post->setAttribute('postPage',$data['currentPage']);
                $post = ['id' => $post->id, 'html' => View::make('elements.feed.post-box')->with('post', $post)->render()];
    
                return $post;
            });
            $data['posts'] = $postsData;
        } else {
            $postsCurrentPage = $posts->currentPage();
            $posts->map(function ($post) use ($hasSub, $ownPosts, $postsCurrentPage) {
                if ($ownPosts) {
                    $post->hasSub = $hasSub;
                    $post->setAttribute('isSubbed', $hasSub);
                } else {
                    $post->setAttribute('isSubbed', true);
                }
                $post->setAttribute('postPage',$postsCurrentPage);
                return $post;
            });
            $data = $posts;
        }
    
        return $data;
    }

    /**
     * Filters out posts using fast, join based queries.
     * @param $posts
     * @param $userID
     * @param $filterType
     * @param bool $mediaType
     * @return mixed
     */
    public static function filterPosts($posts, $userID, $filterType, $mediaType = false, $sortOrder = false, $searchTerm = '', $feed_type = '')
    {
        //if logged in then show posts
        if ( Auth::check() ) {
            $user = Auth::user();
            // Get IDs of posts hidden by the current user
            $hiddenPostIds = $user->hidedPost()->pluck('post_id');
            // Query for posts that are not hidden by the user
            $posts->whereNotIn('posts.id', $hiddenPostIds)->get();                        
        }
        if( $feed_type == 'interest' ){
            $user = Auth::user();
            $userInterests = $user->interests;
            //if ($userInterests && !$userInterests->isEmpty()) {
                $posts->whereHas('interests', function ($query) use ($userInterests) {
                    $query->whereIn('newinterests.id', $userInterests->pluck('id'));
                });
            //}
        }elseif(  $feed_type == 'follow_people'  ){
            $posts->join('user_list_members as following', function ($join) use ($userID) {
                $join->on('following.user_id', '=', 'posts.user_id');
                $join->on('following.list_id', '=', DB::raw(Auth::user()->lists->firstWhere('type', 'following')->id));
            });
        }elseif(  $feed_type == 'hot'  ){

        }else{
            if ($filterType == 'following' || $filterType == 'all') {
                // Followers only
                /*$posts->join('user_list_members as following', function ($join) use ($userID) {
                    $join->on('following.user_id', '=', 'posts.user_id');
                    $join->on('following.list_id', '=', DB::raw(Auth::user()->lists->firstWhere('type', 'following')->id));
                });*/    
                $user = Auth::user();
                $userInterests = $user->interests;
                if ($userInterests && !$userInterests->isEmpty()) {
                    $posts->whereHas('interests', function ($query) use ($userInterests) {
                        $query->whereIn('newinterests.id', $userInterests->pluck('id'));
                    });
                }
            }
        }

        if ($filterType == 'blocked' || $filterType == 'all') {
            // Blocked users
            $blockedUsers = ListsHelperServiceProvider::getListMembers(Auth::user()->lists->firstWhere('type', 'blocked')->id);
            $posts->whereNotIn('posts.user_id', $blockedUsers);
        }
        
        if ($filterType == 'subs' || $filterType == 'all') {
            //get all based on interest
            if( $feed_type == 'interest' ){
                //if interest type

            }elseif( $feed_type == 'hot' ){
                //if hot posts

            }else{
                if($filterType == 'all'){
                    $userIds = array_merge(self::getUserActiveSubs($userID), self::getFreeFollowingProfiles($userID));
                    $posts->whereIn('posts.user_id', $userIds);
                } else {
                    // Subs only
                    $activeSubs = self::getUserActiveSubs($userID);
                    $posts->whereIn('posts.user_id', $activeSubs);
                }
            }
        }

        if ($filterType == 'bookmarks') {
            $posts->join('user_bookmarks', function ($join) use ($userID) {
                $join->on('user_bookmarks.post_id', '=', 'posts.id');
                $join->on('user_bookmarks.user_id', '=', DB::raw($userID));
            });
        }

        if ($filterType == 'media') {
            // This guy is not really that optimal but neither bookmarks is heavy accessed
            $mediaTypes = AttachmentServiceProvider::getTypeByExtension($mediaType);
            $posts->whereHas('attachments', function ($query) use ($mediaTypes) {
                $query->whereIn('type', $mediaTypes);
            });
        }

        if ($filterType == 'search'){
            $posts->where(
                function($query) use ($searchTerm){
                    $query->where('text', 'like', '%'.$searchTerm.'%')
                        ->orWhereHas('user', function($q) use ($searchTerm) {
                            $q->where('username', 'like', '%'.$searchTerm.'%');
                            $q->orWhere('name', 'like', '%'.$searchTerm.'%');
                        });
                }
            );
        }

        if ($filterType == 'pinned'){
            $posts->orderBy('is_pinned','DESC');
        }

        if ($filterType == 'order'){
            if($sortOrder){
                if($sortOrder == 'top'){
                    $relationsCount = ['reactions','comments'];
                    $posts->withCount($relationsCount);
                    $posts->orderBy('comments_count','DESC');
                    $posts->orderBy('reactions_count','DESC');
                }
                elseif($sortOrder =='latest'){
                    $posts->orderBy('created_at','DESC');
                }    
                elseif($sortOrder =='hot'){
                    //HOT POST SORTING                
                    $now = Carbon::now();                
                    $twentyFourHoursAgo = $now->subHours(24);                
                    $relationsCount = ['reactions', 'comments'];
                    // Count the relations
                    $posts->withCount($relationsCount);
                    // Order by the sum of reactions and comments count within the last 24 hours in descending order
                    $posts->orderByRaw('(SELECT COUNT(*) FROM reactions WHERE reactions.post_id = posts.id AND reactions.created_at >= ?) + 
                                        (SELECT COUNT(*) FROM post_comments WHERE post_comments.post_id = posts.id AND post_comments.created_at >= ?) DESC', 
                                        [$twentyFourHoursAgo, $twentyFourHoursAgo]);
                    // If there are no posts within the last 24 hours, order by the total count from the beginning
                    $posts->orderByDesc(DB::raw('(SELECT COUNT(*) FROM reactions WHERE reactions.post_id = posts.id) + 
                    (SELECT COUNT(*) FROM post_comments WHERE post_comments.post_id = posts.id)'));
                }                      
            }else{
                $posts->orderBy('created_at','DESC');
            }
        }

        if ($filterType == 'scheduled') {
            $posts->notExpiredAndReleased();
        }

        if ($filterType == 'approvedPostsOnly') {
            if (!(Auth::check() && (Auth::user()->role_id === 1))) { // Admin can preview all  types of posts
                $posts->where('status', Post::APPROVED_STATUS);
            }
        }

        return $posts;
    }

    /**
     * Returns all comments for a post.
     * @param $post_id
     * @param int $limit
     * @param string $order
     * @param bool $encodePostsToHtml
     * @return array|\Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getPostComments($post_id, $limit = 9, $order = 'DESC', $encodePostsToHtml = false)
{
    $paginatedComments = PostComment::with([
        'author',
        'reactions',
        'replies.author',
        'replies.reactions',
        'replies.replies.author',
        'replies.replies.reactions',
    ])
        ->orderBy('created_at', $order)
        ->where(function ($query) use ($post_id) {
            $query->where('post_id', $post_id)
                ->orWhere('comment_parent_id', $post_id);
        })
        ->paginate($limit);

    // Recursive function to convert nested replies into a tree-like structure
    function buildCommentTree($comments, $parentCommentId = null, $order) {
        $filteredComments = collect();
        foreach ($comments as $comment) {
            if ($comment->comment_parent_id === $parentCommentId) {
                $comment->replies = loadReplies($comment->id, $order);
                $comment->replies = buildCommentTree($comment->replies, $comment->id, $order); // Fetch replies recursively
                $filteredComments->push($comment);
            }
        }
        return $filteredComments;
    }

    // Function to load replies for a specific comment
    function loadReplies($commentId, $order) {
        return PostComment::with([
            'author',
            'reactions',
            'replies.author',
            'replies.reactions',
        ])
            ->where('comment_parent_id', $commentId)
            ->orderBy('created_at', $order)
            ->get();
    }

    // Build the comment tree structure
    $comments = buildCommentTree($paginatedComments, null, $order);

    if ($encodePostsToHtml) {

        // Function to fetch replies HTML recursively
        function fetchRepliesHtml($replies) {
            return $replies->map(function ($reply) {
                try {
                    return [
                        'id'      => $reply->id,
                        'post_id' => $reply->post->id,
                        'html'    => View::make('elements.feed.post-comment')->with(['comment' => $reply])->render(),
                        'replies' => fetchRepliesHtml($reply->replies),
                    ];
                } catch (\Exception $e) {
                    return [
                        'id'      => $reply->id,
                        'post_id' => $reply->post->id,
                        'html'    => "Error rendering reply: " . $e->getMessage(),
                        'replies' => [],
                    ];
                }
            });
        }

        // Map paginated comments to HTML using the recursive structure
        $commentsData = $comments->map(function ($comment) {
            try {
                return [
                    'id'        => $comment->id,
                    'post_id'   => $comment->post->id,
                    'html'      => View::make('elements.feed.post-comment')->with(['comment' => $comment])->render(),
                    'replies'   => fetchRepliesHtml($comment->replies),
                ];
            } catch (\Exception $e) {
                return [
                    'id'        => $comment->id,
                    'post_id'   => $comment->post->id,
                    'html'      => "Error rendering comment: " . $e->getMessage(),
                    'replies'   => [],
                ];
            }
        });
        
        $data = [
            'total' => $paginatedComments->total(),
            'currentPage' => $paginatedComments->currentPage(),
            'last_page' => $paginatedComments->lastPage(),
            'prev_page_url' => $paginatedComments->previousPageUrl(),
            'next_page_url' => $paginatedComments->nextPageUrl(),
            'first_page_url' => $paginatedComments->url(1),
            'last_page_url' => $paginatedComments->url($paginatedComments->lastPage()),
            'hasMore' => $paginatedComments->hasMorePages(),
            'comments' => $commentsData,
        ];
    } else {
        $data = $comments;
    }

    return $data;
}

    


    /**
     * Check if user has unlocked a post.
     * @param $transactions
     * @return bool
     */
    public static function hasUserUnlockedPost($transactions)
    {
        if (Auth::check()) {
            if(Auth::user()->role_id === 1) {
                return true;
            }

            foreach ($transactions as $transaction) {
                if (Auth::user()->id == $transaction->sender_user_id) {
                    return true;
                }
            }
        }

        return false;
    }

    /** Check if user reacted to a post / comment.
     * @param $reactions
     * @return bool
     */
    public static function didUserReact($reactions, $type=false)
    {
        if (Auth::check()) {
            foreach ($reactions as $reaction) {
                if (Auth::user()->id == $reaction->user_id) {
                    if( $type ){
                        return $reaction->reaction_type;
                    }
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if post is bookmarked by current user.
     * @param $bookmarks
     * @return bool
     */
    public static function isPostBookmarked($bookmarks)
    {
        if (Auth::check()) {
            foreach ($bookmarks as $bookmark) {
                if (Auth::user()->id == $bookmark->user_id) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Check if user is coming back to a paginated feed post from a post page.
     * @param $page
     * @return bool
     */
    public static function isComingFromPostPage($page)
    {
        if (isset($page) && is_int(strpos($page['url'], '/posts')) && ! is_int(strpos($page['url'], '/posts/create'))) {
            return true;
        }

        return false;
    }

    /**
     * Get (user session) start page of the feed pagination.
     * @param $prevPage
     * @return int
     */
    public static function getFeedStartPage($prevPage)
    {
        return Cookie::get('app_feed_prev_page') && self::isComingFromPostPage($prevPage) ? Cookie::get('app_feed_prev_page') : 1;
    }

    /**
     * Get (user session) prev page of the feed pagination.
     * @param $request
     * @return mixed
     */
    public static function getPrevPage($request)
    {
        return $request->session()->get('_previous');
    }

    /**
     * Check if the pagination cookie should be deleted when navigating.
     * @param $request
     * @return bool
     */
    public static function shouldDeletePaginationCookie($request)
    {
        if (! self::isComingFromPostPage(self::getPrevPage($request))) {
            Cookie::queue(Cookie::forget('app_feed_prev_page'));
            Cookie::queue(Cookie::forget('app_prev_post'));
            return true;
        }

        return false;
    }

    /**
     * Returns count of each attachment types for user.
     * @param $userID
     * @return array
     */
    public static function getUserMediaTypesCount($userID)
    {
        $attachments = Attachment::
        leftJoin('posts', 'posts.id', '=', 'attachments.post_id')
            ->where('attachments.user_id', $userID)->where('post_id', '<>', null)
            ->where(function($query) {
                $query->where('posts.expire_date', '>', Carbon::now());
                $query->orWhere('posts.expire_date', null);
            })
            ->where(function($query) {
                $query->where('posts.release_date', '<', Carbon::now());
                $query->orWhere('posts.release_date',null);
            })
            ->get();
        $typeCounts = [
            'video' => 0,
            'audio' => 0,
            'image' => 0,
        ];
        foreach ($attachments as $attachment) {
            $typeCounts[AttachmentServiceProvider::getAttachmentType($attachment->type)] += 1;
        }
        $streams = Stream::where('user_id',$userID)->where('is_public',1)->whereIn('status',[Stream::ENDED_STATUS,Stream::IN_PROGRESS_STATUS])->count();
        $typeCounts['streams'] = $streams;
        return $typeCounts;
    }

    /**
     * Check if user paid for post
     * @param $userId
     * @param $postId
     * @return bool
     */
    public static function userPaidForPost($userId, $postId){
        return Transaction::query()->where(
                [
                    'post_id' => $postId,
                    'sender_user_id' => $userId,
                    'type' => Transaction::POST_UNLOCK,
                    'status' => Transaction::APPROVED_STATUS
                ]
            )->first() != null;
    }

    /**
     * Check if user paid for stream access
     * @param $userId
     * @param $streamId
     * @return bool
     */
    public static function userPaidForStream($userId, $streamId){
        return Transaction::query()->where(
                [
                    'stream_id' => $streamId,
                    'sender_user_id' => $userId,
                    'type' => Transaction::STREAM_ACCESS,
                    'status' => Transaction::APPROVED_STATUS
                ]
            )->first() != null;
    }

    /**
     * Checks if user paid access for this message
     * @param $userId
     * @param $messageId
     * @return bool
     */
    public static function userPaidForMessage($userId, $messageId){
        return Transaction::query()->where(
                [
                    'user_message_id' => $messageId,
                    'sender_user_id' => $userId,
                    'type' => Transaction::MESSAGE_UNLOCK,
                    'status' => Transaction::APPROVED_STATUS
                ]
            )->first() != null;
    }


    /**
     * Returns number of approved posts
     * @param $userID
     * @return mixed
     */
    public static function getUserApprovedPostsCount($userID){
        return $postsCount = Post::where([
            'user_id' =>  $userID,
            'status' => Post::APPROVED_STATUS
        ])->count();
    }

    public static function getPostsCountLeftTillAutoApprove($userID){
        return (int)getSetting('compliance.admin_approved_posts_limit') - self::getUserApprovedPostsCount(Auth::user()->id);
    }

    /**
     * Returns the default status for post to be created
     * If admin_approved_posts_limit is > 0, user must have had more posts than that number
     * Otherwise, post goes to pending state
     * @return int
     */
    public static function getDefaultPostStatus($userID){
        $postStatus = Post::APPROVED_STATUS;
        if(getSetting('compliance.admin_approved_posts_limit')){
            $postsCount = self::getUserApprovedPostsCount($userID);
            if((int)getSetting('compliance.admin_approved_posts_limit') > $postsCount){
                $postStatus = Post::PENDING_STATUS;
            }
        }
        return $postStatus;
    }

    /**
     * Counts types of media for a post attachments
     * @param $attachments
     * @return array
     */
    public static function getAttachmentsTypesCount($attachments){
        $counts = [
            'image' => 0,
            'video' => 0,
            'audio' => 0
        ];
        foreach($attachments as $attachment){
            AttachmentServiceProvider::getAttachmentType($attachment->type);
            if(isset($counts[AttachmentServiceProvider::getAttachmentType($attachment->type)])){
                $counts[AttachmentServiceProvider::getAttachmentType($attachment->type)] += 1;
            }
        }
        return $counts;
    }

}
