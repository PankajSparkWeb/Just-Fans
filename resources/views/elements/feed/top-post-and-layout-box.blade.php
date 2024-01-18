<style>
    a.posts_top_nav.active {
    background: green;
}
</style>
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
            <a class="posts_top_nav {{ Route::currentRouteName() == 'feed.hotFeed' ? 'active' : '' }}" href="{{route('feed.hotFeed')}}">Hot</a>
        </div>
        <div class="new-posts">
            <a class="posts_top_nav {{ Route::currentRouteName() == 'feed' ? 'active' : '' }}" href="{{route('feed')}}">New</a>            
        </div>
        <div class="top-posts">
            <a href="">Top</a>
        </div>
        <div class="more-btn"></div>
    </div>

        <div class="post-layout">
            <div class="dropDownFor-layout">
                <!-- The dropdown button -->
                <button class="dropDownFor-btn" onclick="toggleDropdown()">
                    <span class="material-symbols-outlined">view_agenda</span>
                </button>
                
                <!-- The dropdown content/options -->
                <div class="dropDownFor-content" id="myDropdown">
                    <a href="#" onclick="selectOption('view_agenda')">
                        <span class="material-symbols-outlined">view_agenda</span>
                    </a>
                    <a href="#" onclick="selectOption('table_rows')">
                        <span class="material-symbols-outlined">table_rows</span>
                    </a>
                    <a href="#" onclick="selectOption('view_headline')">
                        <span class="material-symbols-outlined">view_headline</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    </div>
</div>