<div class="px-2 list-item">
    <a href="{{route('my.lists.show', ['list_id'=> $list->type !== \App\Model\UserList::FOLLOWERS_TYPE ? $list->id : 'followers'])}}" class="list-link d-flex flex-column pt-2 pb-2 pl-3 rounded">
        <div class="d-flex flex-row-no-rtl justify-content-between">
            <div>
                <h5 class="text-bold mb-1">{{__($list->name)}}</h5>
                <span class="text-muted text-bold">{{trans_choice('people',count($list->members),['number' => count($list->members)])}} - {{trans_choice('posts', $list->posts_count,['number' => $list->posts_count])}}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center pr-3 list-box-avatars-wrapper">
                @foreach($list->members->reverse()->slice(0,3) as $member)
                    <img src="{{$member->avatar}}" class="rounded-circle user-avatar">
                @endforeach
            </div>
        </div>
    </a>
</div>
@if(!$isLastItem)
    <hr class="my-2">
@endif
