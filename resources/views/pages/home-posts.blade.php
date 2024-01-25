@extends('layouts.NewHomeLayout')
@section('page_title', __('Feeds'))
{{-- Page specific CSS --}}
@section('styles')
    {!! Minify::stylesheet([
        '/libs/swiper/swiper-bundle.min.css',
        '/libs/photoswipe/dist/photoswipe.css',
        '/css/pages/checkout.css',
        '/libs/photoswipe/dist/default-skin/default-skin.css',
        '/css/pages/feed.css',
        '/css/posts/post.css',
        '/css/pages/search.css',
    ])->withFullUrl() !!}
    @if (getSetting('feed.post_box_max_height'))
        @include('elements.feed.fixed-height-feed-posts', [
            'height' => getSetting('feed.post_box_max_height'),
        ])
    @endif
@stop

{{-- Page specific JS --}}
@section('scripts')
    {!! Minify::javascript([
        '/js/PostsPaginator.js',
        '/js/CommentsPaginator.js',
        '/js/Post.js',
        '/js/SuggestionsSlider.js',
        '/js/pages/lists.js',
        '/js/pages/feed.js',
        '/js/pages/checkout.js',
        '/libs/swiper/swiper-bundle.min.js',
        '/js/plugins/media/photoswipe.js',
        '/libs/photoswipe/dist/photoswipe-ui-default.min.js',
        '/libs/@joeattardi/emoji-button/dist/index.js',
        '/js/plugins/media/mediaswipe.js',
        '/js/plugins/media/mediaswipe-loader.js',
    ])->withFullUrl() !!}
@stop


@section('content')
    @include('elements.feed.two-top-bars')
    {{-- Post Section Start --}}

    <div class="row feed-top-wrapper feed-without-login">
        <div class="content-post">
            <div class="container posts-flex-without-login">
                <div class="post-pagination-wrapper">
                @include('elements.feed.posts-wrapper', ['posts' => $posts])
                {{-- {{ $posts->links() }}  --}}
                </div>
            <div class="col-12 col-sm-12 col-md-5 col-lg-4 first border-left order-0 pt-4 pb-5 min-vh-100 suggestions-wrapper d-none d-md-block sidebar-wraper sidebar-wrapper-without-login">
                <div class="feed-widgets">
                    <div class="mb-4">
                        @include('template.NewSideBar')
                    </div>
                </div>

            </div>
            </div>
            <!-- Display pagination links -->
         
        </div>
            
        
    </div>
    
    <div class="content-wiki">
        <div class="container d-flex feed-container-outer">
            <div class="wikipeadia-area wiki-inner">
                @include('elements.feed.wiki-text-area')
            </div>
            <div
                class="col-12 col-sm-12 col-md-5 col-lg-4 first border-left order-0 pt-4 pb-5 min-vh-100 suggestions-wrapper d-none d-md-block sidebar-wraper">
                <div class="feed-widgets">
                    <div class="mb-4">
                        @include('template.NewSideBar')
                    </div>
                </div>

            </div>
        </div>
    </div>

@stop
