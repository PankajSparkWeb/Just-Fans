<div class="px-3 new-post-comment-area">
    <div class="d-flex justify-content-center align-items-center comment-text-area-section">
        <img class="rounded-circle" src="{{Auth::user()->avatar}}">
        <div class="input-group">
            <textarea   name="message" class="post-textarea-field form-control comment-textarea mx-3 comment-text text-editor-input textarea-comment-area" placeholder="{{__('Write a message..')}}"  onkeyup="textAreaAdjust(this)" rows="1" spellcheck="false" placeholder="Title"></textarea>
            
            {{-- @include('elements.post-textarea.script-comment') --}}
            <div class="input-group-append z-index-3 d-flex align-items-center justify-content-center space-box-mini">
                <span class="h-pill h-pill-primary rounded mr-3 trigger" data-toggle="tooltip" data-placement="top" title="Like" ></span>
            </div>
            <span class="invalid-feedback pl-4 text-bold" role="alert"></span>
        </div>
      
        <div class="pl-2">
            <button class="btn btn-outline-primary btn-rounded-icon addNewCommentBtn" onclick="Post.addComment({{isset($post)  ? $post->id : $comment->post_id}}, this)">
                <div class="d-flex justify-content-center align-items-center">
                    @include('elements.icon',['icon'=>'paper-plane','variant'=>''])
                </div>
            </button>
        </div>
    </div>
</div>