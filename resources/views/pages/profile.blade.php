@extends('layouts.NewHomeLayout')

@section('page_title',  __("user_profile_title_label",['user' => $user->name]))
@section('share_url', route('home'))
@section('share_title',  __("user_profile_title_label",['user' => $user->name]) . ' - ' .  getSetting('site.name'))
@section('share_description', $seo_description ?? getSetting('site.description'))
@section('share_type', 'article')
@section('share_img', $user->cover)

@section('scripts')
    {!!
        Minify::javascript(array_merge([
            '/js/PostsPaginator.js',
            '/js/CommentsPaginator.js',
            '/js/StreamsPaginator.js',
            '/js/Post.js',
            '/js/pages/profile.js',
            '/js/pages/lists.js',
            '/js/pages/checkout.js',
            '/libs/swiper/swiper-bundle.min.js',
            '/js/plugins/media/photoswipe.js',
            '/libs/photoswipe/dist/photoswipe-ui-default.min.js',
            '/libs/@joeattardi/emoji-button/dist/index.js',
            '/js/plugins/media/mediaswipe.js',
            '/js/plugins/media/mediaswipe-loader.js',
            '/js/LoginModal.js',
            '/js/messenger/messenger.js',
         ],$additionalAssets))->withFullUrl()
    !!}
@stop

@section('styles')
    {!!
        Minify::stylesheet([
            '/css/pages/profile.css',
            '/css/pages/checkout.css',
            '/css/pages/lists.css',
            '/libs/swiper/swiper-bundle.min.css',
            '/libs/photoswipe/dist/photoswipe.css',
            '/libs/photoswipe/dist/default-skin/default-skin.css',
            '/css/pages/profile.css',
            '/css/pages/lists.css',
            '/css/posts/post.css'
         ])->withFullUrl()
    !!}
    @if(getSetting('feed.post_box_max_height'))
        @include('elements.feed.fixed-height-feed-posts', ['height' => getSetting('feed.post_box_max_height')])
    @endif
@stop

@section('meta')
    @if(getSetting('security.recaptcha_enabled') && !Auth::check())
        {!! NoCaptcha::renderJs() !!}
    @endif
    @if($activeFilter)
        <link rel="canonical" href="{{route('profile',['username'=> $user->username])}}" />
    @endif
@stop

@section('content')
<div class='post-tab-outer'>
<div class="mt-3 inline-border-tabs top-tab-header">
    <div class="border-wrapper">
                {{-- Nav For profile --}}
    <nav class="nav nav-pills nav-justified text-bold post-top-navbar">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <a class="nav-link {{ $activeTab == 'posts' ? 'active' : '' }}" href="{{ route('profile', ['username' => $user->username]) }}">Posts</a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab == 'history' ? 'active' : '' }}" href="{{ route('profile', ['username' => $user->username, 'tab' => 'history']) }}">History</a>
            </li>
            @if(Auth::check())
            <li>
                <a class="nav-item nav-link {{ $activeTab == 'comments' ? 'active' : '' }}" href="{{ route('profile', ['username' => $user->username, 'tab' => 'comments']) }}">Comments</a></li>
            <li>
                <a class="nav-item nav-link {{ $activeTab == 'share' ? 'active' : '' }}" href="{{ route('profile', ['username' => $user->username, 'tab' => 'share']) }}">Share</a>
            </li>
            <li>
                <a class="nav-item nav-link {{ $activeTab == 'learned' ? 'active' : '' }}" href="{{ route('profile', ['username' => $user->username, 'tab' => 'learned']) }}">learned</a>
            </li>
            @else
                
            @endif
            @if(Auth::check() && Auth::user()->id === $user->id)
            <li>
                <a class="nav-item nav-link {{ $activeTab == 'hiddenPosts' ? 'active' : '' }}" href="{{ route('profile', ['username' => $user->username, 'tab' => 'hiddenPosts']) }}">Hidden</a>
            </li>
            @else
                
            @endif
            @if(Auth::check() && Auth::user()->id === $user->id)
            <li>
                <a class="nav-item nav-link {{ $activeTab == 'savedPosts' ? 'active' : '' }}" href="{{ route('profile', ['username' => $user->username, 'tab' => 'savedPosts']) }}">Saved</a>
            </li>
            @else
                
            @endif
        </ul>
        
    </nav>
    </div>
</div>
</div>
    <div class="row all-posts-visited">
        <div class="min-vh-100 col-12 col-md-8 border-right pr-md-0 post-container-left-section">
            <div class="container pt-2 pl-0 pr-0 post-container-div">
                <div class="justify-content-center align-items-center {{(Cookie::get('app_feed_prev_page') && PostsHelper::isComingFromPostPage(request()->session()->get('_previous'))) ? 'mt-3' : ''}}">
                    @if($activeTab == 'posts')
                    <!-- Display posts content -->
                    @include('elements.feed.posts-load-more', ['classes' => 'mb-2'])
                    <div class="feed-box mt-0 posts-wrapper">
                        @include('elements.feed.posts-wrapper',['posts'=>$posts])
                    </div>
                @elseif($activeTab == 'history')
                    <!-- Display history content -->
                    @include('elements.profile.postHistory', ['history' => $postsHistory])
                @elseif($activeTab == 'comments')
                    <!-- Display comments history content -->
                    @if(Auth::check())
                    @include('elements.profile.commentHistory', ['history' => $postscommentsHistory])
                    @else
                    <script>window.location = "{{ route('login') }}";</script>
                    @endif
                @elseif($activeTab == 'share')
                    <!-- Display Share history content -->
                    @if(Auth::check())
                    @include('elements.profile.shareHistory', ['history' => $shareHistory])
                    @else
                    <script>window.location = "{{ route('login') }}";</script>
                    @endif
                @elseif($activeTab == 'learned')
                    <!-- Display Learned history content -->
                    @if(Auth::check())
                    @include('elements.profile.learnedHistory', ['history' => $learnedHistory])
                    @else
                    <script>window.location = "{{ route('login') }}";</script>
                    @endif
                @elseif($activeTab == 'hiddenPosts')
                    <!-- Display Hidden post history content -->
                    @if(Auth::check() && Auth::user()->id === $user->id)
                    @include('elements.profile.hiddenPosts')
                    {{-- , ['history' => $hiddenPosts] --}}
                    @else
                    <script>window.location = "{{ route('login') }}";</script>
                    @endif
                @elseif($activeTab == 'savedPosts')
                    <!-- Display Saved post history content -->
                    @if(Auth::check() && Auth::user()->id === $user->id)
                    @include('elements.profile.savedPosts')
                    {{-- , ['history' => $hiddenPosts] --}}
                    @else
                    <script>window.location = "{{ route('login') }}";</script>
                    @endif
                @endif

                @include('elements.feed.posts-loading-spinner')
                </div>

            </div>

        </div>
        
        
        <div class="col-12 col-md-4 d-none d-md-block pt-3 post-container-profile-right">
            @include('elements.profile.side-bar')
        </div>
    
    </div>

    <div class="d-none">
        <ion-icon name="heart"></ion-icon>
        <ion-icon name="heart-outline"></ion-icon>
    </div>

    @if(Auth::check())
        @include('elements.lists.list-add-user-dialog',['user_id' => $user->id, 'lists' => ListsHelper::getUserLists()])
        @include('elements.checkout.checkout-box')
        @include('elements.messenger.send-user-message',['receiver'=>$user])
    @else
        @include('elements.modal-login')
    @endif

    @include('elements.profile.qr-code-dialog')

@stop
