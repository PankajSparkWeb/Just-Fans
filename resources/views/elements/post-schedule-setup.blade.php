<div class="modal fade" tabindex="-1" role="dialog" id="post-set-schedule-dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{__('Post scheduling')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="post_release_date">{{__('Post release date')}}</label>
                    <input type="datetime-local" class="form-control {{ $errors->has('location') ? 'is-invalid' : '' }}" id="post_release_date" name="post_release_date"  value="{{isset($release_date) ? $release_date : null}}" max="">
                    @if($errors->has('post_release_date'))
                        <span class="invalid-feedback" role="alert">
                    <strong>{{$errors->first('post_release_date')}}</strong>
                </span>
                    @endif
                </div>

                <div class="form-group">
                    <label for="post_expire_date">{{__('Post expire date')}}</label>
                    <input type="datetime-local" class="form-control {{ $errors->has('location') ? 'is-invalid' : '' }}" id="post_expire_date" name="post_expire_date" aria-describedby="emailHelp"  value="{{isset($expire_date) ? $expire_date : null}}" max="">
                        <span class="invalid-feedback" role="alert">
                            <strong>{{__('Posts having an expire date can not be price locked.')}}</strong>
                        </span>
                </div>

                <p class="mb-0">{{__("Scheduling takes place on server time")}}, {{config('app.timezone')}}.</p>


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-white"   onclick="PostCreate.clearPostScheduleSettings()">{{__('Clear')}}</button>
                <button type="button" class="btn btn-primary" onclick="PostCreate.savePostScheduleSettings()">{{__('Save')}}</button>
            </div>
        </div>
    </div>
</div>
