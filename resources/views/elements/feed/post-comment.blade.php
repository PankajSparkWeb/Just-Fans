<div class="post-comment d-flex flex-row mb-3" data-commentID="{{ $comment->id }}">
    <div class="">
        <img class="rounded-circle" src="{{ $comment->author->avatar }}">
    </div>
    <div class="pl-3 w-100">
        <div class="d-flex flex-row justify-content-between">
            <div class="text-bold d-flex align-items-center"><a
                    href="{{ route('profile', ['username' => $comment->author->username]) }}"
                    class="text-dark-r">{{ $comment->author->username }}</a>
                @if (
                    $comment->author->email_verified_at &&
                        $comment->author->birthdate &&
                        ($comment->author->verification && $comment->author->verification->status == 'verified'))
                    <span class="ml-1" data-toggle="tooltip" data-placement="top" title="{{ __('Verified user') }}">
                        @include('elements.icon', [
                            'icon' => 'checkmark-circle-outline',
                            'centered' => true,
                            'classes' => 'ml-1 text-primary',
                        ])
                    </span>
                @endif
            </div>
            <div class="position-absolute separator">
                <div class="d-flex">

                    @if (Auth::user()->id == $comment->author->id)
                        <span class="ml-1 h-pill h-pill-primary rounded react-button" data-toggle="tooltip"
                            data-placement="top" title="{{ __('Delete') }}"
                            onclick="Post.showDeleteCommentDialog({{ $comment->post->id }},{{ $comment->id }})">
                            @include('elements.icon', ['icon' => 'trash-outline'])
                        </span>
                    @else
                    {{-- 
                        <span
                            class="h-pill h-pill-primary rounded react-button {{ PostsHelper::didUserReact($comment->reactions) ? 'active' : '' }}"
                            data-toggle="tooltip" data-placement="top" title="{{ __('Like') }}"
                            onclick="Post.reactTo('comment',{{ $comment->id }})">
                            @include('elements.icon', ['icon' => 'heart-outline'])
                        </span>
                        --}}
                    @endif
                </div>

            </div>
        </div>
        <div>
            @php
                $is_react_type = PostsHelper::didUserReact($comment->reactions, true);
            @endphp		

            <div class="comment-text-upvote d-flex">
                @if (Auth::check() )
                {{-- Start Upvote/downvote section --}}
                <div class="comment-upvote-downvote comment_upvote_downvote_section">                 
                    <div class="upvoteLink upvote-icon react-button {{ $is_react_type == 'like' ? 'active' : '' }}"  onclick="Post.reactToPost(this, 'comment',{{ $comment->id }}, 'like')">
                        <div class="upvoteLink">
                            <span class="material-symbols-outlined">shift</span>
                        </div>
                    </div>
                    <span class="ml-2-h">
                        <strong class="text-bold comment-reactions-label-count">{{ $comment->count_reactions }}</strong>
                    </span>
                    <div class="downvoteLink rotate-180 react-button {{ $is_react_type == 'dislike' ? 'active' : '' }}"  onclick="Post.reactToPost(this, 'comment',{{ $comment->id }}, 'dislike')">
                        <div class="downvoteLink">
                            <span class="material-symbols-outlined">shift</span>
                        </div>
                    </div>
                </div>
                @else
                <div class="comment-upvote-downvote comment_upvote_downvote_section">                 
                    <div class="upvoteLink upvote-icon">
                        <div class="upvoteLink">
                            <span class="material-symbols-outlined">shift</span>
                        </div>
                    </div>
                    <span class="ml-2-h">
                        <strong class="text-bold comment-reactions-label-count">{{ $comment->count_reactions }}</strong>
                    </span>
                    <div class="downvoteLink rotate-180">
                        <div class="downvoteLink">
                            <span class="material-symbols-outlined">shift</span>
                        </div>
                    </div>
                </div>
                @endif
                {{-- END Upvote/downvote section --}}
                <div class="comment-text-area">
                    <div class="text-break">{{ $comment->message }}</div>
                    <div class="d-flex text-muted">
                        <div>{{ $comment->created_at->format('g:i A') }}</div>
                       {{-- 
                        <div class="ml-2">
                            <span class="comment-reactions-label-count">{{ count($comment->reactions) }}</span>
                            <span
                                class="comment-reactions-label">{{ trans_choice('likes', count($comment->reactions)) }}</span>
                        </div>
                        --}}
                        <div class="ml-2">
                            <!--a href="javascript:void(0)"
                                onclick="Post.addReplyUser('{{ $comment->author->username }}', '{{ $comment->id }}')"
                                class="text-muted">{{ __('Reply') }}</a></div-->

                                <div class="ml-2">
                                    <a href="javascript:void(0)"
                                        onclick="Post.toggleReplyForm('{{ $comment->id }}', '{{ $comment->author->username }}')"
                                        class="text-muted reply-link"
                                    >
                                        {{ __('Reply') }}
                                    </a>
                                </div>

                                <div class="d-flex reply_form_section">
                                    <div class="reply-form" style="display: none;" data-comment-id="{{ $comment->id }}">
                                        @if (Auth::check())
                                            <hr>
                                            @include('elements.feed.post-new-comment', ['comment'=> $comment])                                            
                                        @endif
                                    </div>
                                </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
