@extends('layouts.NewHomeLayout')
@section('page_title', __('New post'))

@section('styles')
    {!! Minify::stylesheet(['/css/posts/post.css', '/libs/dropzone/dist/dropzone.css'])->withFullUrl() !!}
@stop

@section('scripts')
    {!! Minify::javascript([
        '/js/Post.js',
        '/js/posts/create-helper.js',
        '/js/suggestions.js',
        Route::currentRouteName() == 'posts.create' ? '/js/posts/create.js' : '/js/posts/edit.js',
        '/libs/dropzone/dist/dropzone.js',
        '/js/FileUpload.js',
    ])->withFullUrl() !!}
@stop

@section('content')

    <div class="row">
        <div class="container col-12">
            @include('elements.uploaded-file-preview-template')
            @include('elements.post-price-setup', ['postPrice' => isset($post) ? $post->price : 0])
            @include('elements.attachments-uploading-dialog')
            @include(
                'elements.post-schedule-setup',
                isset($post) ? ['release_date' => $post->release_date, 'expire_date' => $post->expire_date] : []
            )
            <div class="d-flex justify-content-between pt-4 pb-3 px-3 border-bottom">
                <h5
                    class="text-truncate text-bold  {{ Cookie::get('app_theme') == null ? (getSetting('site.default_user_theme') == 'dark' ? '' : 'text-dark-r') : (Cookie::get('app_theme') == 'dark' ? '' : 'text-dark-r') }}">
                    {{ Route::currentRouteName() == 'posts.create' ? __('New post') : __('Edit post') }}</h5>
            </div>
            @if (!PostsHelper::getDefaultPostStatus(Auth::user()->id))
                <div class="pl-3 pr-3 pt-3">
                    @include('elements.pending-posts-warning-box')
                </div>
            @endif
            <div class="pl-3 pr-3 pt-2">
                @if (!GenericHelper::isUserVerified() && getSetting('site.enforce_user_identity_checks'))
                    <div class="alert alert-warning text-white font-weight-bold mt-2 mb-0" role="alert">
                        {{ __('Before being able to publish an item, you need to complete your') }} <a class="text-white"
                            href="{{ route('my.settings', ['type' => 'verify']) }}">{{ __('profile verification') }}</a>.
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                <div class="d-flex flex-column-reverse ql-editor-section">
                    <div class="w-100 editor-wrapper-flex">
                        <div id="dropzone-uploader" name="input-text"
                            class="form-control border dropzone w-100 editor-for-txt ql-container ql-snow ql-editor ql-blank"
                            rows="1" spellcheck="false" placeholder="Title"></div>
                        @include('elements.post-textarea.script')
                        <br>
                        <input type='url' id="external_post_link" name="external_post_link"
                            class="form-control border w-100" value="{{ isset($post) ? $post->external_post_link : '' }}"
                            placeholder="URL">
                        @include('elements.post-textarea.select-intrest')
                        <span class="invalid-feedback" role="alert">
                            <strong
                                class="post-invalid-feedback">{{ __('Your post must contain more than 10 characters.') }}</strong>
                        </span>

                        <div class="d-flex justify-content-between w-100 mb-3 mt-3 post-create-wrapper">
                            {{-- @include('elements.post-create-actions') --}}
                            <div class="d-flex align-items-center justify-content-center">
                                @if (Route::currentRouteName() == 'posts.create')
                                    <div class="">
                                        {{-- <a href="#"
                                            class="draft-clear-button mr-3 mr-md-3">{{ __('Clear draft') }}</a> --}}
                                    </div>
                                @endif
                                @if (!GenericHelper::isUserVerified() && getSetting('site.enforce_user_identity_checks'))
                                    <button class="btn btn-outline-primary disabled mb-0 ">{{ __('Save') }}</button>
                                @else
                                    <button
                                        class="btn btn-outline-primary post-create-button mb-0 save-button-draft">{{ __('Save') }}</button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@stop
