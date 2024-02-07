<div class="min-vh-100 col-12 border-right pr-md-0 profile-side-bar">
<div class="pofile-wrapper">
    <div class="side-bar-avtar-wraper">
       <div class="profile-cover-bg">
           <img class="card-img-top centered-and-cropped" src="{{$user->cover}}">
       </div> 
   </div>

  <div class="container justify-content-between align-items-center">
       <div class="z-index-3 avatar-holder profile-avtar-holder">
        <img src="{{ asset($user->avatar) }}" class="rounded-circle" alt="User Avatar">
       </div>
       <div>
           @if(!Auth::check() || Auth::user()->id !== $user->id)
               <div class="d-flex flex-row">
                   @if(Auth::check())
                       {{-- <div class="">
                       <span class="p-pill ml-2 pointer-cursor to-tooltip"
                             @if(!Auth::user()->email_verified_at && getSetting('site.enforce_email_validation'))
                             data-placement="top"
                             title="{{__('Please verify your account')}}"
                             @elseif(!\App\Providers\GenericHelperServiceProvider::creatorCanEarnMoney($user))
                             data-placement="top"
                             title="{{__('This creator cannot earn money yet')}}"
                             @else
                             data-placement="top"
                             title="{{__('Send a tip')}}"
                             data-toggle="modal"
                             data-target="#checkout-center"
                             data-type="tip"
                             data-first-name="{{Auth::user()->first_name}}"
                             data-last-name="{{Auth::user()->last_name}}"
                             data-billing-address="{{Auth::user()->billing_address}}"
                             data-country="{{Auth::user()->country}}"
                             data-city="{{Auth::user()->city}}"
                             data-state="{{Auth::user()->state}}"
                             data-postcode="{{Auth::user()->postcode}}"
                             data-available-credit="{{Auth::user()->wallet->total}}"
                             data-username="{{$user->username}}"
                             data-name="{{$user->name}}"
                             data-avatar="{{$user->avatar}}"
                             data-recipient-id="{{$user->id}}"
                             @endif
                       >
                        @include('elements.icon',['icon'=>'cash-outline'])
                       </span>
                       </div> --}}
                       
                       <span class="p-pill ml-2 pointer-cursor" data-toggle="tooltip" data-placement="top" title="{{__('Add to your lists')}}" onclick="Lists.showListAddModal();">
                        @include('elements.icon',['icon'=>'list-outline'])
                   </span>
                   @endif
                   @if(getSetting('profiles.allow_profile_qr_code'))
                       <div>
                           <span class="p-pill ml-2 pointer-cursor" data-toggle="tooltip" data-placement="top" title="{{__('Get profile QR code')}}" onclick="Profile.getProfileQRCode()">
                               @include('elements.icon',['icon'=>'qr-code-outline'])
                           </span>
                       </div>
                   @endif
                   {{-- <span class="p-pill ml-2 pointer-cursor" data-toggle="tooltip" data-placement="top" title="{{__('Copy profile link')}}" onclick="shareOrCopyLink()">
                        @include('elements.icon',['icon'=>'share-social-outline'])
                   </span>
               </div> --}}
           @else
               <div class="d-flex flex-row">
                       {{-- @if(getSetting('profiles.allow_profile_qr_code'))
                       <div>
                           <span class="p-pill ml-2 pointer-cursor" data-toggle="tooltip" data-placement="top" title="{{__('Get profile QR code')}}" onclick="Profile.getProfileQRCode()">
                               @include('elements.icon',['icon'=>'qr-code-outline'])
                           </span>
                       </div>
                   @endif --}}
                   <div>
                       {{-- <span class="p-pill ml-2 pointer-cursor" data-toggle="tooltip" data-placement="top" title="{{__('Copy profile link')}}" onclick="shareOrCopyLink()">
                           @include('elements.icon',['icon'=>'share-social-outline'])
                       </span> --}}
                   </div>
               </div>
           @endif
       </div>
   </div>
</div>


   <div class="container pt-2 pl-0 pr-0 profile-bottom-section">
       <div class="pt-2 pl-4 pr-4 profile-avtar-name">
           <h5 class="text-bold d-flex align-items-center">
               <span>{{$user->name}}</span>
               @if($user->email_verified_at && $user->birthdate && ($user->verification && $user->verification->status == 'verified'))
                   <span data-toggle="tooltip" data-placement="top" title="{{__('Verified user')}}">
                       @include('elements.icon',['icon'=>'checkmark-circle-outline','centered'=>true,'classes'=>'ml-1 text-primary'])
                   </span>
               @endif
               @if($hasActiveStream)
                   <span data-toggle="tooltip" data-placement="right" title="{{__('Live streaming')}}">
                   <div class="blob red ml-3"></div>
                   </span>
               @endif
           </h5> 
          {{-- <h6 class="text-muted">{{$user->username}}</h6> --}}
       </div> 
       
       @if(Auth::check() && Auth::user()->id === $user->id)
        <div class="following-follower">
            <div class="lists-wrapper mt-2">
                @if(count($lists) >= 2) <!-- Ensure there are at least two lists -->
                    @php $counter = 0; @endphp <!-- Initialize a counter -->
                    @foreach($lists as $key => $list)
                        @if($counter < 2) <!-- Check if we've displayed two lists -->
                            @include('elements.lists.list-box', ['list'=>$list, 'isLastItem' => (count($lists) == $key + 1)])
                            @php $counter++; @endphp <!-- Increment the counter -->
                        @endif
                    @endforeach
                @else
                    <p class="ml-4">{{__('No lists available')}}</p>
                @endif
            </div>
            
           </div>
        @else
        @endif

       @if(Auth::check() && Auth::user()->id === $user->id)
    <div class="mr-2 go-to-profile">
        <a href="{{route('my.settings')}}" class="p-pill p-pill-text ml-2 pointer-cursor">
            @include('elements.icon',['icon'=>'settings-outline', 'variant'=>'medium'])
        </a>
    </div>
@endif
        <div class="profile-bio-and-create d-flex my-profile-setting">
       <div class="pt-2 pb-2 pl-4 pr-4 profile-description-holder">
           <div class="description-content {{$user->bio && (strlen(trim(strip_tags(GenericHelper::parseProfileMarkdownBio($user->bio)))) >= 85 || substr_count($user->bio,"\r\n") > 1) &&  !getSetting('profiles.disable_profile_bio_excerpt') ? 'line-clamp-1' : ''}}">
               @if($user->bio)
                   @if(getSetting('profiles.allow_profile_bio_markdown'))
                       {!!  GenericHelper::parseProfileMarkdownBio($user->bio) !!}
                   @else
                       {{$user->bio}}
                   @endif
               @else
                   {{__('No description available.')}}
               @endif
           </div>
           @if($user->bio && (strlen(trim(strip_tags(GenericHelper::parseProfileMarkdownBio($user->bio)))) >= 85 || substr_count($user->bio,"\r\n") > 1) && !getSetting('profiles.disable_profile_bio_excerpt'))
               <span class="text-primary pointer-cursor" onclick="Profile.toggleFullDescription()">
                   <span class="label-more">{{__('More info')}}</span>
                   <span class="label-less d-none">{{__('Show less')}}</span>
               </span>
           @endif
       </div> 
      
       <div class="d-flex flex-column flex-md-row justify-content-md-between pb-2 pl-4 pr-4 mb-3 mt-1 porfile-second-description">

           <div class="d-flex align-items-center mr-2 text-truncate mb-0 mb-md-0">
               @include('elements.icon',['icon'=>'calendar-clear-outline','centered'=>false,'classes'=>'mr-1'])
               <div class="text-truncate ml-1">
                   {{ucfirst($user->created_at->translatedFormat('F d'))}}
               </div>
           </div>
           @if($user->location)
               <div class="d-flex align-items-center mr-2 text-truncate mb-0 mb-md-0">
                   @include('elements.icon',['icon'=>'location-outline','centered'=>false,'classes'=>'mr-1'])
                   <div class="text-truncate ml-1">
                       {{$user->location}}
                   </div>
               </div>
           @endif
           @if(!getSetting('profiles.disable_website_link_on_profile'))
               @if($user->website)
                   <div class="d-flex align-items-center mr-2 text-truncate mb-0 mb-md-0">
                       @include('elements.icon',['icon'=>'globe-outline','centered'=>false,'classes'=>'mr-1'])
                       <div class="text-truncate ml-1">
                           <a href="{{$user->website}}" target="_blank" rel="nofollow">
                               {{str_replace(['https://','http://','www.'],'',$user->website)}}
                           </a>
                       </div>
                   </div>
               @endif
           @endif
           @if(getSetting('profiles.allow_gender_pronouns'))
               @if($user->gender_pronoun)
                   <div class="d-flex align-items-center mr-2 text-truncate mb-0 mb-md-0">
                       @include('elements.icon',['icon'=>'male-female-outline','centered'=>false,'classes'=>'mr-1'])
                       <div class="text-truncate ml-1">
                           {{$user->gender_pronoun}}
                       </div>
                   </div>
               @endif
           @endif
        </div>
       </div>

       <div class="interest-count-percentage">        
            @php
            $users_interests = get_users_learned_posts_interests($user->id);
            @endphp        
            @if (count($users_interests) > 0)
                <ul class='user_interest_count'>
                    @foreach ($users_interests as $key => $interestCount)
                        <li>
                            <span>{{ $key }} ____ {{ $interestCount['total_posts'] }} ({{ $interestCount['percentage'] }})</span>
                        </li>
                    @endforeach
                </ul>
            @else
                <p>No user interests available.</p>
            @endif
        </div>


       @if(Auth::check() && Auth::user()->id === $user->id)
       <div class="new-post-profile-side">
       <a class="" href="{{ route('posts.create') }}">New Post</a>
    </div>
      @endif
       {{-- <div class="bg-separator border-top border-bottom"></div> --}}

       @include('elements.message-alert',['classes'=>'px-2 pt-4'])
       @if($user->paid_profile && (!getSetting('profiles.allow_users_enabling_open_profiles') || (getSetting('profiles.allow_users_enabling_open_profiles') && !$user->open_profile)))
           @if( (!Auth::check() || Auth::user()->id !== $user->id) && !$hasSub)
               <div class="p-4 subscription-holder subscribe-button">
                   <h6 class="font-weight-bold text-uppercase mb-3">{{__('Subscription')}}</h6>
                   @if(count($offer))
                       <h5 class="m-0 text-bold">{{__('Limited offer main label',['discount'=> round($offer['discountAmount']), 'days_remaining'=> $offer['daysRemaining'] ])}}</h5>
                       <small class="">{{__('Offer ends label',['date'=>$offer['expiresAt']->format('d M')])}}</small>
                   @endif
                   @if($hasSub)
                       <button class="btn btn-round btn-lg btn-primary btn-block mt-3 mb-2 text-center">
                           <span>{{__('Subscribed')}}</span>
                       </button>
                   @else

                       @if(Auth::check())
                           @if(!GenericHelper::isEmailEnforcedAndValidated())
                               <i>{{__('Your email address is not verified.')}} <a href="{{route('verification.notice')}}">{{__("Click here")}}</a> {{__("to re-send the confirmation email.")}}</i>
                           @endif
                       @endif

                       @include('elements.checkout.subscribe-button-30')
                       <div class="d-flex justify-content-between">
                           @if($user->profile_access_price_6_months || $user->profile_access_price_12_months)
                               <small>
                                   <div class="pointer-cursor d-flex align-items-center" onclick="Profile.toggleBundles()">
                                       <div class="label-more">{{__('Subscriptions bundles')}}</div>
                                       <div class="label-less d-none">{{__('Hide bundles')}}</div>
                                       <div class="ml-1 label-icon">
                                           @include('elements.icon',['icon'=>'chevron-down-outline','centered'=>false])
                                       </div>
                                   </div>
                               </small>
                           @endif
                           @if(count($offer))
                               <small class="">{{__('Regular price label',['currency'=> getSetting('payments.currency_code') ?? 'USD','amount'=>$user->offer->old_profile_access_price])}}</small>
                           @endif
                       </div>

                       @if($user->profile_access_price_6_months || $user->profile_access_price_12_months || $user->profile_access_price_3_months)
                           <div class="subscription-bundles d-none mt-4">
                               @if($user->profile_access_price_3_months)
                                   @include('elements.checkout.subscribe-button-90')
                               @endif

                               @if($user->profile_access_price_6_months)
                                   @include('elements.checkout.subscribe-button-182')
                               @endif

                               @if($user->profile_access_price_12_months)
                                   @include('elements.checkout.subscribe-button-365')
                               @endif

                           </div>
                       @endif
                   @endif
               </div>
               {{-- <div class="bg-separator border-top border-bottom"></div> --}}
           @endif
           
       @elseif(!Auth::check() || (Auth::check() && Auth::user()->id !== $user->id))
       <div class="follow-chat d-flex follow-chat-flex-wrapper">
           <div class="subscription-holder">
               @if(Auth::check())
                   <button class="btn btn-round btn-lg btn-primary btn-block mt-3 mb-0 manage-follow-button" onclick="Lists.manageFollowsAction('{{$user->id}}')">
                       <span class="manage-follows-text">{{\App\Providers\ListsHelperServiceProvider::getUserFollowingType($user->id, true)}}</span>
                   </button>
               @else
                   <button class="btn btn-round btn-lg btn-primary btn-block mt-3 mb-0 text-center"
                           data-toggle="modal"
                           data-target="#login-dialog"
                   >
                       <span class="">{{__('Follow')}}</span>
                   </button>
               @endif
           </div>
           <div class="follow-wrapper">
            @if($hasSub || $viewerHasChatAccess)
                <span class="p-pill ml-2 pointer-cursor" data-toggle="tooltip" data-placement="top" title="{{__('Send a message')}}" onclick="messenger.showNewMessageDialog()">
                    {{-- @include('elements.icon',['icon'=>'chatbubbles-outline']) --}}
                    chat
                </span>
            @else
                <span class="p-pill ml-2 pointer-cursor" data-toggle="tooltip" data-placement="top" title="{{__('DMs unavailable without subscription')}}">
                @include('elements.icon',['icon'=>'chatbubbles-outline'])
            </span>
            @endif
        </div>
    </div>
           {{-- <div class="bg-separator border-top border-bottom"></div> --}}
       @endif 
       
   </div>

   
</div>
{{-- <div class="col-md-8 col-lg-9 mb-5 mb-lg-0 min-vh-100 border-left border-right settings-content mt-1 mt-md-0 pl-md-0 pr-md-0">
    <div class="ml-3 d-none d-md-flex justify-content-between">
        <div>
            <h5 class="text-bold mt-0 mt-md-3 mb-0 {{(Cookie::get('app_theme') == null ? (getSetting('site.default_user_theme') == 'dark' ? '' : 'text-dark-r') : (Cookie::get('app_theme') == 'dark' ? '' : 'text-dark-r'))}}">{{ ucfirst(__($activeSettingsTab))}}</h5>
            <h6 class="mt-2 text-muted">{{__($currentSettingTab['heading'])}}</h6>
        </div>
       @include('elements.table-filter')
    </div>
    <hr class="{{in_array($activeSettingsTab, ['subscriptions','payments']) ? 'mb-0' : ''}} d-none d-md-block mt-2">
    <div class="{{in_array($activeSettingsTab, ['subscriptions','payments', 'referrals']) ? '' : 'px-4 px-md-3'}}">
        @include('elements.settings.setting-side-bar-profile-'.$activeSettingsTab)
    </div>
</div> --}}