<!doctype html>
<html class="h-100" dir="{{ GenericHelper::getSiteDirection() }}" lang="{{ session('locale') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
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
