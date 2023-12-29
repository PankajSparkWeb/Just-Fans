@if(Session::get('impersonated'))
    <div class="content text-center block bg-gradient-faded-primary text-dark py-1">
        <span class="font-weight-bold text-white">{{__("You're logged as")}}: {{\Illuminate\Support\Facades\Auth::user()->name}}</span>
        <a href="{{route('admin.leaveImpersonation')}}" class="font-weight-light text-white">{{__('Leave impersonation')}}</a>
    </div>
@endif
