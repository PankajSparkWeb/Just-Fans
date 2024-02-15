<?php

namespace App\Http\Controllers;

use App\Http\Requests\DeletePostRequest;
use App\Http\Requests\SavePostCommentRequest;
use App\Http\Requests\SavePostRequest;
use App\Http\Requests\UpdatePostBookmarkRequest;
use App\Http\Requests\UpdateReactionRequest;
use App\Model\Attachment;
use App\Model\Post;
use App\Model\PostHide;
use App\Model\History;
use App\Model\PostComment;
use App\Model\Newinterest;
use App\Model\Reaction;
use App\Model\UserBookmark;
use App\Providers\AttachmentServiceProvider;
use App\Providers\EmailsServiceProvider;
use App\Providers\GenericHelperServiceProvider;
use App\Providers\ListsHelperServiceProvider;
use App\Providers\NotificationServiceProvider;
use App\Providers\PostsHelperServiceProvider;
use Carbon\Carbon;
use Cookie;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use JavaScript;
use Log;
use View;
use Session;

class PostsController extends Controller
{
    /**
     * Method used for rendering the single post page.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     * @throws \Exception
     */
    public function getPost(Request $request)
    {
        $post_id = $request->route('post_id');
        $username = $request->route('username');
        Session::forget('mypostId');
        Session::put('mypostId', $post_id);
        $selectedPostId = Session::get('mypostId');
        
        $user = PostsHelperServiceProvider::getUserByUsername($username);
        if (! $user) {
            abort(404);
        }

        $post = Post::withCount('tips')
            ->with('user', 'attachments', 'reactions')
            ->where('id', $post_id)
            ->first();

        if (!$post) {
            abort(404);
        }

        // Only allowing creators to preview non-released/non-approved/expired posts
        if(!Auth::check() || (Auth::check() && $post->user_id != Auth::user()->id)){
            if($post->status !== Post::APPROVED_STATUS){
                abort(404);
            }
            if($post->release_date && $post->release_date >  Carbon::now()){
                abort(404);
            }
            if($post->expire_date && $post->expire_date < Carbon::now()){
                abort(404);
            }
        }

        $post->setAttribute('isSubbed', false);
        // Checking authorization & post existence
        if (PostsHelperServiceProvider::hasActiveSub(Auth::user()->id, $post->user->id)
            || Auth::user()->id == $post->user->id
            || PostsHelperServiceProvider::userPaidForPost(Auth::user()->id, $post->id)
            || (!$post->user->paid_profile && ListsHelperServiceProvider::loggedUserIsFollowingUser($post->user->id))
            || Auth::user()->role_id === 1
        ) {
            $post->setAttribute('isSubbed', true);
        }

        JavaScript::put([
            'postVars' => [
                'post_id' => $post->id,
            ],
        ]);
        
        $data = [
            'post' => $post,
            'user' => $user,
        ];

        $data['recentMedia'] = false;
        if ($post->isSubbed || Auth::user()->id == $post->user->id  || (getSetting('profiles.allow_users_enabling_open_profiles') && $post->user->open_profile)) {
            $data['recentMedia'] = PostsHelperServiceProvider::getLatestUserAttachments($user->id, 'image');
        }

        //ADD HISTORY LOG
        if(Auth::check() && $post->user_id != Auth::user()->id){      
            $create_history = $this->create_history_on_visit_share_post( $post->id , 'view');
        }
        
        return view('pages.post', $data);
    }
    

    private function create_history_on_visit_share_post( $post_id, $action = 'view' ){
        // Delete old history entries for the same user and post
        //action = 'comment', 'view', 'share'
        History::where('user_id', Auth::user()->id)
        ->where('post_id', $post_id)
        ->where('action', $action)
        ->delete();
        // Create a new history entry
        History::create([
            'user_id' => Auth::user()->id,
            'post_id' => $post_id,                
            'action' => $action,
        ]);
        return true;
    }

    /**
     * Renders the post create page.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create()
    {
        $interests = Newinterest::orderBy('name', 'asc')->get();
        $canPost = true;
        if(getSetting('site.enforce_user_identity_checks')){
            if(!GenericHelperServiceProvider::isUserVerified()){
                $canPost = false;
            }
        }
        Javascript::put([
            'isAllowedToPost' => $canPost,
            'mediaSettings' => [
                'allowed_file_extensions' => '.' . str_replace(',', ',.', AttachmentServiceProvider::filterExtensions('videosFallback')),
                'max_file_upload_size' => (int)getSetting('media.max_file_upload_size'),
                'use_chunked_uploads' => (bool)getSetting('media.use_chunked_uploads'),
                'upload_chunk_size' => (int)getSetting('media.upload_chunk_size'),
                'max_post_description_size' => (int)getSetting('feed.min_post_description')
            ],
        ]);
        return view('pages.create', ['interests' => $interests]);
    }

    /**
     * Shows post edit template.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function edit(Request $request)
    {
        $interests = Newinterest::orderBy('name', 'asc')->get();
        $postID = $request->route('post_id');
        $post = Post::where('id', $postID)->where('user_id', Auth::user()->id)->with(['attachments'])->first();
        if (! $post) {
            abort(404);
        }
        Javascript::put([
            'postData' => [
                'id' => $post->id,
                'text' => $post->text,
                'external_post_link' => $post->external_post_link,
                'attachments' => $post->attachments,
                'price' => $post->price,
            ],
            'mediaSettings' => [
                'allowed_file_extensions' => '.'.str_replace(',', ',.', AttachmentServiceProvider::filterExtensions('videosFallback')),
                'max_file_upload_size' => (int) getSetting('media.max_file_upload_size'),
                'use_chunked_uploads' => (bool)getSetting('media.use_chunked_uploads'),
                'upload_chunk_size' => (int)getSetting('media.upload_chunk_size'),
                'max_post_description_size' => (int)getSetting('feed.min_post_description')
            ],
        ]);

        return view('pages.create', [
            'post' => $post,
            'interests' => $interests
        ]);
    }

    /**
     * Method used for creating / editing posts.
     *
     * @param SavePostRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function savePost(SavePostRequest $request)
    {
        try {
            if (! GenericHelperServiceProvider::isUserVerified() && getSetting('site.enforce_user_identity_checks')) {
                return response()->json(['success' => false, 'errors' => ['permissions' => __('User not verified. Can not post content.')]], 500);
            }

            $type = $request->get('type');
            $postStatus = PostsHelperServiceProvider::getDefaultPostStatus(Auth::user()->id);
            $postSchedulingData = [
                'release_date' => $request->get('postReleaseDate') ? Carbon::parse($request->get('postReleaseDate'))->toDateTimeString() : null,
                'expire_date' => $request->get('postExpireDate') ? Carbon::parse($request->get('postExpireDate'))->toDateTimeString() : null
            ];

            if ($type == 'create') {
                $postID = Post::create(array_merge([
                    'user_id' => $request->user()->id,
                    'text' => $request->get('text'),
                    'external_post_link' => $request->get('external_post_link'),
                    'price' => $request->get('price'),
                    'status' => $postStatus,
                ], $postSchedulingData))->id;
                $post = Post::find($postID);
                $post->interests()->sync($request->input('interests', []));
            } elseif ($type == 'update') {
                $postID = $request->get('id');
                $post = Post::where('id', $postID)->where('user_id', Auth::user()->id)->first();
                if ($post) {
                    $validatedData = $request->validate([
                        'interests' => 'array|exists:newinterests,id',
                    ]);
                    $post->update(array_merge([
                        'text' => $request->get('text'),
                        'external_post_link' => $request->get('external_post_link'),
                        'price' => $request->get('price'),
                    ], $postSchedulingData));                   
                    $postID = $post->id;
                    $post->interests()->sync($request->input('interests', []));
                } else {
                    return response()->json(['success' => false, 'errors' => [__('Not authorized')], 'message' => __('Post not found')], 403);
                }
            }

            if ($postID) {
                $attachments = collect($request->get('attachments'))->map(function ($v, $k) {
                    if (isset($v['attachmentID'])) {
                        return $v['attachmentID'];
                    }
                    if (isset($v['id'])) {
                        return $v['id'];
                    }
                })->toArray();

                if ($request->get('attachments')) {
                    Attachment::whereIn('id', $attachments)->update(['post_id' => $postID]);
                }
            }

            $message = __('Post created.');
            if ($type == 'update') {
                $message = __('Post updated successfully.');
            }
            else{
                $postNotifications = $request->get('postNotifications');
                if(getSetting('profiles.enable_new_post_notification_setting') && $postNotifications == 'true'){
                    // Grabbing followers
                    $followers = ListsHelperServiceProvider::getUserFollowers(Auth::user()->id);

                    // Sending them email notifications, if site & user settings allows it
                    foreach($followers as $follower){
                        $serializedSettings = json_decode($follower['settings']);
                        if(isset($serializedSettings->notification_email_new_post_created) && $serializedSettings->notification_email_new_post_created == 'true'){
                            App::setLocale($serializedSettings->locale);
                            EmailsServiceProvider::sendGenericEmail(
                                [
                                    'email' => $follower['email'],
                                    'subject' => __('New content from @:username', ['username' => Auth::user()->username]),
                                    'title' => __('Hello, :name,', ['name'=>$follower['name']]),
                                    'content' => __('New content from people you follow is available', ['siteName'=>getSetting('site.name')]),
                                    'button' => [
                                        'text' => __('View your feed'),
                                        'url' => route('feed'),
                                    ],
                                ]
                            );
                            App::setLocale(Auth::user()->settings['locale']);
                        }
                    }
                }

            }

            return response()->json([
                'success' => 'true', 'message' => $message,
            ]);
        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'errors' => [$exception->getMessage()]]);
        }
    }

    /**
     * Gets (ajaxed) post comments.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPostComments(Request $request)
    {
        try {
            $postID = $request->get('post_id');

            // Checking authorization & post existence
            $post = Post::with(['user'])->where('id', $postID)->first();
            if (! $post) {
                return response()->json(['success' => false, 'errors' => [__('Not found')], 'message'=> __('Post not found')], 404);
            }

            if ($this->validateUserAccessForPost($post)) {
                $limit = $request->get('limit') ? $request->get('limit') : 9;

                return response()->json([
                    'success' => true,
                    'data' => PostsHelperServiceProvider::getPostComments($postID, $limit, 'DESC', true),
                ]);
            } else {
                return response()->json(['success' => false, 'errors' => [__('Not authorized')], 'message' => __('Not authorized')], 403);
            }
        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'errors' => [$exception->getMessage()]]);
        }
    }

    /**
     * Method used for adding a new post comment.
     *
     * @param SavePostCommentRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addNewComment(SavePostCommentRequest $request)
    {
        try {
            $comment            = $request->get('message');
            $postID             = $request->get('post_id');
            $comment_parent_id  = $request->get('comment_parent_id');            


            // Checking authorization & post existence
            $post = Post::where('id', $postID)->first();
            if (!$post) {
                return response()->json(['success' => false, 'errors' => [__('Not found')], 'message' => __('Post not found')], 404);
            }

            if(GenericHelperServiceProvider::hasUserBlocked($post->user_id, Auth::user()->id)){
                return response()->json(['success' => false, 'errors' => [__('This user has blocked you')], 'message'=> __('This user has blocked you')], 403);
            }

            if ($this->validateUserAccessForPost($post)) {
                $comment = PostComment::create([
                    'message' => $comment,
                    'post_id' => $postID,
                    'comment_parent_id' => $comment_parent_id,
                    'user_id' => Auth::user()->id,
                ]);

                $post = Post::query()->where('id', $postID)->first();
                if ($comment != null && $post != null && $comment->user_id != $post->user_id) {
                    NotificationServiceProvider::createNewPostCommentNotification($comment);
                }

                return response()->json([
                    'success' => true,
                    'data' => View::make('elements.feed.post-comment')->with('comment', $comment)->render(),
                ]);
            }
            else{
                return response()->json(['success' => false, 'errors' => [__('Not authorized')], 'message' => __('Not authorized')], 403);
            }

        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'errors' => [$exception->getMessage()]]);
        }
    }

    /**
     * Method used for adding / removing a post / comment reaction.
     *
     * @param UpdateReactionRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateReaction(UpdateReactionRequest $request)
    {
        $type = $request->get('type');
        $action = $request->get('action');
        $id = $request->get('id');

        $data = [
            'reaction_type' => 'like',
            'user_id' => Auth::user()->id,
        ];
        $where_reaction = [
            'user_id' => Auth::user()->id,
        ];

        try {
            // Checking authorization & post existence
            $postComment = PostComment::where('id', $id)->first();
            $post = null;
            if ($postComment != null) {
                $post = $postComment->post;
            } else if ($type === 'post' && $id != null) {
                $post = Post::where('id', $id)->first();
            }

            if (!$post) {
                return response()->json(['success' => false, 'errors' => [__('Not found')], 'message' => __('Post not found')], 404);
            }

            if ($this->validateUserAccessForPost($post)) {
                if ($type == 'post') {
                    $data['post_id'] = $id;
                    $where_reaction['post_id'] = $id;
                } elseif ($type == 'comment') {
                    $data['post_comment_id'] = $id;
                    $where_reaction['post_comment_id'] = $id;
                }

               $is_reaction_id      = Reaction::where($where_reaction)->first();
               $reaction_id         = $is_reaction_id ? $is_reaction_id->id : 0;

                $message = '';
                if ($action == 'add') {
                    $message = __('Reaction added.');
                    //if reaction id exists
                    if( $reaction_id ){                        
                        $reaction = Reaction::find($reaction_id);
                        $reaction->update($data);                    
                        // Save the changes to the database
                        $reaction->save();
                    }else{
                        $reaction = Reaction::create($data);
                    }

                    if ($reaction != null) {
                        NotificationServiceProvider::createNewReactionNotification($reaction);
                    }
                } elseif ($action == 'remove') {
                    $data['reaction_type'] = 'dislike';
                    if( $reaction_id ){                        
                        $reaction = Reaction::find($reaction_id);
                        $reaction->update($data);                    
                        // Save the changes to the database
                        $reaction->save();
                    }else{
                        $reaction = Reaction::create($data);
                    }
                    $message = __('Reaction Dislike.');
                    //Reaction::where($data)->first()->delete();
                } elseif ($action == 'delete') {
                    unset($data['reaction_type']);
                    Reaction::where($data)->first()->delete();
                    $message = __('Reaction Dislike.');
                }
                
                
                if( $type === 'post'  ){
                    $post = Post::with('reactions')->where('id', $id)->first();
                    $reaction_count = $post->count_reactions;
                }else{
                    //need to count comment reaction TODO
                    $post = PostComment::with('reactions')->where('id', $id)->first();
                    $reaction_count = $post->count_reactions;
                }

                return response()->json(['success' => true, 'message' => $message, 'reaction_count' => $reaction_count]);
            }

        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'errors' => [__('An internal error has occurred.')], 'message' => $exception->getMessage()]);
        }
    }


    /**
     * Method used for adding / deleting a post bookmark.
     *
     * @param UpdatePostBookmarkRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function updatePostBookmark(UpdatePostBookmarkRequest $request)
    {
        $action = $request->get('action');
        $id = $request->get('id');
        $data = [
            'post_id' => $id,
            'user_id' => Auth::user()->id,
        ];
        try {

            // Checking authorization & post existence
            $post = Post::where('id', $id)->first();
            if (! $post) {
                return response()->json(['success' => false, 'errors' => [__('Not found')], 'message'=> __('Post not found')], 404);
            }

            if (
                PostsHelperServiceProvider::hasActiveSub(Auth::user()->id, $post->user_id)
                || Auth::user()->id == $post->user_id ||
                (!$post->user->paid_profile)) {
                $message = '';
                if ($action == 'add') {
                    $message = 'Bookmark added.';
                    UserBookmark::create($data);
                } elseif ($action == 'remove') {
                    $message = 'Bookmark removed.';
                    UserBookmark::where($data)->first()->delete();
                }

                return response()->json(['success' => true, 'message' => __($message)]);
            }
            else{
                return response()->json(['success' => false, 'errors' => [__('Not authorized')], 'message'=> __('Not authorized')], 403);
            }

        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'errors' => [__('An internal error has occurred.')]]);
        }
    }

// function for hide post
    public function hide_unhide_posts(Request $request)
    {
        $user = Auth::user();
        $postId = $request->get('id');
        $type = $request->get('type');
        if( $type == 'hide' ){
            if (!$user->hidedPost()->where('post_id', $postId)->exists()) {                        
                $user->hidedPost()->create(['post_id' => $postId]);                        
                return response()->json(['success' => true, 'message' => 'Post hidden successfully']);
            }else{
                return response()->json(['success' => false, 'errors' => [__('Post already hidden')], 'message' => 'Post already hidden']);
            }
        }else{
            if ($user->hidedPost()->where('post_id', $postId)->exists()) {                            
                $user->hidedPost()->where('post_id', $postId)->delete();          
                return response()->json(['success' => true, 'message' => 'Post unhide successfully']);
            }else{
                return response()->json(['success' => false, 'errors' => [__('Post already unhide')], 'message' => 'Post already unhide']);
            }
        }
    }
    // function for hide post
        public function save_unsave_posts(Request $request)
        {
            $user = Auth::user();
            $postId = $request->get('id');
            $type = $request->get('type');
            if( $type == 'save' ){
                if (!$user->savedPost()->where('post_id', $postId)->exists()) {                        
                    $user->savedPost()->create(['post_id' => $postId]);                        
                    return response()->json(['success' => true, 'message' => 'Post saved successfully']);
                }else{
                    return response()->json(['success' => false, 'errors' => [__('Post already saved')], 'message' => 'Post already saved']);
                }
            }else{
                if ($user->savedPost()->where('post_id', $postId)->exists()) {                            
                    $user->savedPost()->where('post_id', $postId)->delete();          
                    return response()->json(['success' => true, 'message' => 'Post unsave successfully']);
                }else{
                    return response()->json(['success' => false, 'errors' => [__('Post already unsave')], 'message' => 'Post already unsave']);
                }
            }
        }

        // old learnd post code

        // public function learnedPost($postId){
        //     $user = Auth::user();
        //     // Check if the user has already shared the post
        //     if (!$user->learnedPost()->where('post_id', $postId)->exists()) {            
        //         // Share the post
        //         $user->learnedPost()->create(['post_id' => $postId]);
        //         // Additional logic (e.g., update post share count)
        //         return redirect()->back()->with('success', 'Post learned');
        //     }
        //     return redirect()->back()->with('error', 'Already Learned');
        // }

        // controller function for learn post
        public function learnedPost(Request $request)
        {
            $user = Auth::user();
            $postId = $request->get('id');
            $type = $request->get('type');
            if( $type == 'learned' ){
                if (!$user->learnedPost()->where('post_id', $postId)->exists()) {                        
                    $user->learnedPost()->create(['post_id' => $postId]);                        
                    return response()->json(['success' => true, 'message' => 'Post learned successfully']);
                }else{
                    return response()->json(['success' => false, 'errors' => [__('Post already learned')], 'message' => 'Post already learned']);
                }
            }else{
                if ($user->learnedPost()->where('post_id', $postId)->exists()) {                            
                    $user->learnedPost()->where('post_id', $postId)->delete();          
                    return response()->json(['success' => true, 'message' => 'Post learned successfully']);
                }else{
                       return response()->json(['success' => false, 'errors' => [__('Post already learned')], 'message' => 'Post already learned']);
                }
            }
        }

        // old code for sharing post 

        // public function sharePost($postId){
        //     $user = Auth::user();
        //     // Check if the user has already shared the post
        //     if (!$user->sharedPosts()->where('post_id', $postId)->exists()) {
        //         //$create_history = $this->create_history_on_visit_share_post( $postId , 'share');
        //         // Share the post
        //         $user->sharedPosts()->create(['post_id' => $postId]);
        //         // Additional logic (e.g., update post share count)
        //         return redirect()->back()->with('success', 'Post shared successfully');
        //     }
        //     return redirect()->back()->with('error', 'Post already shared');
        // }

        // new code for sharing post

        public function sharePost(Request $request)
        {
            $user = Auth::user();
            $postId = $request->get('id');
            
            if (!$user->sharedPost()->where('post_id', $postId)->exists()) {                        
                $user->sharedPost()->create(['post_id' => $postId]);                        
                return response()->json(['success' => true, 'message' => 'Post shared successfully']);
            } else {
                return response()->json(['success' => false, 'errors' => [__('Post already shared')], 'message' => 'Post already shared']);
            }
        }
        


        /**
         * Updated the post pin status
         * @param Request $request
         * @return \Illuminate\Http\JsonResponse
         */
        public function updatePostPin(Request $request){
            $postID = $request->get('id');
            $action = $request->get('action');
            try {
                // Checking authorization & post existence
                $post = Post::where('id', $postID)->where('user_id', Auth::user()->id)->first();
                if (! $post) {
                    return response()->json(['success' => false, 'errors' => [__('Not found')], 'message'=> __('Post not found')], 404);
                }

                // Delete prev pinned post
                $pinnedPost = Post::where('user_id', Auth::user()->id)->where('is_pinned', 1)->first();
                if($pinnedPost){
                    $pinnedPost->is_pinned = false;
                    $pinnedPost->save();
                }

                $message = '';
                if ($action == 'add') {
                    $message = 'Pin added.';
                    $post->is_pinned = true;
                    $post->save();
                } elseif ($action == 'remove') {
                    $message = 'Pin removed.';
                }

                return response()->json(['success' => true, 'message' => __($message)]);

            } catch (\Exception $exception) {
                return response()->json(['success' => false, 'errors' => [__('An internal error has occurred.') . $exception->getMessage()]]);
            }
        }

    /**
     * Method used for deleting a post.
     *
     * @param DeletePostRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletePost(DeletePostRequest $request)
    {
        $postID = $request->get('id');

        $userPosts = Auth::user()->posts;
        if(getSetting('compliance.minimum_posts_deletion_limit') > 0 && count($userPosts) <= getSetting('compliance.minimum_posts_deletion_limit')) {
            return response()->json(['success' => false, 'errors' => [__('You reached the minimum limit of posts')]]);
        }

        $post = Post::where('id', $postID)->where('user_id', Auth::user()->id)->withCount('postPurchases')->first();

        if(getSetting('compliance.disable_creators_ppv_delete')){
            if(isset($post->post_purchases_count) && $post->post_purchases_count > 0){
                return response()->json(['success' => false, 'errors' => [__('The post has been bought and can not be deleted.')]]);
            }
        }

        if ($post) {
            // Deleting attachments from storage
            foreach($post->attachments as $attachment){
                AttachmentServiceProvider::removeAttachment($attachment);
            }
            $post->delete();
            return response()->json(['success' => true, 'message' => __('Post deleted successfully.')]);
        }

        return response()->json(['success' => false, 'errors' => [__('Post not found.')]]);
    }

    /**
     * Deletes post comment
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteComment(Request $request){
        $commentID = $request->get('id');
        $comment = PostComment::where('id', $commentID)->where('user_id', Auth::user()->id)->first();
    
        if (!$comment) {
            return response()->json(['success' => false, 'errors' => [__('Not authorized')], 'message' => __('Comment not found')], 403);
        }
    
        // Delete child comments with the same parent_id
        PostComment::where('comment_parent_id', $commentID)->delete();
    
        // Delete the parent comment
        $comment->delete();
    
        return response()->json(['success' => true, 'message' => __('Comment deleted successfully.')]);
    }
    

    /**
     * Validates post access
     * @param $post
     * @return bool
     */
    private function validateUserAccessForPost($post) {
        return PostsHelperServiceProvider::hasActiveSub(Auth::user()->id, $post->user_id)
            || Auth::user()->id == $post->user_id
            || (getSetting('profiles.allow_users_enabling_open_profiles') && $post->user->open_profile)
            || (!$post->user->paid_profile && ListsHelperServiceProvider::loggedUserIsFollowingUser($post->user->id))
            // check if logged user is admin
            || Auth::user()->role_id === 1;
    }
}
