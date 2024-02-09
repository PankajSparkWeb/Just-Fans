@extends('layouts.NewHomeLayout')

@section('page_title', __($page->title))
@section('share_url', route('home'))
@section('share_title', getSetting('site.name') . ' - ' . getSetting('site.slogan'))
@section('share_description', getSetting('site.description'))
@section('share_type', 'article')
@section('share_img', GenericHelper::getOGMetaImage())

@section('content')
    <div class="container pt-5">
        <div class="page-content-wrapper pb-5">
            <div class="row">
                <div class="col-12">
                    <div class="mt-1 mb-5 text-center">
                        <h1 class=" text-bold">{{$page->title}}</h1>
                        @if(in_array($page->slug,['help','privacy','terms-and-conditions', 'about-us', 'content-policy']))
                            <p class="text-muted mb-0 mt-2">{{__("Last updated")}}: {{$page->updated_at->format('Y-m-d')}}</p>
                        @endif
                    </div>
                </div>
                <div class="col-12">
                    <div class="d-flex justify-content-center">
                        <div class="col-8">
                            {!! $page->content  !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
