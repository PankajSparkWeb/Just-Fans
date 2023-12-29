<div class="d-flex flex-column flex-md-row">
    <div class="mt-1">
        <span
            data-toggle="tooltip" data-placement="bottom" title="{{__('Add files')}}."
            class="h-pill h-pill-primary file-upload-button {{!GenericHelper::isUserVerified() && getSetting('site.enforce_user_identity_checks') ? 'disabled' : ''}}"
        >
            @include('elements.icon',['icon'=>'document-outline','variant'=>'medium','centered'=>true, 'classes' => 'mr-1'])
            <span class="">{{__("Files")}}</span>
        </span>
    </div>
    <div class="mt-1 ml-0 ml-md-2">
        <span
            class="h-pill h-pill-primary post-price-button {{!GenericHelper::isUserVerified() && getSetting('site.enforce_user_identity_checks') ? 'disabled' : ''}}"
            onclick="{{!GenericHelper::isUserVerified() && getSetting('site.enforce_user_identity_checks') ? '' : 'PostCreate.showSetPricePostDialog()'}}"
            data-toggle="tooltip" data-placement="bottom" title="{{__('Set post price')}}."
        >
            @include('elements.icon',['icon'=>'logo-usd','variant'=>'medium','centered'=>true, 'classes' => 'mr-1'])
            <span class="d-none d-md-block">{{__("Price")}}</span>
            <span class="d-block d-md-none">{{__("Price")}}</span>
            <span class="post-price-label ml-1">{{(isset($post) && $post) > 0 ? "(".\App\Providers\SettingsServiceProvider::getWebsiteFormattedAmount($post->price).")" : ''}}</span>
        </span>
    </div>
    @if(getSetting('profiles.enable_new_post_notification_setting'))
        <div class="d-none"><ion-icon name="notifications-outline"></ion-icon></div>
        <div class="mt-1 ml-0 ml-md-2">
            <span data-toggle="tooltip" data-placement="bottom" title="{{__('If enabled, your followers will receive an email notification.')}}" class="h-pill h-pill-primary post-notification-button {{!GenericHelper::isUserVerified() && getSetting('site.enforce_user_identity_checks') ? 'disabled' : ''}}" onclick="{{!GenericHelper::isUserVerified() && getSetting('site.enforce_user_identity_checks') ? '' : 'PostCreate.togglePostNotifications()'}}">
               <div class="post-notification-icon">
                @include('elements.icon',['icon'=>'notifications-off-outline','variant'=>'medium','centered'=>true, 'classes' => 'mr-1'])
               </div>
                <span class="d-none d-md-block">{{__("Notifications")}}</span>
                <span class="d-block d-md-none">{{__("Notify")}}</span>
            </span>
        </div>
    @endif
    @if(getSetting('ai.open_ai_enabled'))
        @include('elements.suggest-description')
        <div class="mt-1 ml-0 ml-md-2">
            <span class="h-pill h-pill-primary" data-toggle="tooltip" data-placement="bottom" title="{{__('Use AI to generate your description.')}}" onclick="{{"AiSuggestions.suggestDescriptionDialog();"}}">
                @include('elements.icon',['icon'=>'hardware-chip-outline','variant'=>'medium','centered'=>true, 'classes' => 'mr-1'])
                <span class="d-none d-md-block">{{trans_choice("Suggestion",2)}}</span>
                <span class="d-block d-md-none">{{trans_choice("Suggestion",2)}}</span>
            </span>
        </div>
    @endif
    @if(getSetting('feed.allow_post_scheduling'))
        <div class="mt-1 ml-0 ml-md-2">
            <span class="h-pill h-pill-primary" data-toggle="tooltip" data-placement="bottom" title="{{__('Schedule your post release or deletion date.')}}" onclick="{{"PostCreate.showPostScheduleDialog();"}}">
                @include('elements.icon',['icon'=>'alarm-outline','variant'=>'medium','centered'=>true, 'classes' => 'mr-1'])
                <span class="">{{__("Scheduling")}}</span>
            </span>
        </div>
    @endif
</div>

