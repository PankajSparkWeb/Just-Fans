<!doctype html>
<html class="h-100" dir="{{GenericHelper::getSiteDirection()}}" lang="{{session('locale')}}">
<head>
    @include('template.head',['additionalCss' => [
                
             ]])
</head>
<body class="d-flex flex-column">
    @include('template.NewHeader')
    <div class="flex-fill">
       
    
        <div class="container-xl overflow-x-hidden-m">
            <div class="row main-wrapper">
                
                <div class="col-12 col-md-9 min-vh-100 border-left px-0 overflow-x-hidden-m content-wrapper {{(in_array(Route::currentRouteName(),['feed','profile','my.messenger.get','search.get','my.notifications','my.bookmarks','my.lists.all','my.lists.show','my.settings','posts.get']) ? '' : 'border-right' )}}">
                    @yield('content')
                </div>
            </div>
            <div class="d-block d-md-none fixed-bottom">
                @include('elements.mobile-navbar')
            </div>
        </div>
    
    </div>
    @if(getSetting('compliance.enable_age_verification_dialog'))
        @include('elements.site-entry-approval-box')
    @endif
    @include('template.footer-compact',['compact'=>true])
    @include('template.jsVars')
    @include('template.jsAssets',['additionalJs' => [
                   '/libs/jquery-backstretch/jquery.backstretch.min.js',
                   '/libs/wow.js/dist/wow.min.js',
                   '/libs/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js',
                   '/js/SideMenu.js'
    ]])
    
    </body>
</html>
