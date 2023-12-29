
@if($subscribersCount)
    <div class="mt-0 mt-md-3 mb-1 inline-border-tabs">
        <nav class="nav nav-pills nav-justified">
            @foreach(['subscriptions', 'subscribers'] as $tab)
                <a class="nav-item nav-link {{$activeSubsTab == $tab ? 'active' : ''}}" href="{{route('my.settings',['type' => 'subscriptions', 'active' => $tab])}}">

                    <div class="d-flex align-items-center justify-content-center">
                        @if($tab == 'subscriptions')
                            @include('elements.icon',['icon'=>'people','variant'=>'medium','classes'=>'mr-2'])
                        @else
                            @include('elements.icon',['icon'=>'logo-usd','variant'=>'medium','classes'=>'mr-2'])
                        @endif
                        {{ucfirst(__($tab))}}
                    </div>
                </a>
            @endforeach
        </nav>
    </div>
@endif

@if(count($subscriptions))
    <div class="table-wrapper">
        @include('elements/message-alert', ['classes' =>'p-2'])
        <div class="">
            <div class="col d-flex align-items-center py-3 border-bottom text-bold">
                <div class="col-3 col-md-3 text-truncate">{{$activeSubsTab == 'subscriptions' ? __('To') : __('From')}}</div>
                <div class="col-4 col-md-2 text-truncate">{{__('Status')}}</div>
                <div class="col-2 text-truncate d-none d-md-block">{{__('Paid with')}}</div>
                <div class="col-4 col-md-2 text-truncate">{{__('Renews')}}</div>
                <div class="col-2 text-truncate d-none d-md-block">{{__('Expires at')}}</div>
                <div class="col-1 text-truncate"></div>
            </div>
            @foreach($subscriptions as $subscription)
                <div class="col d-flex align-items-center py-3 border-bottom">
                    <div class="col-3 col-md-3 text-truncate">
                        <span class="mr-2">
                            <img src="{{$activeSubsTab == 'subscriptions' ? $subscription->creator->avatar : $subscription->subscriber->avatar}}" class="rounded-circle user-avatar" width="35">
                        </span>
                        <a href="{{route('profile',['username'=> $activeSubsTab == 'subscriptions' ? $subscription->creator->username : $subscription->subscriber->username])}}" class="text-dark-r">
                            {{$activeSubsTab == 'subscriptions' ? $subscription->creator->name : $subscription->subscriber->name}}
                        </a>
                    </div>
                    <div class="col-4 col-md-2">
                        @switch($subscription->status)
                            @case('pending')
                            @case('update-needed')
                            @case('canceled')
                            <span class="badge badge-warning">{{ucfirst(__($subscription->status))}}</span>
                            @break
                            @case('completed')
                            <span class="badge badge-success">{{ucfirst(__($subscription->status))}}</span>
                            @break
                            @case('suspended')
                            @case('expired')
                            @case('failed')
                            <span class="badge badge-danger">{{ucfirst(__($subscription->status))}}</span>
                            @break
                        @endswitch
                    </div>
                    <div class="col-2 text-truncate d-none d-md-block">{{ucfirst($subscription->provider)}}</div>
                    <div class="col-4 col-md-2 text-truncate text-center">{{isset($subscription->expires_at) ? ($subscription->status == \App\Model\Subscription::CANCELED_STATUS ? '-' : $subscription->expires_at->format('M d Y')) : '-'}}</div>
                    <div class="col-2 text-truncate d-none d-md-block text-center">{{isset($subscription->expires_at) ? ($subscription->status == \App\Model\Subscription::ACTIVE_STATUS ? '-' : $subscription->expires_at->format('M d Y')) : '-'}}</div>
                    <div class="col-1 text-center">
                        @if($subscription->status === \App\Model\Subscription::ACTIVE_STATUS)
                            <div class="dropdown {{GenericHelper::getSiteDirection() == 'rtl' ? 'dropright' : 'dropleft'}}">
                                <a class="btn btn-sm text-dark-r text-hover {{$subscription->status == 'canceled' ? 'disabled' : ''}} btn-outline-{{(Cookie::get('app_theme') == null ? (getSetting('site.default_user_theme') == 'dark' ? 'dark' : 'light') : (Cookie::get('app_theme') == 'dark' ? 'dark' : 'light'))}} dropdown-toggle m-0 py-1 px-2" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                                    @include('elements.icon',['icon'=>'ellipsis-horizontal-outline','centered'=>false])
                                </a>
                                <div class="dropdown-menu">
                                    <!-- Dropdown menu links -->
                                    <!-- TODO: Disable cancel url from UI if ccbill data link not present -->
                                    @if($subscription->status === \App\Model\Subscription::ACTIVE_STATUS && ($subscription->provider !== 'ccbill' || \App\Providers\SettingsServiceProvider::providedCCBillSubscriptionCancellingCredentials()))
                                        <a class="dropdown-item d-flex align-items-center" href="javascript:void(0)" onclick="SubscriptionsSettings.confirmSubCancelation({{$subscription->id}},{{$activeSubsTab == 'subscriptions' ? '"subscriptions"' : '"subscribers"'}})">
                                            @include('elements.icon',['icon'=>'trash-outline','centered'=>false,'classes'=>'mr-2']) {{__('Cancel subscription')}}
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
        <div class="d-flex flex-row-reverse mt-3 mr-4">
            {{ $subscriptions->withQueryString()->onEachSide(1)->links() }}
        </div>
        @else
            <div class="p-3">
                <p>{{__('There are no active or cancelled subscriptions at the moment.')}}</p>
            </div>
@endif

@include('elements.settings.transaction-cancel-dialog')
