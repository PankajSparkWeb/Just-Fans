@if(!Auth::user()->email_verified_at) @include('elements.resend-verification-email-box') @endif

@if(getSetting('ai.open_ai_enabled'))
    @include('elements.suggest-description')
@endif

<form method="POST" action="{{route('my.settings.profile.save',['type'=>'profile'])}}">
    @csrf
    @include('elements.dropzone-dummy-element')
    <div class="mb-4">
        <div class="">
            <div class="card profile-cover-bg">
                <img class="card-img-top centered-and-cropped" src="{{Auth::user()->cover}}">
                <div class="card-img-overlay d-flex justify-content-center align-items-center">
                    <div class="actions-holder d-none">

                        <div class="d-flex">
                        <span class="h-pill h-pill-accent pointer-cursor mr-1 upload-button test" data-toggle="tooltip" data-placement="top" title="{{__('Upload cover image')}}">
                             @include('elements.icon',['icon'=>'image','variant'=>'medium'])
                        </span>
                            <span class="h-pill h-pill-accent pointer-cursor" onclick="ProfileSettings.removeUserAsset('cover')" data-toggle="tooltip" data-placement="top" title="{{__('Remove cover image')}}">
                            @include('elements.icon',['icon'=>'close','variant'=>'medium'])
                        </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="container">
            <div class="card avatar-holder">
                <img class="card-img-top" src="{{Auth::user()->avatar}}">
                <div class="card-img-overlay d-flex justify-content-center align-items-center">
                    <div class="actions-holder d-none">
                        <div class="d-flex">
                        <span class="h-pill h-pill-accent pointer-cursor mr-1 upload-button" data-toggle="tooltip" data-placement="top" title="{{__('Upload avatar')}}">
                            @include('elements.icon',['icon'=>'image','variant'=>'medium'])
                        </span>
                            <span class="h-pill h-pill-accent pointer-cursor" onclick="ProfileSettings.removeUserAsset('avatar')" data-toggle="tooltip" data-placement="top" title="{{__('Remove avatar')}}">
                             @include('elements.icon',['icon'=>'close','variant'=>'medium'])
                        </span>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
    <button class="btn btn-primary btn-block rounded mr-0" type="submit">{{__('Save')}}</button>
</form>