<div class="form-group ">    
    <div class="card py-3 px-3">
        <div class="container post-hostory-conatiner">
            <h1 class='post-history-heading'>Learned Posts History</h1>

            @if($learnedHistory->isEmpty())
                <p>No learned posts available.</p>
            @else
                <ul class="post-history-ul">
                    @foreach($learnedHistory as $history)
                        @php 
                        $post = $history->post;
                        @endphp
                       <div class="post-visit_history"
                       onclick="window.location.href = '{{ Route::currentRouteName() != 'posts.get' ? route('posts.get', ['post_id' => $post->id, 'username' => $post->user->username]) : '#comments' }}';">
                           <div class="min-vh-100 col-12 border-right  pr-md-0 post-history-inner-wraper">
                               <div class="feed-box mt-0 pt-4 mb-3 posts-wrapper">                               
                                   @include('elements.feed.post-box')                
                               </div>
                           </div>      
                       </div>          
                    @endforeach
                </ul> 
                <div class="posts-pagination">   
                 {{ $learnedHistory->appends(['tab' => $activeTab])->links() }}
                </div>
            @endif
        </div>
    </div>
</div>