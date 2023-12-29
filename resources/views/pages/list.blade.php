@extends('layouts.user-no-nav')
@section('page_title', $list->name)

@section('styles')
    {!!
        Minify::stylesheet([
            '/css/pages/lists.css'
         ])->withFullUrl()
    !!}
@stop

@section('scripts')
    {!!
        Minify::javascript([
            '/js/pages/lists.js'
         ])->withFullUrl()
    !!}
@stop

@section('content')
    <div class="row">
        <div class="min-vh-100 border-right col-12 pr-md-0">
            <div class="pt-4 pl-4 px-3 d-flex justify-content-between pb-3 border-bottom">
                <h5 class="mb-0 text-truncate text-bold {{(Cookie::get('app_theme') == null ? (getSetting('site.default_user_theme') == 'dark' ? '' : 'text-dark-r') : (Cookie::get('app_theme') == 'dark' ? '' : 'text-dark-r'))}}">{{__($list->name)}}</h5>
                @if($list->isManageable)
                    <div class="mr-2">
                        <div class="dropdown {{GenericHelper::getSiteDirection() == 'rtl' ? 'dropright' : 'dropleft'}}">
                            <a class="btn btn-outline-primary btn-sm dropdown-toggle px-3 mb-0" data-toggle="dropdown" href="#" role="button" aria-haspopup="true" aria-expanded="false">
                                @include('elements.icon',['icon'=>'ellipsis-horizontal-outline'])
                            </a>
                            <div class="dropdown-menu">
                                <!-- Dropdown menu links -->
                                <a class="dropdown-item" href="javascript:void(0);" onclick="Lists.showListEditDialog('edit')">{{__('Rename list')}}</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" onclick="Lists.showListClearConfirmation()">{{__('Clear list')}}</a>
                                <a class="dropdown-item" href="javascript:void(0);" onclick="Lists.showListDeleteConfirmation()">{{__('Delete list')}}</a>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <div class="mx-4 pt-2">
                <div class="list-wrapper mt-2">
                    @if(count($list->members))
                        <div class="row">
                            @foreach($list->members as $member)
                                <div class="col-12 col-md-6 col-xl-4">
                                    @include('elements.feed.suggestion-card',['profile' => $member, 'isListMode' => true, 'isListManageable' => ($list->type == \App\Model\UserList::FOLLOWERS_TYPE ? false : true)])
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="pl-0 pt-2">{{__('No profiles available')}}</p>
                    @endif
                </div>

            </div>

        </div>
    </div>
    @include('elements.lists.list-update-dialog',['mode'=>'edit'])
    @include('elements.lists.list-delete-dialog')
    @include('elements.lists.list-member-delete-dialog')
    @include('elements.lists.list-clear-dialog')
@stop
