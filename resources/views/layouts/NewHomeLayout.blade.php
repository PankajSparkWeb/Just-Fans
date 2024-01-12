<!doctype html>
<html class="h-100" dir="{{ GenericHelper::getSiteDirection() }}" lang="{{ session('locale') }}">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
<head>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
    <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>


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
