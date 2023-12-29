@if(getSetting('social-login.facebook_client_id') || getSetting('social-login.twitter_client_id') || getSetting('social-login.google_client_id'))
    <div class="strike mt-2">
        <span>{{__("Or use social")}}</span>
    </div>

    <div class="mt-4">
        @if(getSetting('social-login.facebook_client_id'))
            <div class="d-flex justify-content-center">
                <a href="{{url('',['socialAuth','facebook'])}}" rel="nofollow" class="btn btn-block btn-outline-primary btn-round">
                    <div class="d-flex align-items-center justify-content-center">
                        <div class="col-4 d-flex flex-row-reverse pr-0">
                            <img src="{{asset('/img/logos/facebook-logo.svg')}}" class="social-media-icon"/>
                        </div>
                        <div class="col-8 d-flex align-items-center flex-row ">
                            {{__("Sign in with")}} {{__("Facebook")}}
                        </div>
                    </div>
                </a>
            </div>
        @endif

        @if(getSetting('social-login.twitter_client_id'))
            <div class="d-flex justify-content-center">
                <a href="{{url('',['socialAuth','twitter'])}}" rel="nofollow" class="btn btn-block btn-outline-primary btn-round">
                    <div class="d-flex align-items-center justify-content-center">
                         <div class="col-4 d-flex flex-row-reverse pr-0">
                            <img src="{{asset('/img/logos/twitter-logo.svg')}}" class="social-media-icon"/>
                        </div>
                        <div class="col-8 d-flex align-items-center flex-row ">
                            {{__("Sign in with")}} {{__("Twitter")}}
                        </div>
                    </div>
                </a>
            </div>
        @endif

        @if(getSetting('social-login.google_client_id'))
            <div class="d-flex justify-content-center">
                <a href="{{url('',['socialAuth','google'])}}" rel="nofollow" class="btn btn-block btn-outline-primary btn-round">
                    <div class="d-flex align-items-center justify-content-center">
                         <div class="col-4 d-flex flex-row-reverse pr-0">
                            <img src="{{asset('/img/logos/google-logo.svg')}}" class="social-media-icon"/>
                        </div>
                        <div class="col-8 d-flex align-items-center flex-row ">
                            {{__("Sign in with")}} {{__("Google")}}
                        </div>
                    </div>
                </a>
            </div>
        @endif
    </div>
@endif
