<!-- Add these CDN links in your HTML file -->


<nav class="new-navbar navbar-expand-md {{(Cookie::get('app_theme') == null ? (getSetting('site.default_user_theme') == 'dark' ? 'navbar-dark bg-dark' : 'navbar-light bg-white') : (Cookie::get('app_theme') == 'dark' ? 'navbar-dark bg-dark' : 'navbar-light bg-white'))}} ">
    
    <div class="row container-fluid new-header justify-content-between">

        <a class="navbar-brand" href="{{ route('home') }}">
        <img src="{{asset( (Cookie::get('app_theme') == null ? (getSetting('site.default_user_theme') == 'dark' ? getSetting('site.dark_logo') : getSetting('site.light_logo')) : (Cookie::get('app_theme') == 'dark' ? getSetting('site.dark_logo') : getSetting('site.light_logo'))) )}}" class="d-inline-block align-top mr-1 ml-3" alt="{{__("Site logo")}}">
</a>

<div class="dropdown-menu-header" aria-labelledby="dropdownMenuButton">
    Menu
    <div class="dropdown-menu" id="myDropdown">
        <input type="text" id="searchInput" placeholder="Filter" onclick="event.stopPropagation();">
        <li>FEEDS</li>
        <li><a class="dropdown-items menu-option" href="{{ route('home') }}">Home</a></li>
        <li><a class="dropdown-items menu-option" href="#">Popular</a></li>
        <li><a class="dropdown-items menu-option" href="#">All</a></li>
        <li>OTHER</li>
        <li><a class="dropdown-items menu-option" href="{{route('my.settings')}}">User Settings</a></li>
        <li><a class="dropdown-items menu-option" href="{{route('my.messenger.get')}}">Messages</a></li>
        <li><a class="dropdown-items menu-option" href="{{route('posts.create')}}">Create Post</a></li>
        <li><a class="dropdown-items menu-option" href="{{route('my.messenger.get')}}">Notification</a></li>
        <li><a class="dropdown-items menu-option" href="#">Premium</a></li>
        <li><a class="dropdown-items menu-option" href="#">Avtar</a></li>
    </div>
</div>

<div class="search-box-header">
                    @include('elements.search-box')
                </div>


<div class="popular-btn-header">
    <a href="{{'#'}}">
        <div class="d-flex justify-content-center align-items-center">
            <div class="icon-wrapper d-flex justify-content-center align-items-center position-relative">
                @include('elements.icon',['icon'=>'arrow-back-outline','variant'=>'medium'])
            </div>
            </div>
        </a>
</div>

<div class="redit-recap-btn-header">
    <a href="{{'#'}}">
    <div class="d-flex justify-content-center align-items-center">
        <div class="icon-wrapper d-flex justify-content-center align-items-center position-relative">
            <span class="material-symbols-outlined">shield</span>
        </div>
        </div>
    </a>
</div>


  <div class="massage-btn-header">
  <a href="{{route('my.messenger.get')}}" class="nav-link {{Route::currentRouteName() == 'my.messenger.get' ? 'active' : ''}} h-pill h-pill-primary d-flex justify-content-between">
                    <div class="d-flex justify-content-center align-items-center">
                        <div class="icon-wrapper d-flex justify-content-center align-items-center position-relative">
                            @include('elements.icon',['icon'=>'chatbubble-outline','variant'=>'medium'])
                            <div class="menu-notification-badge chat-menu-count {{(NotificationsHelper::getUnreadMessages() > 0) ? '' : 'd-none'}}">
                                {{NotificationsHelper::getUnreadMessages()}}
                            </div>
                        </div>
                   </div>
                </a>
            </div>

      
       <div class="header-notifaction-btn">     
<a href="{{route('my.notifications')}}" class="nav-link h-pill h-pill-primary {{Route::currentRouteName() == 'my.notifications' ? 'active' : ''}} d-flex justify-content-between">
                    <div class="d-flex justify-content-center align-items-center">
                        <div class="icon-wrapper d-flex justify-content-center align-items-center position-relative">
                            @include('elements.icon',['icon'=>'notifications-outline','variant'=>'medium'])
                            <div class="menu-notification-badge notifications-menu-count {{(isset($notificationsCountOverride) && $notificationsCountOverride->total > 0 ) || (NotificationsHelper::getUnreadNotifications()->total > 0) ? '' : 'd-none'}}">
                                {{!isset($notificationsCountOverride) ? NotificationsHelper::getUnreadNotifications()->total : $notificationsCountOverride->total}}
                            </div>
                        </div>
                        </div>
                </a>
                </div>

               



                <div class="header-create-post">
                    <a href="{{route('posts.create')}}">
                        <div class="d-flex justify-content-center align-items-center">
                            <div class="icon-wrapper d-flex justify-content-center align-items-center position-relative">
                                @include('elements.icon',['icon'=>'add-outline','variant'=>'medium'])
                            </div>
                            </div>
                    </a>
</div>


<div class="advertise-btn-header">                    
    <a href="{{'#'}}">
    <div class="d-flex justify-content-center align-items-center">
        <div class="icon-wrapper d-flex justify-content-center align-items-center position-relative">
            <span class="material-symbols-outlined">campaign</span>
        </div>
        </div>
</a></div>

<div class="dropdown-menu-header-second" aria-labelledby="dropdownMenuButton">
    <a id="navbarDropdown" class="nav-link dropdown-toggle text-right text-truncate d-flex align-items-center" href="#" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        <img src="{{Auth::user()->avatar}}" class="home-user-avatar"> <div class="text-truncate max-width-150">{{ Auth::user()->name }}</div> 
    </a>
    <div class="dropdown-menu" id="myDropdown">
    
                <a class="dropdown-item" href="{{route('feed')}}">
                    {{__('My Stuff')}}
                </a>
                <a class="dropdown-item" href="{{'#'}}">
                    {{__('Online Status')}}
                </a>
                <a class="dropdown-item" href="{{'#'}}">
                    {{__('Reddit Recap')}}
                </a>
                <a class="dropdown-item" href="{{route('profile',['username'=>Auth::user()->username])}}">
                    {{__('Profile')}}
                </a>
                <a class="dropdown-item" href="{{'#'}}">
                    {{__('Create Avtar')}}
                </a>
                <a class="dropdown-item" href="{{route('my.settings')}}">
                    {{__('User Settings')}}
                </a>
                <a class="scroll-link d-flex align-items-center dark-mode-switcher same-pd" href="#">
                    @if(Cookie::get('app_theme') == 'dark')
                        @include('elements.icon',['icon'=>'contrast-outline','variant'=>'medium','centered'=>false,'classes'=>'mr-2'])
                        {{__('Light mode')}}
                    @else
                        @include('elements.icon',['icon'=>'contrast','variant'=>'medium','centered'=>false,'classes'=>'mr-2'])
                        {{__('Dark mode')}}
                    @endif
                </a>
                <a class="dropdown-item" href="{{'#'}}">
                    {{__('Create a Community')}}
                </a>
                <a class="dropdown-item" href="{{'#'}}">
                    {{__('Advertise on Redit')}}
                </a>
                <a class="dropdown-item" href="{{'#'}}">
                    {{__('Premimum')}}
                </a>

                {{-- Accordian Start --}}
                <div class="accordion">
                    <div class="accordion-item">

                        {{-- First Accordian --}}
                        <div class="accordion-header" onclick="event.stopPropagation();">Explore</div>
                        <div class="accordion-content">
                            <!-- First Sub-Accordion -->
                            <div class="sub-accordion">
                                <div class="accordion-item">
                                    <div class="accordion-header" onclick="event.stopPropagation();">Gaming</div>
                                    <div class="accordion-content">
                                        <li><a href="">Valheim</a></li>
                                        <li><a href="">Genshin Impact</a></li>
                                        <li><a href="">Minecraft</a></li>
                                        <li><a href="">Pokimane</a></li>
                                        <li><a href="">Halo Infinite</a></li>
                                        <li><a href="">Call of Duty: Warzone</a></li>
                                        <li><a href="">Path of Exile</a></li>
                                        <li><a href="">Hollow Knight: Silksong</a></li>
                                        <li><a href="">Escape from Tarkov</a></li>
                                        <li><a href="">Watch Dogs: Legion</a></li>
                                    </div>
                                </div>
                            </div>
                
                            <!-- Second Sub-Accordion -->
                            <div class="sub-accordion">
                                <div class="accordion-item">
                                    <div class="accordion-header" onclick="event.stopPropagation();">Sports</div>
                                    <div class="accordion-content">
                                        <li><a href="">Valheim</a></li>
                                        <li><a href="">Genshin Impact</a></li>
                                        <li><a href="">Minecraft</a></li>
                                        <li><a href="">Pokimane</a></li>
                                        <li><a href="">Halo Infinite</a></li>
                                        <li><a href="">Call of Duty: Warzone</a></li>
                                        <li><a href="">Path of Exile</a></li>
                                        <li><a href="">Hollow Knight: Silksong</a></li>
                                        <li><a href="">Escape from Tarkov</a></li>
                                        <li><a href="">Watch Dogs: Legion</a></li>
                                    </div>
                                </div>
                            </div>

                            <!-- Third Sub-Accordion -->
                            <div class="sub-accordion">
                                <div class="accordion-item">
                                    <div class="accordion-header" onclick="event.stopPropagation();">Business, Economics, and Finance</div>
                                    <div class="accordion-content">
                                        <li><a href="">Sub-Option 1.1</a></li>
                                        <li><a href="">Sub-Option 1.2</a></li>
                                        <li><a href="">Sub-Option 1.3</a></li>
                                    </div>
                                </div>
                            </div>

                            <!-- Fourth Sub-Accordion -->
                            <div class="sub-accordion">
                                <div class="accordion-item">
                                    <div class="accordion-header" onclick="event.stopPropagation();">Crypto</div>
                                    <div class="accordion-content">
                                        <li><a href="">Sub-Option 1.1</a></li>
                                        <li><a href="">Sub-Option 1.2</a></li>
                                        <li><a href="">Sub-Option 1.3</a></li>
                                    </div>
                                </div>
                            </div>

                            <!-- Fifth Sub-Accordion -->
                            <div class="sub-accordion">
                                <div class="accordion-item">
                                    <div class="accordion-header" onclick="event.stopPropagation();">Television</div>
                                    <div class="accordion-content">
                                        <li><a href="">Sub-Option 1.1</a></li>
                                        <li><a href="">Sub-Option 1.2</a></li>
                                        <li><a href="">Sub-Option 1.3</a></li>
                                    </div>
                                </div>
                            </div>

                            <!-- Sixth Sub-Accordion -->
                            <div class="sub-accordion">
                                <div class="accordion-item">
                                    <div class="accordion-header" onclick="event.stopPropagation();">Celebrity</div>
                                    <div class="accordion-content">
                                        <li><a href="">Sub-Option 1.1</a></li>
                                        <li><a href="">Sub-Option 1.2</a></li>
                                        <li><a href="">Sub-Option 1.3</a></li>
                                    </div>
                                </div>
                            </div>

                            <!-- Seventh Sub-Accordion -->
                            <div class="sub-accordion">
                                <div class="accordion-item">
                                    <div class="accordion-header" onclick="event.stopPropagation();">More Topics</div>
                                    <div class="accordion-content">
                                        <li><a href="">Sub-Option 1.1</a></li>
                                        <li><a href="">Sub-Option 1.2</a></li>
                                        <li><a href="">Sub-Option 1.3</a></li>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="help-center-header-drp">
                    <a href="" class="same-pd">Help Center</a>    
                    </div>

                    {{-- Second Accordian --}}
                    <div class="accordion-item">
                        <div class="accordion-header" onclick="event.stopPropagation();">More</div>
                        <div class="accordion-content">
                          <li><a href="">Reddit IOS</a></li>
                          <li><a href="">Reddit Android</a></li>
                          <li><a href="">Rereddit</a></li>
                          <li><a href="">Best Communities</a></li>
                          <li><a href="">Communities</a></li>
                          <li><a href="">About Reddit</a></li>
                          <li><a href="">Blog</a></li>
                          <li><a href="">Careers</a></li>
                          <li><a href="">Press</a></li>
                          <li><a href="">Visit old Reddit</a></li>
                        </div>
                      </div>
                      <div class="about-us">
                          <a href="/pages/about-us" class="same-pd">About Us</a></li>
                      </div>
                      {{-- Third Accordian --}}
                      <div class="accordion-item">
                        <div class="accordion-header" onclick="event.stopPropagation();">Terms & Policies</div>
                        <div class="accordion-content">
                          <li><a href="/pages/terms-and-conditions">Terms & Condition</a></li>
                          <li><a href="/pages/privacy">Privacy Policy</a></li>
                          <li><a href="/pages/content-policy">Content Policy</a></li>
                          
                        </div>
                      </div>
                  </div>

                <a class="dropdown-item" href="{{ route('logout') }}"
                   onclick="event.preventDefault();
                                         document.getElementById('logout-form').submit();">
                    {{ __('Logout') }}
                </a>
                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                    @csrf
                </form>
            </div>
        </div>

</div>
</nav>