<!doctype html>
<html class="h-100" dir="{{GenericHelper::getSiteDirection()}}" lang="{{session('locale')}}">
<head>
    @include('template.head',['additionalCss' => [
                
             ]])
</head>
<body class="">
@include('template.NewHeader')
<div class="blank-first-blue-block"></div>
<div class="white-blank-div post-wiki-tabs">
    <div class="container">
        <div class="top-bar-area">
            <div class="top-white-bar-content">
                <div class="today_i_learnd_logo"><img src="img\logos\TodayLearndLogo.png" alt="IMG"></div>
                <div class="today_i_learnd_text_JoinBtn">
                <div class="today_i_learnd_text">
                    <h2>Today I Learned (TIL)</h2>
                    <p>r/todayilearned</p>
                </div>
                    <div class="today_i_learnd_Join&Bell">
                        <a href="">Join</a>
                    </div>
                    
            </div>
        </div>
            <div class="top-white-bar-btns">
    <a href="#" onclick="showContent('post')">Post</a>
    <a href="#" onclick="showContent('wiki')">Wiki</a>
</div>
</div>
</div>
</div>

<div class="content-post">
    <div class="container">
        <div class="inner">
            <div class="wrapper">
                @yield('content')
            </div>
            <div class="Side_bar"></div>
        </div>
    </div>
</div>

<div class="content-wiki">
    <div class="container">
        <div class="inner">
            <div class="wrapper">
                @yield('content-wiki')
            </div>
            <div class="Side_bar"></div>
        </div>
    </div>
</div>

@if(getSetting('compliance.enable_age_verification_dialog'))
    @include('elements.site-entry-approval-box')
@endif
@include('template.jsVars')
@include('template.jsAssets',['additionalJs' => [
               '/libs/jquery-backstretch/jquery.backstretch.min.js',
               '/libs/wow.js/dist/wow.min.js',
               '/libs/malihu-custom-scrollbar-plugin/jquery.mCustomScrollbar.concat.min.js',
               '/js/SideMenu.js'
]])
</body>
</html>
