/**
 * Ai Suggestions (helper) component
 */
"use strict";
/* global app, updateButtonState, launchToast, trans, ProfileSettings */

var AiSuggestions = {
    targetedClass: null,
    suggestionType: null,

    initAISuggestions: function(selector, type){
        this.setTargetedClass(selector);
        this.setSuggestionType(type);
        AiSuggestions.setDefaultDescription(type);
    },

    /**
     * Shows up the ai suggestion dialog
     */
    suggestDescriptionDialog: function(){
        $('#suggest-description-dialog').modal('show');
    },

    /**
     * Set targeted html class to update the text to when saving a suggestion
     * @param className
     */
    setTargetedClass(className) {
        this.targetedClass = className;
    },

    /**
     * Sets suggestion type
     * @param type
     */
    setSuggestionType(type) {
        this.suggestionType = type;
    },

    /**
     * Saves the post description suggestion
     */
    saveSuggestion: function(){
        let description = $('#ai-request').val();
        let validDescription = this.validateDescription();
        if(validDescription) {
            $('#ai-request').removeClass('is-invalid');
            $('#suggest-description-dialog').modal('hide');
            if(this.suggestionType == 'profile'){
                ProfileSettings.mdeEditor.value(description);
            }
            else{
                $(this.targetedClass).val(description);
            }
            $('#ai-request').removeClass('is-invalid');
        }
    },
    /**
     * Clears up post description suggestion
     */
    clearSuggestion: function(){
        $('#ai-request').val('');
    },

    /**
     * Generates a suggestion
     */
    suggestDescription: function () {
        let validDescription = this.validateDescription();
        if(validDescription) {
            $('#ai-request').removeClass('is-invalid');
            updateButtonState('loading',$('.suggest-description'), trans('Suggest'), 'light');
            let route = app.baseUrl + '/suggestions/generate';
            let data = {
                'text': $('#ai-request').val(),
            };
            $.ajax({
                type: 'POST',
                data: data,
                url: route,
                success: function (response) {
                    if(response.message) {
                        $('#ai-request').val(response.message);
                    }
                    updateButtonState('loaded',$('.suggest-description'), trans('Suggest'));
                },
                error: function (result) {
                    if(result.status === 422 || result.status === 500) {
                        launchToast('danger',trans('Error'),result.responseJSON.message);
                    }
                    else if(result.status === 403){
                        launchToast('danger',trans('Error'),'Something went wrong, please try again');
                    }
                    updateButtonState('loaded',$('.suggest-description'), trans('Suggest'));
                }
            });
        }
    },

    /**
     * Validate description length before saving / making another suggestion call
     * @returns {boolean}
     */
    validateDescription: function () {
        let description = $('#ai-request').val();
        if(description.length < 5){
            $('#ai-request').addClass('is-invalid');
            return false;
        }
        return true;
    },

    /**
     * sets default description
     */
    setDefaultDescription: function (type) {
        let description;
        if(type == 'post') {
            description = trans('Write me a short post description to post on my profile');
        } else if(type == 'profile') {
            description = trans('Write me a short profile bio description');
        }
        if(description) {
            $('#ai-request').val(description);
        }
    }
};
