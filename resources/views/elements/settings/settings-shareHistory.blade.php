<div class="form-group ">    
    <div class="card py-3 px-3">
        <div class="container">
            <h1>Share History</h1>

            @if($shareHistory->isEmpty())
                <p>No history records found.</p>
            @else
                <ul>
                    @foreach($shareHistory as $history)
                        @php 
                        $post = $history->post;
                        @endphp
                       <div class="post-visit_history"
                       onclick="window.location.href = '{{ Route::currentRouteName() != 'posts.get' ? route('posts.get', ['post_id' => $post->id, 'username' => $post->user->username]) : '#comments' }}';">
                           <div class="min-vh-100 col-12 border-right  pr-md-0">
                               <div class="feed-box mt-0 pt-4 mb-3 posts-wrapper">                               
                                   @include('elements.feed.post-box')                
                               </div>
                           </div>      
                       </div>          
                    @endforeach
                </ul>    
                {{ $shareHistory->links() }} <!-- Pagination links -->
            @endif
        </div>
    </div>
</div>
