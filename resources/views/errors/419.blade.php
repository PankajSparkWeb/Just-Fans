@extends('layouts.NewHomeLayout')
@section('page_title', __('Page Expired'))

@section('content')
    <div class="container">
        <div class=" d-flex justify-content-center align-items-center min-vh-65" >
            <div class="error-container d-flex flex-column">
                <div class="d-flex justify-content-center align-items-center">
                    <img src="{{asset('/img/500.svg')}}">
                </div>
                <div class="text-center">
                    <h3 class="text-bold"> 419 | {{__('Page Expired')}}</h3>
                    <div class="d-flex justify-content-center mt-2">
                        <a href="{{route('home')}}" class="right">{{__('Go home')}} »</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
