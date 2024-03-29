<div class="form-group ">
    <div class="card py-3 px-3">
        <div class="container post-hostory-conatiner">
            <h1 class='post-history-heading'>Hidden Posts</h1>

            @if($hiddenPosts->isEmpty())
                <p>No hidden posts found.</p>
            @else
                <ul class="post-history-ul">
                    @foreach($hiddenPosts as $postHide)
                        @php 
                        $post = $postHide->post;
                        @endphp
                           <div class="min-vh-100 col-12 border-right  pr-md-0 post-history-inner-wraper">
                               <div class="feed-box mt-0 pt-4 mb-3 posts-wrapper">                               
                                   @include('elements.feed.post-box')                
                               </div>
                           </div>          
                    @endforeach
                </ul> 
                <div class="posts-pagination">   
                 {{ $hiddenPosts->appends(['tab' => $activeTab])->links() }}
                </div>
            @endif
        </div>
    </div>
</div>