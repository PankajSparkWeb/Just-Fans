<div class="feed-top-container">
    <div class="feed-top-container-inner">
    <div class="create-post-area d-flex d-flex feed-top-dfrt-sec bg-white">
        <a href="{{route('profile',['username'=>Auth::user()->username])}}">
            <div class="create-post-avtar"><img src="{{Auth::user()->avatar}}" class="home-user-avatar"></div>
        </a>
    <a href="{{route('posts.create')}}" class=" d-flex justify-content-between align-items-center create-post-area-go-to-create">
        
        <div class="create-post-input"><input type="text" placeholder="Create Post"></div>
        <div class="create-post-link-icon"><span class="material-symbols-outlined">
            attach_file
            </span></div></a>
    </div>
    <div class="tabs-and-layout d-flex justify-content-between bg-white align-items-center feed-top-dfrt-sec">
        <div class="option-categery d-flex justify-content-between">
        <div class="hot-posts">
            <a class="posts_top_nav {{ Route::currentRouteName() == 'feed.hotFeed' ? 'active' : '' }}" href="{{route('feed.hotFeed')}}">Explorer</a>
        </div>
        <div class="new-posts">
            <a class="posts_top_nav {{ Route::currentRouteName() == 'feed' ? 'active' : '' }}" href="{{route('feed')}}">My Interested</a>            
        </div>
        <div class="top-posts">
            
            <a class="posts_top_nav {{ Route::currentRouteName() == 'feed.followedPeople' ? 'active' : '' }}" href="{{route('feed.followedPeople')}}">People who followed</a>  
        </div>
        <div class="more-btn"></div>
    </div>

        <div class="post-layout">
            <div class="dropdown" onclick="toggleDropdownLayout()">
                <button class="dropbtn" id="defaultOption">
                    <span class="material-symbols-outlined">bottom_sheets</span>
                </button>
                <div class="dropdown-content">
                    <a href="#" onclick="selectOption('bottom_sheets', this)" class="layout">
                        <span class="material-symbols-outlined">bottom_sheets</span> Card
                    </a>
                    <a href="#" onclick="selectOption('density_medium', this)" class="layout">
                        <span class="material-symbols-outlined">density_medium</span> Classic
                    </a>
                    <a href="#" onclick="selectOption('density_small', this)" class="layout">
                        <span class="material-symbols-outlined">density_small</span> Compact    
                    </a>
                    
                </div>
            </div>

         
             
        </div>
    </div>
    </div>
</div>