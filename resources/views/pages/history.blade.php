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
    <div class="row">
        <div class="justify-content-center align-items-center mt-4'}}">
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
            @include('elements.profile.commentHistory', ['history' => $postscommentsHistory])
        @elseif($activeTab == 'likes')
            <!-- Display likes history content -->
            @include('elements.profile.shareHistory', ['history' => $shareHistory])
        @endif

            @include('elements.feed.posts-loading-spinner')
        </div>
    </div>

@stop
