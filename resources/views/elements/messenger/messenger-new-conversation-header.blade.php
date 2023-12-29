<div class="new-conversation-header d-none">
    <div class="details-holder border-bottom">
        <div class="d-flex justify-content-between px-1">
            <div class="flex-grow-1 pl-0 d-flex">
                <form id="userMessageForm" role="form" autocomplete="off" class="w-100">
                    <div class="mfv-errorBox"></div>
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    @if(!isset($user))
                        <div class="input-holder">
                            <select id="select-repo" name="receiverID" class="repositories form-control input-sm"  multiple="multiple" placeholder="{{__('To...')}}"></select>
                        </div>
                    @else
                        <input type="hidden" name="receiverID" value="{{$user->id}}">
                    @endif
                </form>

            </div>
            <div class=" pt-2 pr-0 d-flex justify-content-end pl-3 pr-2">
                <div class="d-flex justify-content-center align-items-center">
                    <a class="pointer-cursor new-conversation-toggle-all mr-2" data-toggle="tooltip" data-placement="bottom" title="{{__('Select all')}}" data-original-title="{{__('Select all')}}">
                        <div class="mt-0 h5">@include('elements.icon',['icon'=>'checkmark-done-outline','variant'=>'medium'])</div>
                    </a>
                    <a class="pointer-cursor new-conversation-close" data-toggle="tooltip" data-placement="bottom" title="{{__('Close')}}" data-original-title="{{__('Close')}}">
                        <div class="mt-0 h5">@include('elements.icon',['icon'=>'close-outline','variant'=>'medium'])</div>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
