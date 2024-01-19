@extends('layouts.NewHomeLayout')

@section('page_title', __('user_profile_title_label', ['user' => $user->name]))
@section('share_url', route('home'))
@section('share_title', __('user_profile_title_label', ['user' => $user->name]) . ' - ' . getSetting('site.name'))
@section('share_description', $seo_description ?? getSetting('site.description'))
@section('share_type', 'article')
@section('share_img', $user->cover)

@section('scripts')
    {!! Minify::javascript(
        array_merge(
            [
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
            ],
            $additionalAssets,
        ),
    )->withFullUrl() !!}
@stop

@section('styles')
    {!! Minify::stylesheet([
        '/css/pages/profile.css',
        '/css/pages/checkout.css',
        '/css/pages/lists.css',
        '/libs/swiper/swiper-bundle.min.css',
        '/libs/photoswipe/dist/photoswipe.css',
        '/libs/photoswipe/dist/default-skin/default-skin.css',
        '/css/pages/profile.css',
        '/css/pages/lists.css',
        '/css/posts/post.css',
    ])->withFullUrl() !!}
    @if (getSetting('feed.post_box_max_height'))
        @include('elements.feed.fixed-height-feed-posts', [
            'height' => getSetting('feed.post_box_max_height'),
        ])
    @endif
@stop

@section('meta')
    @if (getSetting('security.recaptcha_enabled') && !Auth::check())
        {!! NoCaptcha::renderJs() !!}
    @endif
    @if ($activeFilter)
        <link rel="canonical" href="{{ route('profile', ['username' => $user->username]) }}" />
    @endif
@stop

@section('content')
    <div class="container profile-container">
        <div class="row">
            <div class="min-vh-100 col-12 col-md-8 border-right pr-md-0">
                <div class="container pt-2 pl-0 pr-0">
                    <div class="mt-3 inline-border-tabs">
                        <ul class="nav nav-pills nav-justified text-bold">
                            <li class="nav-item">
                                <a href="#overview" class="nav-link">Overview</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('profile', ['username' => $user->username]) }}"
                                    class="nav-link {{ $activeFilter == false ? 'active' : '' }}">
                                    {{ trans_choice('posts', $posts->total(), ['number' => $posts->total()]) }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a href="#overview" class="nav-link">History</a>
                            </li>
                            <li class="nav-item">
                                <a href="#overview" class="nav-link">Comments</a>
                            </li>
                        </ul>

                        @if ($filterTypeCounts['image'] > 0)
                            <a class="nav-item nav-link {{ $activeFilter == 'image' ? 'active' : '' }}"
                                href="{{ route('profile', ['username' => $user->username]) . '?filter=image' }}">{{ trans_choice('images', $filterTypeCounts['image'], ['number' => $filterTypeCounts['image']]) }}</a>
                        @endif

                        @if ($filterTypeCounts['video'] > 0)
                            <a class="nav-item nav-link {{ $activeFilter == 'video' ? 'active' : '' }}"
                                href="{{ route('profile', ['username' => $user->username]) . '?filter=video' }}">{{ trans_choice('videos', $filterTypeCounts['video'], ['number' => $filterTypeCounts['video']]) }}</a>
                        @endif

                        @if ($filterTypeCounts['audio'] > 0)
                            <a class="nav-item nav-link {{ $activeFilter == 'audio' ? 'active' : '' }}"
                                href="{{ route('profile', ['username' => $user->username]) . '?filter=audio' }}">{{ trans_choice('audio', $filterTypeCounts['audio'], ['number' => $filterTypeCounts['audio']]) }}</a>
                        @endif

                        @if (getSetting('streams.allow_streams'))
                            @if (isset($filterTypeCounts['streams']) && $filterTypeCounts['streams'] > 0)
                                <a class="nav-item nav-link {{ $activeFilter == 'streams' ? 'active' : '' }}"
                                    href="{{ route('profile', ['username' => $user->username]) . '?filter=streams' }}">
                                    {{ $filterTypeCounts['streams'] }}
                                    {{ trans_choice('streams', $filterTypeCounts['streams'], ['number' => $filterTypeCounts['streams']]) }}</a>
                            @endif
                        @endif

                        </nav>
                    </div>
                    <div
                        class="justify-content-center align-items-center {{ Cookie::get('app_feed_prev_page') &&PostsHelper::isComingFromPostPage(request()->session()->get('_previous'))? 'mt-3': 'mt-4' }}">
                        @if ($activeFilter !== 'streams')
                            @include('elements.feed.posts-load-more', ['classes' => 'mb-2'])
                            <div class="feed-box mt-0 posts-wrapper">
                                @include('elements.feed.posts-wrapper', ['posts' => $posts])
                            </div>
                        @else
                            <div class="streams-box mt-4 streams-wrapper mb-4">
                                @include('elements.search.streams-wrapper', [
                                    'streams' => $streams,
                                    'showLiveIndicators' => true,
                                    'showUsername' => false,
                                ])
                            </div>
                        @endif
                        @include('elements.feed.posts-loading-spinner')
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 d-none d-md-block pt-3">
                @include('elements.profile.side-bar')
            </div>
        </div>
    </div>

    <div class="d-none">
        <ion-icon name="heart"></ion-icon>
        <ion-icon name="heart-outline"></ion-icon>
    </div>

    @if (Auth::check())
        @include('elements.lists.list-add-user-dialog', [
            'user_id' => $user->id,
            'lists' => ListsHelper::getUserLists(),
        ])
        @include('elements.checkout.checkout-box')
        @include('elements.messenger.send-user-message', ['receiver' => $user])
    @else
        @include('elements.modal-login')
    @endif

    @include('elements.profile.qr-code-dialog')

@stop
