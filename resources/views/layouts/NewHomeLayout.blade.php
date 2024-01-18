<!doctype html>
<html class="h-100" dir="{{ GenericHelper::getSiteDirection() }}" lang="{{ session('locale') }}">
<head>
    @include('template.head', ['additionalCss' => []])
</head>
<body class="">

    @include('template.NewHeader')

    <div class="wrapper">
        @yield('content')
    </div>

    @if (getSetting('compliance.enable_age_verification_dialog'))
        @include('elements.site-entry-approval-box')
    @endif
    @include('template.jsVars')
    @include('template.jsAssets', [
        'additionalJs' => [
            '/libs/jquery-backstretch/jquery.backstretch.min.js',
            '/libs/wow.js/dist/wow.min.js',
            '/libs/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js',
            '/js/SideMenu.js',
        ],
    ])
</body>

</html>
