<?php

namespace App\Http\Controllers;

use App\Events\NewUserMessage;
use App\Model\Attachment;
use App\Model\Notification;
use App\Model\Subscription;
use App\Model\Transaction;
use App\Model\UserMessage;
use App\Providers\AttachmentServiceProvider;
use App\Providers\EmailsServiceProvider;
use App\Providers\GenericHelperServiceProvider;
use App\Providers\ListsHelperServiceProvider;
use App\Providers\NotificationServiceProvider;
use App\Providers\PostsHelperServiceProvider;
use App\Providers\SettingsServiceProvider;
use App\User;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Javascript;
use Pusher\Pusher;
use Ramsey\Uuid\Uuid;

class MessengerController extends Controller
{
    /**
     * Renders the main messenger view / layout
     * Rest of the messenger elements are mostly loaded via JS.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function index(Request $request)
    {
        $lastContactID = false;
        $lastContact = $this->fetchContacts(1);
        if ($lastContact) {
            $lastContactID = $lastContact[0]->receiverID == Auth::user()->id ? $lastContact[0]->senderID : $lastContact[0]->receiverID;
        }
        // handles messenger tips
        if(!empty($request->get('tip')) || !empty($request->get('messageUnlock'))) {
            $transaction = Transaction::query()
                ->where('sender_user_id', Auth::user()->id)
                ->whereIn('type', [Transaction::CHAT_TIP_TYPE, Transaction::MESSAGE_UNLOCK])
                ->orderBy('id', 'DESC')
                ->first();
            if($transaction) {
                $lastContactID = $transaction->recipient_user_id;
            }
        }

        $availableContacts = $this->getUserSearch($request);

        Javascript::put([
            'messengerVars' => [
                'userAvatarPath' =>  ($request->getHost() == 'localhost' ? 'http://localhost' : 'https://'.$request->getHost()).$request->getBaseUrl().'/uploads/users/avatars/',
                'lastContactID' => (int) $lastContactID,
                'pusherDebug' => (bool) env('PUSHER_APP_LOGGING', false),
                'pusherCluster' => config('broadcasting.connections.pusher.options.cluster'),
                'bootFullMessenger' => true,
                'lockedMessageSVGPath' => asset('/img/post-locked.svg'),
                'minimumPostsLimit' => getSetting('compliance.minimum_posts_until_creator'),
                'availableContacts' => $availableContacts
            ],
            'mediaSettings' => [
                'allowed_file_extensions' => '.'.str_replace(',', ',.', AttachmentServiceProvider::filterExtensions('videosFallback')),
                'max_file_upload_size' => (int) getSetting('media.max_file_upload_size'),
            ],
            'user' => [
                'username' => Auth::user()->username,
                'user_id' => Auth::user()->id,
                'lists' => [
                    'blocked'=>Auth::user()->lists->firstWhere('type', 'blocked')->id,
                    'following'=>Auth::user()->lists->firstWhere('type', 'following')->id,
                ],
                'billingData' => [
                    'first_name' => Auth::user()->first_name,
                    'last_name' => Auth::user()->last_name,
                    'billing_address' => Auth::user()->billing_address,
                    'country' => Auth::user()->country,
                    'city' => Auth::user()->city,
                    'state' => Auth::user()->state,
                    'postcode' => Auth::user()->postcode,
                    'credit' => Auth::user()->wallet->total,
                ]
            ],
        ]);

        $unseenMessages = UserMessage::where('receiver_id', Auth::user()->id)->where('isSeen', 0)->count();
        $data = [
            'lastContactID' => $lastContactID,
            'unseenMessages' => $unseenMessages,
            'availableContacts' => $availableContacts
        ];

        return view('pages.messenger', $data);
    }

    /**
     * Method used for fetching available contacts/conversations.
     *
     * @param string $limit
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchContacts($limit = '0')
    {
        $userID = Auth::user()->id;
        $query = '
        SELECT *
         FROM (
            SELECT
             t1.sender_id as lastMessageSenderID,
             t1.message as lastMessage,
             t1.isSeen,
             null as created_at, #hack around laravel orm behaviour
             t1.created_at as messageDate,
             senderDetails.id as senderID,
             senderDetails.name as senderName,
             senderDetails.avatar as senderAvatar,
             senderDetails.role_id as senderRole,
             receiverDetails.id as receiverID,
             receiverDetails.name as receiverName,
             receiverDetails.avatar as receiverAvatar,
             receiverDetails.role_id as receiverRole,
             IF(receiverDetails.id = '.$userID.', senderDetails.id, receiverDetails.id) as contactID
            FROM user_messages AS t1
            INNER JOIN
            (
                SELECT
                    LEAST(receiver_id, sender_id) AS receiverID,
                    GREATEST(receiver_id, sender_id) AS senderID,
                    MAX(id) AS max_id
                FROM user_messages
                GROUP BY
                    LEAST(receiver_id, sender_id),
                    GREATEST(receiver_id, sender_id)
            ) AS t2
                ON LEAST(t1.receiver_id, t1.sender_id) = t2.receiverID AND
                   GREATEST(t1.receiver_id, t1.sender_id) = t2.senderID AND
                   t1.id = t2.max_id
            INNER JOIN users senderDetails ON t1.sender_id = senderDetails.id #AND senderDetails.level <> 3
            INNER JOIN users receiverDetails ON t1.receiver_id = receiverDetails.id #AND receiverDetails.level <> 3
            WHERE  (t1.receiver_id = ? OR t1.sender_id = ?)
                ) as contactsData
                ORDER BY contactsData.messageDate DESC
            ';
        $contacts = DB::select($query, [$userID, $userID]);

        foreach ($contacts as $contact) {
            if($contact->messageDate){
                $contact->created_at = Carbon::createFromTimeStamp(strtotime($contact->messageDate))->diffForHumans(null, true, true);
            }

            $contact->senderAvatar = GenericHelperServiceProvider::getStorageAvatarPath($contact->senderAvatar);
            $contact->receiverAvatar = GenericHelperServiceProvider::getStorageAvatarPath($contact->receiverAvatar);
        }

        // Removing blocked contacts
        $contacts = array_filter($contacts, function ($contact){
            if(!GenericHelperServiceProvider::hasUserBlocked($contact->contactID, Auth::user()->id) && !GenericHelperServiceProvider::hasUserBlocked(Auth::user()->id, $contact->contactID)){
                return $contact;
            }
        });
        $contacts = array_values($contacts);

        // Additional (proper) messenger acccess check function, applied to contacts as well
        $contacts = array_filter($contacts, function ($contact){
            if(self::checkMessengerAccess($contact->senderID,$contact->receiverID) || self::checkMessengerAccess($contact->receiverID,$contact->senderID)){
                return $contact;
            }
        });
        $contacts = array_values($contacts);

        // Filtering unique contactIDs
        // TODO: This could have been done within the initial query - can be inspected for later on, was causing dupe on mass messages
        $filteredContacts = [];
        $uniqueContacts = array_unique(array_map(function ($v){
            return $v->contactID;
        },$contacts));
        foreach($uniqueContacts as $uniqueContact){
            foreach($contacts as $contact){
                if($contact->contactID === $uniqueContact){
                    $filteredContacts[] = $contact;
                    break;
                }
            }
        }
        $contacts = $filteredContacts;

        if ($limit) {
            return $contacts;
        }

        return response()->json([
            'status'=>'success',
            'data'=>[
                'contacts' => $contacts,
            ],
        ]);
    }

    /**
     * Method used for fetching the conversation messages.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function fetchMessages(Request $request)
    {
        $senderID = Auth::user()->id;
        $receiverID = $request->route('userID');

        // Checking access
        if(!self::checkMessengerAccess($senderID,$receiverID)){
            return response()->json(['success' => false, 'errors' => [__('Not authorized')], 'message'=> __('Not authorized')], 403);
        }

        if(GenericHelperServiceProvider::hasUserBlocked($receiverID, $senderID)){
            return response()->json(['success' => false, 'errors' => [__('This user has blocked you')], 'message'=> __('This user has blocked you')], 403);
        }

        $conversation = UserMessage::with(['sender', 'receiver', 'attachments'])->where(function ($q) use ($senderID, $receiverID) {
            $q->where('sender_id', $senderID)
                ->where('receiver_id', $receiverID);
        })
            ->orWhere(
                function ($q) use ($senderID, $receiverID) {
                    $q->where('receiver_id', $senderID)
                        ->Where('sender_id', $receiverID);
                }
            )
            ->leftJoin('transactions', function ($join) {
                $join->on('transactions.user_message_id', '=', 'user_messages.id' );
                $join->on('transactions.sender_user_id', '=', DB::raw(Auth::user()->id));
                $join->where('transactions.id', '<>', null)
                    ->where('transactions.type', '=', Transaction::MESSAGE_UNLOCK)
                    ->where('transactions.status', '=', Transaction::APPROVED_STATUS)
                    ->where('transactions.sender_user_id', '=', Auth::user()->id);
            })
            ->orderBy('user_messages.created_at')
            ->select(['user_messages.*', DB::raw('COALESCE(transactions.id,NULL) as hasUserUnlockedMessage')])
            ->get()
            ->map(function ($message) {
                $message->hasUserUnlockedMessage = $message->hasUserUnlockedMessage ? true : false;
                $message->sender->profileUrl = route('profile', ['username'=> $message->sender->username]);
                $message->receiver->profileUrl = route('profile', ['username'=> $message->receiver->username]);
                $message = self::cleanUpMessageData($message);
                return $message;
            });

        return response()->json([
            'status'=>'success',
            'data'=>[
                'messages' => $conversation,
            ], ]);
    }


    /**
     * Sends the user message
     * Manages the assets
     * Sends the notifications
     * @param $options
     * @return array
     */
    public function sendUserMessage($options){

        $senderID = $options['senderID'];
        $receiverID = $options['receiverID'];
        $messageValue = $options['messageValue'];;
        $messagePrice = $options['messagePrice'];;
        $attachments =  $options['attachments'];

        $isFirstMessage = UserMessage::where(function($query) use ($senderID, $receiverID) {
            $query->where('sender_id', $senderID)
                ->orWhere('sender_id',$receiverID);
        })
            ->where(function($query) use ($senderID, $receiverID) {
                $query->where('receiver_id', $senderID)
                    ->orWhere('receiver_id',$receiverID);
            })
            ->count();

        $message = UserMessage::create([
            'sender_id' => $senderID,
            'receiver_id' => $receiverID,
            'message' => $messageValue,
            'price' => $messagePrice
        ]);


        // Turning date into human readable format
        $dateDiff = $message->created_at->diffForHumans(null, true, true);
        $message = $message->toArray();
        $message['dateAdded'] = $dateDiff;

        if ($message['id']) {
            $attachments = collect($attachments)->map(function ($v, $k) {
                if (isset($v['attachmentID'])) {
                    return $v['attachmentID'];
                }
                if (isset($v['id'])) {
                    return $v['id'];
                }
            })->toArray();
            $attachments = Attachment::whereIn('id',$attachments)->get();

            // Attaching the assets to the message
            // TODO: Review if createAttachment could have been used
            if ($attachments) {
                foreach($attachments as $attachment){
                    // Creating unique attachment-message relation, for mass-media-messages
                    $id = Uuid::uuid4()->getHex();
                    $newFileName = 'messenger/images/' . $id . '.' . $attachment->type;
                    // 1. Create new attachment
                    Attachment::create([
                        'id' => $id,
                        'user_id' => Auth::user()->id,
                        'filename' => $newFileName,
                        'driver' => $attachment->driver,
                        'type' => $attachment->type,
                        'message_id' => $message['id'],
                    ]);
                    // 2. Copy the assets of previous attachment to the new one
                    $storage = Storage::disk(AttachmentServiceProvider::getStorageProviderName($attachment->driver));
                    if($attachment->driver != Attachment::PUSHR_DRIVER){
                        $storage->copy($attachment->filename,$newFileName);
                    }
                    else{
                        // Pushr logic - Copy alternative as S3Adapter fails to do ->copy operations
                        AttachmentServiceProvider::pushrCDNCopy($attachment,$newFileName);
                    }
                    if (AttachmentServiceProvider::getAttachmentType($attachment->type) == 'image') {
                        $thumbnailDir = 'messenger/images/150X150/';
                        $thumbnailfilePath = $thumbnailDir.'/'.$id.'.jpg';
                        if($attachment->driver != Attachment::PUSHR_DRIVER){
                            $storage->copy($thumbnailDir.'/'.$attachment->id.'.jpg',$thumbnailfilePath);
                        }
                        else {
                            // Pushr logic - Copy alternative as S3Adapter fails to do ->copy operations
                            AttachmentServiceProvider::pushrCDNCopy($attachment,$thumbnailfilePath);
                        }
                    }
                }
            }
        }

        // Fetching serialized message object
        $message = UserMessage::with(['sender', 'receiver', 'attachments'])->where('user_messages.id', $message['id'])
            ->leftJoin('transactions', function ($join) {
                $join->on('transactions.user_message_id', '=', 'user_messages.id' );
                $join->on('transactions.sender_user_id', '=', DB::raw(Auth::user()->id));
            })
            ->select(['user_messages.*', DB::raw('COALESCE(transactions.id,NULL) as hasUserUnlockedMessage')])
            ->first();
        $message->hasUserUnlockedMessage = $message->hasUserUnlockedMessage ? true : false;
        $message->sender->profileUrl = route('profile', ['username'=> $message->sender->username]);
        $message->receiver->profileUrl = route('profile', ['username'=> $message->receiver->username]);

        // Sending the email
        if (isset($message->receiver->settings['notification_email_new_message']) && $message->receiver->settings['notification_email_new_message'] == 'true') {
            $throttleNotification = Notification::where('user_message_id', '<>', null)->where('to_user_id',$receiverID)->where('created_at', '>=', Carbon::now()->subHours(6))->count();
            if($throttleNotification === 0){
                App::setLocale($message->receiver->settings['locale']);
                EmailsServiceProvider::sendGenericEmail(
                    [
                        'email' => $message->receiver->email,
                        'subject' => __('New message received'),
                        'title' => __('Hello, :name,', ['name'=>$message->receiver->name]),
                        'content' => __('Email new message title', ['siteName'=>getSetting('site.name')]),
                        'button' => [
                            'text' => __('View your messages'),
                            'url' => route('my.messenger.get'),
                        ],
                    ]
                );
                App::setLocale(Auth::user()->settings['locale']);
            }

        }
        NotificationServiceProvider::createNewUserMessageNotification($message);

        // Cleaning up the message
        $message = self::cleanUpMessageData($message);

        // Sending the message to the socket
        broadcast(new NewUserMessage(json_encode($message), $senderID, $receiverID))->toOthers();

        $return = [
            'message' => $message,
        ];

        if ($isFirstMessage === 0) {
            $lastContact = $this->fetchContacts(1);
            $return['contact'] = $lastContact;
            NotificationServiceProvider::publishNotification(
                (object)[
                    'message' => 'new-messenger-conversation',
                    'type' => 'new-messenger-conversation',
                    'fromUserID' => $senderID,
                ],
                User::where('id', $receiverID)->first(),
                'messenger-actions'
            );
        }
        return $return;

    }

    /**
     * Sends the user message.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendMessage(Request $request)
    {
        $receiverIDs = $request->get('receiverIDs');
        $return = [];
        $errors = [];
        foreach($receiverIDs as $receiverID){
            $senderID = (int) Auth::user()->id;
            $receiverID = (int) $receiverID;
            // Checking access
            if(!self::checkMessengerAccess($senderID,$receiverID)) {
                $errors[] = __('Not authorized');
                if (count($receiverIDs) == 1) {
                    return response()->json(['success' => false, 'errors' => [__('Not authorized')], 'message' => __('Not authorized')], 403);
                }
            }
            if(GenericHelperServiceProvider::hasUserBlocked($receiverID, $senderID)) {
                $errors[] = __('This user has blocked you');
                if (count($receiverIDs) == 1) {
                    return response()->json(['success' => false, 'errors' => [__('This user has blocked you')], 'message' => __('This user has blocked you')], 403);
                }
            }
            $return[] = $this->sendUserMessage([
                'senderID' => $senderID,
                'receiverID' => $receiverID,
                'messageValue' => $request->get('message'),
                'messagePrice' => $request->get('price'),
                'isFirstMessage' => $request->get('new'),
                'attachments' => $request->get('attachments')
            ]);
        }
        // Delete initially created attachments, after attaching them to the messages
        if($request->get('attachments')){
            foreach($request->get('attachments') as $attachment){
                Attachment::where('id', $attachment['attachmentID'])->first()->delete();
            }
        }
        // If single message, return the single message entry | keep ui as it was
        if(count($receiverIDs) === 1) $return = $return[0];
        return response()->json([
            'status'=>'success',
            'data'=> $return,
            'errors' => count($errors) ? "Some of your messages couldn't be sent." : false,
        ]);
    }

    /**
     * Marks message as being seen.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function markSeen(Request $request)
    {
        $senderID = $request->get('userID');
        $unreadMessages = UserMessage::where('receiver_id', Auth::user()->id)->where('sender_id', $senderID)->where('isSeen', 0)->count();
        UserMessage::where('receiver_id', Auth::user()->id)->where('sender_id', $senderID)->where('isSeen', 0)->update(['isSeen'=>1]);

        return response()->json([
            'status'=>'success',
            'data'=>[
                'count' => $unreadMessages,
            ], ]);
    }

    /**
     * Authorize socket connections.
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function authorizeUser(Request $request)
    {
        $envVars['PUSHER_APP_KEY'] =     config('broadcasting.connections.pusher.key');
        $envVars['PUSHER_APP_SECRET'] =  config('broadcasting.connections.pusher.secret');
        $envVars['PUSHER_APP_ID'] =      config('broadcasting.connections.pusher.app_id');
        $envVars['PUSHER_APP_CLUSTER'] = config('broadcasting.connections.pusher.options.cluster');
        $pusher = new Pusher(
            $envVars['PUSHER_APP_KEY'],
            $envVars['PUSHER_APP_SECRET'],
            $envVars['PUSHER_APP_ID'],
            [
                'cluster' => $envVars['PUSHER_APP_CLUSTER'],
                'encrypted' => true,
            ]
        );

        try {
            $output = [];
            foreach ($request->get('channel_name') as $channelName) {
                $users = explode('-', $channelName);
                $users = array_slice($users, 3, 2);
                $users = array_map('intval', $users);
                if (in_array(Auth::user()->id, $users)) {
                    $auth = $pusher->socket_auth(
                        $channelName,
                        $request->input('socket_id')
                    );
                    $output[$channelName] = ['status'=>200, 'data'=>json_decode($auth)];
                } else {
                    $output[$channelName] = [
                        'code' => '403',
                        'data' => [
                            'errors' => ['Not authorized'],
                        ],
                    ];
                }
            }

            return $output;
        } catch (\Exception $exception) {
            return response()->json([
                'code' => '403',
                'data' => [
                    'errors' => [__($exception->getMessage())],
                ], ]);
        }
    }

    /**
     * Gets available users to start a conversation with.
     *
     * @param Request $request
     * @return false|string
     */
    public function getUserSearch(Request $request)
    {
        $users = $this->selectizeList($request->input('q'), Auth::user()->id);
        $filteredData = [];
        foreach($users as $user){
            $filteredData[] = $user;
        }
        return $filteredData;
    }

    /**
     * Turns the mysql collection into a selectize-2 list compatible array format.
     *
     * @param $q
     * @param $id
     * @return array
     */
    public static function selectizeList($q, $id)
    {
        $values = [
            'users' => []
        ];

        if(Auth::user()->role_id == 1){
            $users = User::select('id','name','avatar')->where('id','<>', Auth::user()->id)->get();
            foreach ($users as $k => $user) {
                $values['users'][$user->id]['id'] = $user->id;
                $values['users'][$user->id]['name'] = $user->name;
                $values['users'][$user->id]['avatar'] = $user->avatar;
                $values['users'][$user->id]['label'] = '<div><img class="searchAvatar" src="uploads/users/avatars/'.$user->avatar.'" alt=""><span class="name">'.$user->name.'</span></div>';
            }
        }
        else{
            // Fetching users subscribed to
            $subbedUsers = Subscription::with(['creator'])
                ->where(function($query) use ($id){
                    $query->where('sender_user_id', $id)
                        ->orWhere('recipient_user_id', $id);
                })
                ->where(function($query){
                    $query->where('status', 'completed')
                        ->orwhere([
                            ['status', '=', 'canceled'],
                            ['expires_at', '>', Carbon::now()],
                        ]);
                })
                ->get();

            foreach ($subbedUsers as $k => $user) {
                $userData = $user->creator->id === $id ? $user->subscriber : $user->creator;
                $values['users'][$userData->id]['id'] = $userData->id;
                $values['users'][$userData->id]['name'] = $userData->name;
                $values['users'][$userData->id]['avatar'] = $userData->avatar;
                $values['users'][$userData->id]['label'] = '<div><img class="searchAvatar" src="uploads/users/avatars/'.$userData->avatar.'" alt=""><span class="name">'.$userData->name.'</span></div>';
            }

            // Fetching users that are being followed for free
            $freeFollowIDs = PostsHelperServiceProvider::getFreeFollowingProfiles(Auth::user()->id);
            $freeFollowUsers = User::whereIn('id',$freeFollowIDs)->get();
            foreach ($freeFollowUsers as $k => $user) {
                $values['users'][$user->id]['id'] = $user->id;
                $values['users'][$user->id]['name'] = $user->name;
                $values['users'][$user->id]['avatar'] = $user->avatar;
                $values['users'][$user->id]['label'] = '<div><img class="searchAvatar" src="uploads/users/avatars/'.$user->avatar.'" alt=""><span class="name">'.$user->name.'</span></div>';
            }

            // Fetching follower profiles if own profile is free/open
            if(!Auth::user()->paid_profile || (getSetting('profiles.allow_users_enabling_open_profiles') && Auth::user()->open_profile)) {
                $list = ListsHelperServiceProvider::getUserFollowersList();
                foreach ($list->members as $k => $user) {
                    $values['users'][$user->id]['id'] = $user->id;
                    $values['users'][$user->id]['name'] = $user->name;
                    $values['users'][$user->id]['avatar'] = $user->avatar;
                    $values['users'][$user->id]['label'] = '<div><img class="searchAvatar" src="uploads/users/avatars/'.$user->avatar.'" alt=""><span class="name">'.$user->name.'</span></div>';
                }
            }

        }

        return $values['users'];
    }


    public static function cleanUpMessageData($message){
        // Cleaning up the message data, removing any sensitive / un-needed data
        $toRemove = ['settings','role_id','email','postcode','country','state','birthdate','billing_address','auth_provider','auth_provider_id',
            'public_profile','identity_verified_at','enable_2fa','created_at','email_verified_at', 'updated_at', 'paid_profile',
            'profile_access_price_3_months','profile_access_price','profile_access_price_6_months','profile_access_price_12_months', 'enable_geoblocking'];
        foreach($toRemove as $prop){
            unset($message->sender[$prop]);
            unset($message->receiver[$prop]);
        }
        if($message->hasUserUnlockedMessage == false && ($message->price && $message->price > 0) && $message->sender->id !== Auth::user()->id){
            unset($message->attachments);
            $message->attachments = collect([]);
        }
        $message->sender['canEarnMoney'] = Auth::user()->id === $message->sender->id ? GenericHelperServiceProvider::creatorCanEarnMoney($message->receiver) : GenericHelperServiceProvider::creatorCanEarnMoney($message->sender);

        return $message;
    }

    /**
     * Checks messenger access
     * @param $viewerID
     * @param $contactId
     * @return bool
     */
    protected static function checkMessengerAccess($viewerID, $contactId)
    {
        if (Auth::check()) {
            $viewerUser = Auth::user();
        } else {
            $viewerUser = User::where('id', $viewerID)->first();
        }
        $contactUser = User::where('id', $contactId)->first();
        if ($viewerUser) {
            // Is subscribed to user
            if (PostsHelperServiceProvider::hasActiveSub($viewerUser->id, $contactUser->id)) {
                return true;
            }
            if ($viewerUser->id === $contactUser) {
                return true;
            }

            // handles chat access for creators so they can message their subscribers without subscribing back
            if (PostsHelperServiceProvider::hasActiveSub($contactUser->id, $viewerUser->id)) {
                return true;
            }

            // Contacted user has free profile
            if (!$contactUser->paid_profile && ListsHelperServiceProvider::loggedUserIsFollowingUser($contactUser->id)) {
                return true;
            }

            // Contacted user has open profile
            if ($contactUser->open_profile && ListsHelperServiceProvider::loggedUserIsFollowingUser($contactUser->id)) {
                return true;
            }

            if ($viewerUser->role_id === 1 || $contactUser->role_id === 1) {
                return true;
            }
            // + If paid creator first created a conversation between him and a open/free profile, set sub = true for the free profile
            if (( !$viewerUser->paid_profile || $viewerUser->open_profile ) && $contactUser->paid_profile) {
                $senderID = $viewerUser->id;
                $receiverID = $contactUser->id;
                $conversation = UserMessage::with(['sender', 'receiver', 'attachments'])->where(function ($q) use ($senderID, $receiverID) {
                    $q->where('sender_id', $senderID)
                        ->where('receiver_id', $receiverID);
                })
                    ->orWhere(
                        function ($q) use ($senderID, $receiverID) {
                            $q->where('receiver_id', $senderID)
                                ->Where('sender_id', $receiverID);
                        }
                    )
                    ->orderBy('created_at', 'ASC')
                    ->first();
                if ($conversation && $conversation->sender_id === $contactUser->id) {
                    return true;
                }
            }
            // Handling access when both profiles are either free or open an users have a follow relation from any of them
            if(
                (($viewerUser->open_profile && $contactUser->open_profile) || (!$viewerUser->paid_profile && !$contactUser->paid_profile))
                &&
                (
                    ListsHelperServiceProvider::isUserFollowing($viewerID, $contactId) ||
                    ListsHelperServiceProvider::isUserFollowing($contactId, $viewerID)
                )
            ){
                return true;
            }
            // Creator is free/open & wants to message the follower
            if((!$viewerUser->paid_profile || $viewerUser->open_profile) && ListsHelperServiceProvider::isUserFollowing($contactId, $viewerID)){
                return true;
            }

        }
        return false;
    }

    /**
     * Method used for deleting messenger messages
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteMessage(Request $request){
        $messageID = $request->route('commentID');
        $message = UserMessage::where('id', $messageID)->where('sender_id', Auth::user()->id)->withCount('messagePurchases')->first();

        // Checking if the deleted message is the last one
        $isLastMessage = UserMessage::where(function ($q) use ($message) {
            $q->where('sender_id', Auth::user()->id)
                ->where('receiver_id', $message->receiver_id);
        })->orWhere(
            function ($q) use ($message) {
                $q->where('receiver_id', Auth::user()->id)
                    ->Where('sender_id', $message->receiver_id);
            }
        )->count();

        if(getSetting('compliance.disable_creators_ppv_delete')){
            if($message->message_purchases_count > 0){
                return response()->json(['success' => false, 'message' => __('The message has been bought and can not be deleted.')],500);
            }
        }

        if(!$message){
            return response()->json(['success' => false, 'message' => __('Message can not be found.')],500);
        }
        try {
            $message->delete();
            return response()->json([
                'status' => 'success',
                'isLastMessage' => $isLastMessage === 1 ? true : false,
            ]);
        } catch (\Exception $exception) {
            return response()->json(['success' => false, 'message' => $exception->getMessage()],500);
        }
    }


}
