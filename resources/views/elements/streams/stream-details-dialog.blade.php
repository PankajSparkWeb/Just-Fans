<div class="modal fade" tabindex="-1" role="dialog" id="stream-details-dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{__('How to stream')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="{{__('Close')}}">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">

                <p>{{__('Your stream server is online. In order to get going, follow the steps below:')}}</p>


                <div class="mt-3 inline-border-tabs">
                    <nav class="nav nav-pills nav-justified" role="tablist">
                        <a class="nav-link active"  data-toggle="tab" data-target="#nav-desktop" type="button" role="tab" aria-controls="nav-desktop" aria-selected="true">
                            <div class="d-flex align-items-center justify-content-center">
                                @include('elements.icon',['icon'=>'laptop-outline','variant'=>'small','classes'=>'mr-2'])
                                {{__("Desktop")}}
                            </div>
                        </a>
                        <a class="nav-link"  data-toggle="tab" data-target="#nav-mobile" type="button" role="tab" aria-controls="nav-mobile" aria-selected="true">
                            <div class="d-flex align-items-center justify-content-center">
                                @include('elements.icon',['icon'=>'phone-portrait-outline','variant'=>'small','classes'=>'mr-2'])
                                {{__("Mobile")}}
                            </div>
                        </a>
                    </nav>
                </div>
                <div class="tab-content" id="nav-tabContent">
                    <div class="tab-pane fade show active" id="nav-desktop" role="tabpanel">
                        <div class="mt-2">
                            <ol class="py-3">
                                <li class="mb-1">{{__('Download')}} <a href="https://obsproject.com/download" target="_blank">OBS</a> {{__('for desktop or mobile alternatives.')}}</li>
                                <li class="mb-1">{{__('Go to')}} <code>{{__("Settings > Stream")}}</code>. {{__('For')}} <code>{{__("Service")}}</code>, {{__('select')}} <code>{{__("Custom")}}</code>.</li>
                                <li class="mb-1">{{ucfirst(__('for the'))}} <code>{{__("Server & Stream key")}}</code>, {{__('use the values below.')}}</li>
                            </ol>
                            <div class="form-group row ">
                                <label for="colFormLabelSm" class="col-sm-3 col-form-label col-form-label-md">{{__('Stream url')}}</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control form-control-md" id="stream-url" placeholder="{{__('Stream url')}}">
                                </div>
                                <div class="col-sm-auto d-flex align-items-center justify-content-center">
                            <span class="h-pill h-pill-accent rounded mr-2" onclick="Streams.copyStreamData('url')">
                                @include('elements.icon',['icon'=>'copy-outline'])
                            </span>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label for="colFormLabelSm" class="col-sm-3 col-form-label col-form-label-md">{{__('Stream key')}}</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control form-control-md" id="stream-key" placeholder="{{__('Stream key')}}">
                                </div>
                                <div class="col-sm-auto d-flex align-items-center justify-content-center">
                            <span class="h-pill h-pill-accent rounded mr-2" onClick="Streams.copyStreamData('key');">
                                @include('elements.icon',['icon'=>'copy-outline'])
                            </span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="nav-mobile" role="tabpanel" >
                        <div class="mt-2">
                            <ol class="py-3">
                                <li class="mb-1">{{__('Download')}} {{__("Larix for")}} <a href="https://apps.apple.com/us/app/larix-broadcaster/id1042474385" target="_blank">iOS</a> {{__("or")}} <a href="https://play.google.com/store/apps/details?id=com.wmspanel.larix_broadcaster&hl=en&gl=US" target="_blank">Android</a>.</li>
                                <li class="mb-1">{{__('Go to')}} <code>{{__("Settings > Connection > New connection")}}</code>.</li>
                                <li class="mb-1">{{ucfirst(__('for the'))}} <code>URL</code>, {{__("use the following value")}}.</li>
                            </ol>
                            <div class="form-group row ">
                                <label for="colFormLabelSm" class="col-sm-3 col-form-label col-form-label-md">{{__('Stream url')}}</label>
                                <div class="col-sm-7">
                                    <input type="text" class="form-control form-control-md" id="stream-url-larix" placeholder="{{__('Stream url')}}">
                                </div>
                                <div class="col-sm-auto d-flex align-items-center justify-content-center">
                            <span class="h-pill h-pill-accent rounded mr-2" onclick="Streams.copyStreamData('mobile-url')">
                                @include('elements.icon',['icon'=>'copy-outline'])
                            </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" data-dismiss="modal">{{__('Got it')}}</button>
            </div>
        </div>
    </div>
</div>
