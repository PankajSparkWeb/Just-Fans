<div class="modal fade" tabindex="-1" role="dialog" id="suggest-description-dialog">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{__('Suggest a description')}}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>{{__('Use AI to generate your description.')}}</p>
                <div class="input-group">
                    <textarea id="ai-request" rows="6" type="text" class="form-control" name="text" required  placeholder="{{__('Add a few words about what your suggestion should be about')}}"></textarea>
                    <span class="invalid-feedback" role="alert">
                        <strong>{{__("Description must be at least 5 characters.")}}</strong>
                    </span>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-white mb-0" onclick="AiSuggestions.clearSuggestion()">{{__('Clear')}}</button>
                </div>
                <div>
                    <button type="button" class="btn btn-secondary mb-0 suggest-description" onclick="AiSuggestions.suggestDescription()">{{__('Suggest')}}</button>
                    <button type="button" class="btn btn-primary mb-0" onclick="AiSuggestions.saveSuggestion()">{{__('Save')}}</button>
                </div>
            </div>
        </div>
    </div>
</div>
