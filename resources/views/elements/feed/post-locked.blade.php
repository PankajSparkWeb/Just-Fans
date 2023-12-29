<div class="px-3 px-md-0">
    <div class="row d-flex justify-content-center align-items-center">
        <div class="col-10">
            <div class="">
                <img src="{{asset('/img/post-locked.svg')}}">
            </div>
        </div>
    </div>
    @if($type == 'post')
        @if(getSetting('feed.show_attachments_counts_for_ppv'))
            {{--  Post attachments info --}}
            <div class="col-12 mb-3 n-mt-2">
                <div class="d-flex flex-row">
                    @foreach(PostsHelper::getAttachmentsTypesCount($post->attachments) as $type => $count)
                        @switch($type)
                            @case('image')
                            <div class="d-flex justify-content-center align-items-center mr-2">
                                @include('elements.icon',['icon'=>'images-outline','centered' => false,'variant'=>'small', 'classes' => 'mr-1']) {{$count}}
                            </div>
                            @break
                            @case('video')
                            <div class="d-flex justify-content-center align-items-center mr-2">
                                @include('elements.icon',['icon'=>'videocam-outline','centered' => false,'variant'=>'small', 'classes' => 'mr-1']) {{$count}}
                            </div>
                            @break
                            @case('audio')
                            <div class="d-flex justify-content-center align-items-center mr-2">
                                @include('elements.icon',['icon'=>'musical-notes-outline','centered' => false,'variant'=>'small', 'classes' => 'mr-1']) {{$count}}
                            </div>
                            @break
                        @endswitch
                    @endforeach
                </div>
            </div>
        @endif
        {{--  Sub button --}}
        <div class="col-12">
            <button class="btn btn-outline-primary btn-block to-tooltip {{(!GenericHelper::creatorCanEarnMoney($post->user)) ? 'disabled' : ''}}"
                    @if(Auth::check())
                    @if(!GenericHelper::creatorCanEarnMoney($post->user))
                    data-placement="top"
                    title="{{__('This creator cannot earn money yet')}}"
                    @else
                    data-toggle="modal"
                    data-target="#checkout-center"
                    data-type="post-unlock"
                    data-recipient-id="{{$post->user->id}}"
                    data-amount="{{$post->price}}"
                    data-first-name="{{Auth::user()->first_name}}"
                    data-last-name="{{Auth::user()->last_name}}"
                    data-billing-address="{{Auth::user()->billing_address}}"
                    data-country="{{Auth::user()->country}}"
                    data-city="{{Auth::user()->city}}"
                    data-state="{{Auth::user()->state}}"
                    data-postcode="{{Auth::user()->postcode}}"
                    data-available-credit="{{Auth::user()->wallet->total}}"
                    data-username="{{$post->user->username}}"
                    data-name="{{$post->user->name}}"
                    data-avatar="{{$post->user->avatar}}"
                    data-post-id="{{$post->id}}"
                    @endif
                    @else
                    data-toggle="modal"
                    data-target="#login-dialog"
                @endif
            >{{__('Unlock post for')}} {{\App\Providers\SettingsServiceProvider::getWebsiteFormattedAmount($post->price)}}</button>
        </div>
    @endif
</div>
