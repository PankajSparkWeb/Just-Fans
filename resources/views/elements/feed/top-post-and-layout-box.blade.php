<div class="feed-top-container">
    <div class="create-post-area d-flex">
        <div class="create-post-avtar"><img src="{{Auth::user()->avatar}}" class="home-user-avatar"></div>
        <div class="create-post-input"><input type="text" placeholder="Create Post"></div>
        <div class="create-post-link-icon"><span class="material-symbols-outlined">
            attach_file
            </span></div>
    </div>
    <div class="tabs-and-layout d-flex">
        <div class="hot-posts">Hot</div>
        <div class="new-posts">New</div>
        <div class="top-posts">Top</div>
        <div class="more-btn"></div>

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