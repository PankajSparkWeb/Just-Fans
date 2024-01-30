{{-- on click event for open full div in  --}}
<div class="post-box post-box-container-section {{isset($is_visited) && $is_visited ? 'visited_post' : '' }}" data-postID="{{ $post->id }}"  >
    <div class="post-content mt-3  pl-3 pr-3 upvote_downvote_section">
        @if (
            $post->isSubbed ||
                (Auth::check() && getSetting('profiles.allow_users_enabling_open_profiles') && $post->user->open_profile))
            @php
                $is_react_type = PostsHelper::didUserReact($post->reactions, true);
            @endphp
            <div class="upvote-downvote ">
                <div title="{{ __('Upvote') }}"
                    class="upvote-icon react-button exclude-child {{ $is_react_type == 'like' ? 'active' : '' }}"
                    onclick="Post.reactToPost(this, 'post',{{ $post->id }}, 'like')">
                    <div class="upvoteLink">
                        <span class="material-symbols-outlined">shift</span>
                    </div>
                </div>
                <div class="vote_counter">
                    <span class="ml-2-h">
                        <strong class="text-bold post-reactions-label-count">{{ $post->count_reactions }}</strong>
                    </span>
                </div>
                <div title="{{ __('Downvote') }}"
                    class="down-vote-icon react-button exclude-child 
                    {{ $is_react_type == 'dislike' ? 'active' : '' }}"
                    onclick="Post.reactToPost(this, 'post',{{ $post->id }}, 'dislike')">
                    <div class="downvoteLink">
                        <span class="material-symbols-outlined">shift</span>
                    </div>
                </div>
            </div>
        @else
            {{-- Not login redirect to login page --}}
            <div class="upvote-downvote">
                <div class="upvote-icon exclude-on">
                    <a href="{{route('login')}}" class="upvoteLink exclude-on">
                        <span class="material-symbols-outlined exclude-on">shift</span></a>
                </div>
                <div class="vote_counter">
                    <span class="ml-2-h">
                        <strong class="text-bold post-reactions-label-count">{{ $post->count_reactions }}</strong>
                    </span>
                </div>
                <div class="down-vote-icon">
                    <a href="{{route('login')}}" class="downvoteLink">
                        <span class="material-symbols-outlined">shift</span></a>
                </div>
            </div>
        @endif
        <div class="post-text-area-main">
            <div class="post-header pl-3 pr-3 post-header-top">
                <div class="d-flex post-header-flex-area">
                    {{-- <div class="avatar-wrapper post-icon-img">
                        <img class="avatar rounded-circle" src="{{ $post->user->avatar }}">
                    </div> --}}
                    <div class="post-details pl-2 w-100 post-details-heading-wrapper{{ $post->is_pinned ? '' : '' }}">
                        <div class="d-flex justify-content-between post-header-justify top-header-post-justify">
                            <div>
                                <div class="text-bold post-url-image"><a
                                        href="{{ route('profile', ['username' => $post->user->username]) }}"
                                        class="text-dark-r">{{ $post->user->name }}</a></div>
                                {{-- <div class='post-url-next-page'><a
                                        href="{{ route('profile', ['username' => $post->user->username]) }}"
                                        class="text-dark-r text-hover"><span>@</span>{{ $post->user->username }}</a>
                                </div> --}}
                            </div>

                            <div class="d-flex">

                                @if (Auth::check() &&
                                        (($post->user_id === Auth::user()->id && $post->status == 0) ||
                                            (Auth::user()->role_id === 1 && $post->status == 0)))
                                    <div class="pr-3 pr-md-3"><span
                                            class="badge badge-pill bg-gradient-faded-secondary">{{ ucfirst(__('pending')) }}</span>
                                    </div>
                                @endif

                                @if ($post->expire_date)
                                    <div class="pr-3 pr-md-3">
                                        <span class="badge badge-pill bg-gradient-faded-primary"
                                            data-toggle="{{ !$post->is_expired ? 'tooltip' : '' }}"
                                            data-placement="bottom"
                                            title="{{ !$post->is_expired ? __('Expiring in') . '' . \Carbon\Carbon::parse($post->expire_date)->diffForHumans(null, false, true) : '' }}">
                                            {{ !$post->is_expired ? ucfirst(__('Expiring')) : ucfirst(__('Expired')) }}
                                        </span>
                                    </div>
                                @endif
                                @if (Auth::check() && $post->release_date && Auth::user()->id === $post->user_id && $post->is_scheduled)
                                    @if ($post->release_date > \Carbon\Carbon::now())
                                        <div class="pr-3 pr-md-3">
                                            <span class="badge badge-pill bg-gradient-faded-primary"
                                                data-toggle="{{ $post->is_scheduled ? 'tooltip' : '' }}"
                                                data-placement="bottom"
                                                title="{{ $post->is_scheduled ? __('Posting in') . '' . \Carbon\Carbon::parse($post->release_date)->diffForHumans(null, false, true) : '' }}">
                                                {{ ucfirst(__('Scheduled')) }}
                                            </span>
                                        </div>
                                    @endif
                                @endif
                                @if (Auth::check() && $post->user_id === Auth::user()->id && $post->price > 0)
                                    <div class="pr-3 pr-md-3"><span
                                            class="badge badge-pill bg-gradient-faded-primary">{{ ucfirst(__('PPV')) }}</span>
                                    </div>
                                @endif

                                @if (Auth::check() && $post->user_id === Auth::user()->id)
                                    <div
                                        class="pr-3 pr-md-3 pt-1 {{ $post->is_pinned ? '' : 'd-none' }} pinned-post-label">
                                        <span data-toggle="tooltip" data-placement="bottom"
                                            title="{{ __('Pinned post') }}">
                                            @include('elements.icon', [
                                                'icon' => 'pricetag-outline',
                                                'classes' => 'text-primary',
                                            ])
                                        </span>
                                    </div>
                                @endif

                                <div class="pr-3 pr-md-3">
                                    <a class="text-dark-r text-hover d-flex top-minutes-show"
                                        onclick="PostsPaginator.goToPostPageKeepingNav({{ $post->id }},{{ $post->postPage }},'{{ route('posts.get', ['post_id' => $post->id, 'username' => $post->user->username]) }}')"
                                        href="javascript:void(0)">
                                        {{ $post->created_at->diffForHumans(null, false, true) }}
                                    </a>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="post-visit post-para"
                onclick="window.location.href = '{{ Route::currentRouteName() != 'posts.get' ? route('posts.get', ['post_id' => $post->id, 'username' => $post->user->username]) : '#comments' }}';">
                <p
                    class="text-break post-content-data  {{ getSetting('feed.enable_post_description_excerpts') && (strlen($post->text) >= 85 || substr_count($post->text, "\r\n") > 1) ? 'line-clamp-1 pb-0 mb-0' : '' }}">
                    {!! $post->text !!}</p>
                @if (getSetting('feed.enable_post_description_excerpts') &&
                        (strlen($post->text) >= 85 || substr_count($post->text, "\r\n") > 1))
                    <span class="text-primary pointer-cursor"
                        onclick="Post.toggleFullDescription({{ $post->id }})">
                        <span class="label-more">{{ __('More info') }}</span>
                        <span class="label-less d-none">{{ __('Show less') }}</span>
                    </span>
                @endif
            </div>
            @if ($post->external_post_link)
            <a href="{{ $post->external_post_link }}" target="_blank"
                class='view_ext_link'>{{ $post->external_post_link }}</a>
        @endif
            <div class="post-footer mt-3 pl-3 pr-3 post-bottom-footer">
                <div class="footer-actions d-flex justify-content-between">
                    <div class="d-flex footer-icon-flex-wrap">
                        {{-- Likes --}}
                        {{--
                    @if ($post->isSubbed || (Auth::check() && getSetting('profiles.allow_users_enabling_open_profiles') && $post->user->open_profile))
                        <div class="h-pill h-pill-primary mr-1 rounded react-button {{ PostsHelper::didUserReact($post->reactions) ? 'active' : '' }}"
                            data-toggle="tooltip" data-placement="top" title="{{ __('Like') }}"
                            onclick="Post.reactTo('post',{{ $post->id }})">
                            @if (PostsHelper::didUserReact($post->reactions))
                                @include('elements.icon', [
                                    'icon' => 'heart',
                                    'variant' => 'medium',
                                    'classes' => 'text-primary',
                                ])
                            @else
                                @include('elements.icon', ['icon' => 'heart-outline', 'variant' => 'medium'])
                            @endif
                        </div>
                    @else
                        <div class="h-pill h-pill-primary mr-1 rounded react-button disabled">
                            @include('elements.icon', ['icon' => 'heart-outline', 'variant' => 'medium'])
                        </div>
                    @endif
                    
                --}}
                        {{-- Comments --}}
                        @if (Route::currentRouteName() != 'posts.get')
                            @if (
                                $post->isSubbed ||
                                    (Auth::check() && getSetting('profiles.allow_users_enabling_open_profiles') && $post->user->open_profile))
                                <div class='comment-wrapper'>
                                <div class="h-pill h-pill-primary mr-1 rounded" data-toggle="tooltip"
                                    data-placement="top" title="{{ __('Show comments') }}"
                                    onclick="window.location.href = '{{ Route::currentRouteName() != 'posts.get' ? route('posts.get', ['post_id' => $post->id, 'username' => $post->user->username]) : '#comments' }}';">
                                    @include('elements.icon', [
                                        'icon' => 'chatbubble-outline',
                                        'variant' => 'medium',
                                    ])

                                </div>
                               
                                <span class="ml-2-h d-none d-lg-block padding-remover">
                                    <a onclick="window.location.href = '{{ Route::currentRouteName() != 'posts.get' ? route('posts.get', ['post_id' => $post->id, 'username' => $post->user->username]) : '#comments' }}';"
                                        class="text-dark-r text-hover">
                                        <strong class="post-comments-label-count post-span-tag">{{ count($post->comments) }}</strong>
                                        <span class="post-comments-label post-span-tag">
                                            {{ trans_choice('comments', count($post->comments)) }}
                                        </span>
                                    </a>
                                </span>
                            </div>
                            @else
                                <div class="h-pill h-pill-primary mr-1 rounded">
                                    <a href="{{route('login')}}">  
                                    @include('elements.icon', [
                                        'icon' => 'chatbubble-outline',
                                        'variant' => 'medium',
                                    ])
                                    </a>
                                </div>
                            @endif
                        @endif

                        {{-- Tips --}}
                        {{-- @if (Auth::check() && $post->user->id != Auth::user()->id)
                        @if ($post->isSubbed || (getSetting('profiles.allow_users_enabling_open_profiles') && $post->user->open_profile))
                            <div class="h-pill h-pill-primary send-a-tip to-tooltip poi {{(!GenericHelper::creatorCanEarnMoney($post->user)) ? 'disabled' : ''}}"
                                 @if (!GenericHelper::creatorCanEarnMoney($post->user))
                                 data-placement="top"
                                 title="{{__('This creator cannot earn money yet')}}">
                                @else
                                    data-toggle="modal"
                                    data-target="#checkout-center"
                                    data-post-id="{{$post->id}}"
                                    data-type="tip"
                                    data-first-name="{{Auth::user()->first_name}}"
                                    data-last-name="{{Auth::user()->last_name}}"
                                    data-billing-address="{{Auth::user()->billing_address}}"
                                    data-country="{{Auth::user()->country}}"
                                    data-city="{{Auth::user()->city}}"
                                    data-state="{{Auth::user()->state}}"
                                    data-postcode="{{Auth::user()->postcode}}"
                                    data-available-credit="{{Auth::user()->wallet->total}}"
                                    data-username="{{$post->user->username}}"
                                    data-name="{{$post->user->name}}"
                                    data-avatar="{{$post->user->avatar}}"
                                    data-recipient-id="{{$post->user_id}}">
                                @endif
                                <div class=" d-flex align-items-center">
                                    @include('elements.icon',['icon'=>'gift-outline', 'variant' => 'medium'])
                                    <div class="ml-1 d-none d-lg-block"> {{__('Send a tip')}} </div>
                                </div>
                            </div>
                        @else
                            <div class="h-pill h-pill-primary send-a-tip disabled">
                                <div class=" d-flex align-items-center">
                                    @include('elements.icon',['icon'=>'gift-outline', 'variant' => 'medium'])
                                    <div class="ml-1 d-none d-md-block"> {{__('Send a tip')}} </div>
                                </div>
                            </div>
                        @endif
                    @endif --}}





                        {{-- Copy Post --}}

                        <div
                            class="dropdown exclude-child {{ GenericHelper::getSiteDirection() == 'rtl' ? 'dropright' : 'dropleft' }}">
                            <a class="post-svg-icon btn btn-sm text-dark-r text-hover btn-outline-{{ Cookie::get('app_theme') == null ? (getSetting('site.default_user_theme') == 'dark' ? 'dark' : 'light') : (Cookie::get('app_theme') == 'dark' ? 'dark' : 'light') }} dropdown-toggle px-2 py-1 m-0"
                                data-toggle="dropdown" href="#" role="button" aria-haspopup="true"
                                aria-expanded="false">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                    fill="currentColor" class="bi bi-arrow-90deg-right" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd"
                                        d="M14.854 4.854a.5.5 0 0 0 0-.708l-4-4a.5.5 0 0 0-.708.708L13.293 4H3.5A2.5 2.5 0 0 0 1 6.5v8a.5.5 0 0 0 1 0v-8A1.5 1.5 0 0 1 3.5 5h9.793l-3.147 3.146a.5.5 0 0 0 .708.708z" />
                                </svg>
                                <span class='post-span-tag'>share</span>
                            </a>
                           
                            <div class="dropdown-menu">
                                <!-- Dropdown menu links -->
                                <a class="dropdown-item" href="javascript:void(0)"
                                    onclick="shareOrCopyLink('{{ route('posts.get', ['post_id' => $post->id, 'username' => $post->user->username]) }}')">{{ __('Copy post link') }}</a>
                                   

                                <!-- Example in a Blade view -->
                                <form action="{{ route('posts.share', ['postId' => $post->id]) }}" method="post">
                                    @csrf
                                    <button type="submit">Share</button>
                                </form>

                            </div>
                        </div>
                        <div
                            class="dropdown exclude-child {{ GenericHelper::getSiteDirection() == 'rtl' ? 'dropright' : 'dropleft' }}">
                            <a class="post-top-third-icon btn btn-sm text-dark-r text-hover btn-outline-{{ Cookie::get('app_theme') == null ? (getSetting('site.default_user_theme') == 'dark' ? 'dark' : 'light') : (Cookie::get('app_theme') == 'dark' ? 'dark' : 'light') }} dropdown-toggle px-2 py-1 m-0"
                                data-toggle="dropdown" href="#" role="button" aria-haspopup="true"
                                aria-expanded="false">
                                @include('elements.icon', ['icon' => 'ellipsis-horizontal-outline'])
                            </a>
                            <div class="dropdown-menu">
                                <!-- Dropdown menu links -->
                                {{-- <a class="dropdown-item" href="javascript:void(0)"
                                        onclick="shareOrCopyLink('{{ route('posts.get', ['post_id' => $post->id, 'username' => $post->user->username]) }}')">{{ __('Copy post link') }}</a> --}}
                                @if (Auth::check())
                                    {{-- <a class="dropdown-item bookmark-button {{ PostsHelper::isPostBookmarked($post->bookmarks) ? 'is-active' : '' }}"
                                            href="javascript:void(0);"
                                            onclick="Post.togglePostBookmark({{ $post->id }});">{{ PostsHelper::isPostBookmarked($post->bookmarks) ? __('Remove the bookmark') : __('Bookmark this post') }}
                                        </a> --}}
                                    @if (Auth::user()->id === $post->user_id)
                                        {{-- <a class="dropdown-item pin-button {{ $post->is_pinned ? 'is-active' : '' }}"
                                                href="javascript:void(0);"
                                                onclick="Post.togglePostPin({{ $post->id }});">{{ $post->is_pinned ? __('Un-pin post') : __('Pin this post') }}
                                            </a> --}}
                                    @endif
                                    @if (Auth::check() && Auth::user()->id != $post->user->id)
                                        <div class="dropdown-divider"></div>
                                        {{-- <a class="dropdown-item" href="javascript:void(0);"
                                                onclick="Lists.showListManagementConfirmation('{{ 'unfollow' }}', {{ $post->user->id }});">{{ __('Unfollow') }}</a>
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                onclick="Lists.showListManagementConfirmation('{{ 'block' }}', {{ $post->user->id }});">{{ __('Block') }}</a> --}}
                                        <a class="dropdown-item" href="javascript:void(0);"
                                            onclick="Lists.showReportBox({{ $post->user->id }},{{ $post->id }});">{{ __('Report') }}</a>
                                    @endif
                                    @if (Auth::check() && Auth::user()->id == $post->user->id)
                                        <div class="dropdown-divider"></div>
                                        <a class="dropdown-item"
                                            href="{{ route('posts.edit', ['post_id' => $post->id]) }}">{{ __('Edit post') }}</a>
                                        @if (
                                            !getSetting('compliance.minimum_posts_deletion_limit') ||
                                                (getSetting('compliance.minimum_posts_deletion_limit') > 0 &&
                                                    count($post->user->posts) > getSetting('compliance.minimum_posts_deletion_limit')))
                                            <a class="dropdown-item" href="javascript:void(0);"
                                                onclick="Post.confirmPostRemoval({{ $post->id }});">{{ __('Delete post') }}</a>
                                        @endif
                                    @endif
                                @endif
                            </div>
                        </div>

                    </div>
                    <div class="mt-0 d-flex align-items-center justify-content-center post-count-details">
                        {{--
                    <span class="ml-2-h">
                        <strong class="text-bold post-reactions-label-count">{{ $post->count_reactions }}</strong>
                        <span class="post-reactions-label">{{ trans_choice('likes', $post->count_reactions) }}</span>
                    </span>
                --}}
                        {{-- @if (
                            $post->isSubbed ||
                                (Auth::check() && getSetting('profiles.allow_users_enabling_open_profiles') && $post->user->open_profile))
                        @else
                            <span class="ml-2-h d-none d-lg-block">
                                <strong class="post-comments-label-count">{{ count($post->comments) }}</strong>
                                <span class="post-comments-label">
                                    {{ trans_choice('comments', count($post->comments)) }}
                                </span>
                            </span>
                        @endif --}}
                        {{-- <span class="ml-2-h d-none d-lg-block">
                            <strong class="post-tips-label-count">{{ $post->tips_count }}</strong>
                            <span class="post-tips-label">{{ trans_choice('tips', $post->tips_count) }}</span>
                        </span> --}}
                    </div>
                </div>
            </div>
           

        </div>
    </div>

    @if (count($post->attachments))
        <div class="post-media">
            @if ($post->isSubbed || (getSetting('profiles.allow_users_enabling_open_profiles') && $post->user->open_profile))
                @if (
                    (Auth::check() &&
                        Auth::user()->id !== $post->user_id &&
                        $post->price > 0 &&
                        !PostsHelper::hasUserUnlockedPost($post->postPurchases)) ||
                        (!Auth::check() && $post->price > 0))
                    @include('elements.feed.post-locked', ['type' => 'post', 'post' => $post])
                @else
                    @if (count($post->attachments) > 1)
                        <div class="swiper-container mySwiper pointer-cursor">
                            <div class="swiper-wrapper">
                                @foreach ($post->attachments as $attachment)
                                    <div class="swiper-slide">
                                        @include('elements.feed.post-box-media-wrapper', [
                                            'attachment' => $attachment,
                                            'isGallery' => true,
                                        ])
                                    </div>
                                @endforeach
                            </div>
                            <div class="swiper-button swiper-button-next p-pill-white">@include('elements.icon', ['icon' => 'chevron-forward-outline'])
                            </div>
                            <div class="swiper-button swiper-button-prev p-pill-white">@include('elements.icon', ['icon' => 'chevron-back-outline'])
                            </div>
                            <div class="swiper-pagination"></div>
                        </div>
                    @else
                        @include('elements.feed.post-box-media-wrapper', [
                            'attachment' => $post->attachments[0],
                            'isGallery' => false,
                        ])
                    @endif
                @endif
            @else
                @include('elements.feed.post-locked', ['type' => 'subscription'])
            @endif
        </div>
    @endif


    <!-- Start interst Need to remove code-->
    {{-- {{ $post->id }}
    @if ($post->interests->isNotEmpty())
        @foreach ($post->interests as $interest)
            {{ $interest->name }} ,
        @endforeach
    @endif --}}
    <!-- END interst Need to remove code-->

    @if (
        $post->isSubbed ||
            (Auth::check() && getSetting('profiles.allow_users_enabling_open_profiles') && $post->user->open_profile))
        <div class="post-comments d-none" {{ Route::currentRouteName() == 'posts.get' ? 'id="comments"' : '' }}>
            <hr>

            <div class="px-3 post-comments-wrapper">
                <div class="comments-loading-box">
                    @include('elements.preloading.messenger-contact-box', ['limit' => 1])
                </div>
            </div>
            <div class="show-all-comments-label pl-3 d-none">
                @if (Route::currentRouteName() != 'posts.get')
                    <a href="javascript:void(0)"
                        onclick="PostsPaginator.goToPostPageKeepingNav({{ $post->id }},{{ $post->postPage }},'{{ route('posts.get', ['post_id' => $post->id, 'username' => $post->user->username]) }}')">{{ __('Show more') }}</a>
                @else
                    <a onClick="CommentsPaginator.loadResults({{ $post->id }});"
                        href="javascript:void(0);">{{ __('Show more') }}</a>
                @endif
            </div>
            <div class="no-comments-label pl-3 d-none">
                {{ __('No comments yet.') }}
            </div>
            @if (Auth::check())
                <hr>
                @include('elements.feed.post-new-comment')
            @endif
        </div>
    @endif




</div>
