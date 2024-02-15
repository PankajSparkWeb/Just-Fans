@if(count($posts))
<div class="without-login-show posts">
    @foreach($posts as $post)          
        <div data-id="{{ $post->id }}">
                @include('elements.feed.post-box', ['is_visited' => $post->is_visited_post ? true : false])
        </div>
    @endforeach
</div>
    @include('elements.report-user-or-post',['reportStatuses' => ListsHelper::getReportTypes()])
    @include('elements.feed.post-delete-dialog')
    @include('elements.feed.post-list-management')
    @include('elements.photoswipe-container')
@else
    <div class="d-flex justify-content-center align-items-center">
        <div class="col-10">
            <img src="{{asset('/img/no-content-available.svg')}}">
        </div>
    </div>
    <div class="d-flex justify-content-center align-items-center">
        <h5 class="text-center mb-2 mt-2">{{__('No posts available')}}</h5>
    </div>
@endif
<script>
    $(document).ready(function() {
        var selectedPostId = "{{ Session::get('mypostId') }}";
        if (selectedPostId) {
            if (localStorage.getItem('pageReloaded')) {
                localStorage.removeItem('pageReloaded');
                sessionStorage.removeItem('mypostId');
                {{ Session::forget('mypostId') }}
            } else {
                localStorage.setItem('pageReloaded', 'true');
            }

            var selectedPostElement = $('[data-id="' + selectedPostId + '"]');
            if (selectedPostElement.length) {
                var scrollPosition = selectedPostElement.offset().top - 100;
                $('html, body').animate({
                    scrollTop: scrollPosition
                }, 1000);
            }
        }
    });
</script>