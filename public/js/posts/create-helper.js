/**
 * Post create (helper) component
 */
"use strict";
/* global app, Post, user, FileUpload, updateButtonState, launchToast, trans, redirect, trans_choice, mediaSettings, passesMinMaxPPVContentCreationLimits, getWebsiteFormattedAmount */

$(function () {
    $("#post-price").keypress(function(e) {
        if(e.which === 13) {
            PostCreate.savePostPrice();
        }
    });
});

var PostCreate = {
    // Paid post price
    postPrice : 0,
    isSavingRedirect: false,
    postNotifications: false,
    postReleaseDate: null,
    postExpireDate: null,

    /**
     * Toggles post notification state
     */
    togglePostNotifications: function(){
        let buttonIcon = '';
        if(PostCreate.postNotifications === true){
            PostCreate.postNotifications = false;
            buttonIcon = `<div class="d-flex justify-content-center align-items-center mr-1"><ion-icon class="icon-medium" name="notifications-off-outline"></ion-icon></div>`;
        }
        else{
            buttonIcon = `<div class="d-flex justify-content-center align-items-center mr-1"><ion-icon class="icon-medium" name="notifications-outline"></ion-icon></div>`;
            PostCreate.postNotifications = true;
        }
        $('.post-notification-icon').html(buttonIcon);
    },

    /**
     * Shows up the post price setter dialog
     */
    showSetPricePostDialog: function(){
        $('#post-set-price-dialog').modal('show');
    },

    /**
     * Saves the post price into the state
     */
    savePostPrice: function(){
        PostCreate.postPrice = $('#post-price').val();
        let hasError = false;
        if(!passesMinMaxPPVContentCreationLimits(PostCreate.postPrice)){
            hasError = 'min';
        }
        if(PostCreate.postExpireDate !== null){
            hasError = 'ppv';
        }
        if(hasError){
            $('.post-price-error').addClass('d-none');
            $('#post-set-price-dialog .'+hasError+'-error').removeClass('d-none');
            $('#post-price').addClass('is-invalid');
            return false;
        }
        $('.post-price-label').html('('+getWebsiteFormattedAmount(PostCreate.postPrice)+')');
        $('#post-set-price-dialog').modal('hide');
        $('#post-price').removeClass('is-invalid');
    },
    /**
     * Clears up post price
     */
    clearPostPrice: function(){
        PostCreate.postPrice = 0;
        $('#post-price').val(0);
        $('.post-price-label').html('');
        $('#post-set-price-dialog').modal('hide');
        $('#post-price').removeClass('is-invalid');
    },

    /**
     * Initiates the post draft data, if available
     * @param data
     * @param type
     */
    initPostDraft: function(data, type = 'draft'){
        Post.initialDraftData = Post.draftData;
        if(data){
            Post.draftData = data;
            if(type === 'draft'){
                FileUpload.attachaments = data.attachments;
            }
            else{
                data.attachments.map(function (item) {
                    FileUpload.attachaments.push({attachmentID: item.id, path: item.path, type:item.attachmentType, thumbnail:item.thumbnail});
                });
            }
            $('#dropzone-uploader').val(Post.draftData.text);
        }
    },

    /**
     * Clears up post draft data
     */
    clearDraft: function(){
        // Clearing attachments from the backend
        Post.draftData.attachments.map(function (value) {
            FileUpload.removeAttachment(value.attachmentID);
        });
        // Removing previews
        $('.dropzone-previews .dz-preview ').each(function (index, item) {
            $(item).remove();
        });
        // Clearing Fileupload class attachments
        FileUpload.attachaments = [];
        // Clearing up the local storage object
        PostCreate.clearDraftData();
        // Clearing up the text area value
    },

    /**
     * Saves post draft data
     */
    saveDraftData: function(){
        Post.draftData.attachments = FileUpload.attachaments;
        Post.draftData.text = $('#dropzone-uploader').val();
        localStorage.setItem('draftData', JSON.stringify(Post.draftData));
    },

    /**
     * Clears up draft data
     * @param callback
     */
    clearDraftData: function(callback = null){
        localStorage.removeItem('draftData');
        Post.draftData = Post.initialDraftData;
        if(callback !== null){
            callback;
        }
        $('#dropzone-uploader').val(Post.draftData.text);
    },


    /**
     * Populates create/edit post form with draft data
     * @returns {boolean|any}
     */
    populateDraftData: function(){
        const draftData = localStorage.getItem('draftData');
        if(draftData){
            return JSON.parse(draftData);
        }
        else{
            return false;
        }
    },

    /**
     * Save new / update post
     * @param type
     * @param postID
     */
    save: function (type = 'create', postID = false, forceSave = false) {
        if(FileUpload.isLoading === true && forceSave === false){
            $('.confirm-post-save').unbind('click');
            $('.confirm-post-save').on('click',function () {
                PostCreate.save(type, postID, true);
            });
            $('#confirm-post-save').modal('show');
            return false;
        }
        updateButtonState('loading',$('.post-create-button'));
        PostCreate.savePostScheduleSettings();
        let route = app.baseUrl + '/posts/save';
        let data = {
            'attachments': FileUpload.attachaments,
            'text': $('#dropzone-uploader').val(),
            'price': PostCreate.postPrice,
            'postNotifications' : PostCreate.postNotifications,
            'postReleaseDate': PostCreate.postReleaseDate,
            'postExpireDate': PostCreate.postExpireDate
        };
        if(type === 'create'){
            data.type = 'create';
        }
        else{
            data.type = 'update';
            data.id = postID;
        }
        $.ajax({
            type: 'POST',
            data: data,
            url: route,
            success: function () {
                if(type === 'create'){
                    PostCreate.isSavingRedirect = true;
                    PostCreate.clearDraftData(redirect(app.baseUrl+'/'+user.username));
                }
                else{
                    redirect(app.baseUrl+'/posts/'+postID+'/'+user.username);
                }
                updateButtonState('loaded',$('.post-create-button'), trans('Save'));
                $('#confirm-post-save').modal('hide');
            },
            error: function (result) {
                if(result.status === 422 || result.status === 500) {
                    $.each(result.responseJSON.errors, function (field, error) {
                        if (field === 'text') {
                            $('.post-invalid-feedback').html(trans_choice('Your post must contain more than 10 characters.',mediaSettings.max_post_description_size, {'num':mediaSettings.max_post_description_size}));
                            $('#dropzone-uploader').addClass('is-invalid');
                            $('#dropzone-uploader').focus();
                        }
                        if (field === 'attachments') {
                            $('.post-invalid-feedback').html(trans('Your post must contain at least one attachment.'));
                            $('#dropzone-uploader').addClass('is-invalid');
                            $('#dropzone-uploader').focus();
                        }

                        if(field === 'permissions'){
                            launchToast('danger',trans('Error'),error);
                        }
                    });
                }
                else if(result.status === 403){
                    launchToast('danger',trans('Error'),'Post not found.');
                }
                $('#confirm-post-save').modal('hide');
                updateButtonState('loaded',$('.post-create-button'), trans('Save'));
            }
        });
    },

    /**
     * Shows up the post scheduling setting setter dialog
     */
    showPostScheduleDialog: function(){
        $('#post-set-schedule-dialog').modal('show');
    },

    /**
     * Saves the post post scheduling setting into the state
     */
    savePostScheduleSettings: function(){

        if(PostCreate.postPrice !== 0 && $('#post_expire_date').val().length > 0){
            $('#post_expire_date').addClass('is-invalid');
            return false;
        }

        PostCreate.postReleaseDate = $('#post_release_date').val();
        PostCreate.postExpireDate = $('#post_expire_date').val();
        $('#post-set-schedule-dialog').modal('hide');
        $('#post_expire_date').removeClass('is-invalid');

    },
    /**
     * Clears up post scheduling setting
     */
    clearPostScheduleSettings: function(){
        PostCreate.postReleaseDate = null;
        PostCreate.postExpireDate = null;
        $('#post_release_date').val('');
        $('#post_expire_date').val('');
        $('#post_expire_date').removeClass('is-invalid');
    },

};
