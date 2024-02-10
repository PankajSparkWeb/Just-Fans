/**
 * Main post class
 */
"use strict";
/* global Swiper, CommentsPaginator, PostsPaginator  */
/* global app */
/* global updateButtonState, redirect, trans, trans_choice, launchToast, mswpScanPage, showDialog, hideDialog, EmojiButton  */


var Post = {

    draftData:{
        text: "",
        attachments:[]
    },

    activePage: 'post',
    postID: null,
    commentID: null,

    /**
     * Sets the current active page
     * @param page
     */
    setActivePage: function(page){
        Post.activePage = page;
    },

    /**
     * Instantiates the media module for post(s)
     * @returns {*}
     */
    initPostsMediaModule: function () {
        return new Swiper(".post-box .mySwiper", {
            // slidesPerColumn:1,
            slidesPerView:'auto',
            pagination: {
                el: ".swiper-pagination",
                // type: "fraction",
                dynamicBullets: true,
            },
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
        });
    },

    /**
     * Initiates the gallery swiper module
     * @param gallerySelector
     */
    initGalleryModule: function (gallerySelector = false) {
        mswpScanPage(gallerySelector,'mswp');
    },
    addComment: function (postID, thisEle) {
        var comment_parent_id = '';
        var newCommentButton;  // Declare the variable outside the if block
        var postElement;  // Declare the variable outside the if block
    
        var replyForm = $(thisEle).closest('.reply-form');
        let postElement_sectoin = $('*[data-postID="'+postID+'"]');
        
        // Find the closest ancestor with class .reply-form .reply_form_section      
        if (replyForm.length > 0) {
            postElement = $(thisEle).closest('.reply-form');
            // .reply-form exists
            // Retrieve the data-comment-id attribute
            let commentId = replyForm.data('comment-id');
            newCommentButton = postElement.find('.new-post-comment-area').find('button.addNewCommentBtn');  
            comment_parent_id = commentId;            
            // Use commentId as needed...            
        } else {
            postElement = $(thisEle).closest('.new-post-comment-area');            
            // .reply-form does not exist
            newCommentButton = postElement.find('button.addNewCommentBtn');                        
        }
        updateButtonState('loading',newCommentButton);
        $.ajax({
            type: 'POST',
            data: {
                'message': postElement.find('.textarea-comment-area').val(),
                'post_id': postID,
                'comment_parent_id': comment_parent_id
            },
            url: app.baseUrl+'/posts/comments/add',
            success: function (result) {
                if(result.success){
                    // Clear the comment input box
                    postElement.find('.textarea-comment-area').val('');
                    
                    postElement.find('.textarea-comment-area').val();
    
                    launchToast('success',trans('Success'),trans('Comment added'));
                    postElement.find('.no-comments-label').addClass('d-none');
                    if( comment_parent_id ){                                                
                        //start
                        var findUlElement_c = $(thisEle).closest('.post-comment');
                        var findUlElement = findUlElement_c.next('.replies-list');                        
                        if (findUlElement.length > 0) {
                            // If ul.replies-list exists, prepend result.data to it
                            var liElement = $('<li>').append(result.data);
                            findUlElement.prepend(liElement).fadeIn('slow');
                        } else {
                            // If ul.replies-list doesn't exist, create a new ul.replies-list and prepend result.data to it
                            var ulElement = $('<ul>', { class: 'replies-list' }).append($('<li>').append(result.data));
                            findUlElement_c.after(ulElement).fadeIn('slow');
                        }                       
                    }else{
                        postElement_sectoin.find('.post-comments-wrapper').prepend(result.data).fadeIn('slow');
                    }
                    postElement.find('.textarea-comment-area').text()
                    const commentsCount = parseInt(postElement.find('.post-comments-label-count').html()) + 1;
                    postElement.find('.post-comments-label-count').html(commentsCount);
                    postElement.find('.post-comments-label').html(trans_choice('comments',commentsCount));
                    updateButtonState('loaded',newCommentButton);
    
                    $(thisEle).closest('.reply-form').hide();
                }
                else{
                    launchToast('danger',trans('Error'),result.errors[0]);
                    updateButtonState('loaded',newCommentButton);
                }
                newCommentButton.blur();
            },
            error: function (result) {
                postElement.find('.textarea-comment-area').addClass('is-invalid');  
                if(result.status === 422) {
                    $.each(result.responseJSON.errors,function (field,error) {
                        if(field === 'message'){
                            postElement.find('.textarea-comment-area').parent().find('.invalid-feedback').html(error);
                        }
                    });
                    updateButtonState('loaded',newCommentButton);
                }
                else if(result.status === 403 || result.status === 404){
                    launchToast('danger',trans('Error'), result.responseJSON.message);
                }
                newCommentButton.blur();
            }
        });
    },
    

    /**
     * Shows up post comment delete dialog confirmation dialog
     * @param postID
     * @param commentID
     */
    showDeleteCommentDialog: function(postID, commentID){
        showDialog('comment-delete-dialog');
        Post.commentID = commentID;
        Post.postID = postID;
    },

    /**
     * Deletes post comment
     */
    deleteComment: function(){
        let commentElement = $('*[data-commentID="'+Post.commentID+'"]');
        let postElement = $('*[data-postID="'+Post.postID+'"]');
        $.ajax({
            type: 'DELETE',
            data: {
                'id': Post.commentID
            },
            dataType: 'json',
            url: app.baseUrl+'/posts/comments/delete',
            success: function (result) {
                if(result.success){
                    commentElement.fadeOut("normal", function() {
                        $(this).remove();
                        if(postElement.find('.post-comment').length === 0){
                            postElement.find('.no-comments-label').removeClass('d-none');
                        }

                    });

                    const commentsCount = parseInt(postElement.find('.post-comments-label-count').html()) - 1;
                    postElement.find('.post-comments-label-count').html(commentsCount);
                    postElement.find('.post-comments-label').html(trans_choice('comments',commentsCount));

                    launchToast('success',trans('Success'),result.message);
                    hideDialog('comment-delete-dialog');
                }
                else{

                    launchToast('danger',trans('Error'),result.errors[0]);
                    $('#comment-delete-dialog').modal('hide');
                }
            },
            error: function (result) {
                launchToast('danger',trans('Error'),result.responseJSON.message);
                hideDialog('comment-delete-dialog');
            }
        });

    },

    /**
     * Toggle post comment area visibility
     * @param post_id
     */
    showPostComments: function(post_id){
        let postElement = $('*[data-postID="'+post_id+'"] .post-comments');        
        // No pagination needed - on feed
        if(typeof postVars === 'undefined'){
            CommentsPaginator.nextPageUrl = '';
        }

        if(CommentsPaginator.nextPageUrl === ''){
            CommentsPaginator.init(app.baseUrl+'/posts/comments',postElement.find('.post-comments-wrapper'));
        }

        const isHidden = postElement.hasClass('d-none');
        if(isHidden){
            if(!postElement.hasClass('latest-comments-loaded')){
                CommentsPaginator.loadResults(post_id,1000000000000000);
            }
            postElement.removeClass('d-none');
            postElement.addClass('latest-comments-loaded');
        }
        else{
            postElement.addClass('d-none');
        }

        Post.initEmojiPicker(post_id);

    },

    /**
     * Instantiates the emoji picker for any given post
     * @param post_id
     */
    initEmojiPicker: function(post_id){
        try{
            const button = document.querySelector('*[data-postID="'+post_id+'"] .trigger');
            const picker = new EmojiButton(
                {
                    position: 'top-end',
                    theme: app.theme,
                    autoHide: false,
                    rows: 4,
                    recentsCount: 16,
                    emojiSize: '1.3em',
                    showSearch: false,
                }
            );
            picker.on('emoji', emoji => {
                document.querySelector('input').value += emoji;
                $('*[data-postID="'+post_id+'"] .comment-textarea').val($('*[data-postID="'+post_id+'"] .comment-textarea').val() + emoji);

            });
            button.addEventListener('click', () => {
                picker.togglePicker(button);
            });
        }
        catch (e) {
            // Maybe avoid ending up in here entirely
            // console.error(e)
        }

    },
   
    /**
     * Add new reaction
     * Can be used for post or comment reactionn
     * @param type
     * @param id
     */
    reactTo: function (type,id) {
        let reactElement = null;
        let reactionsCountLabel = null;
        let reactionsLabel = null;
        if(type === 'post'){
            reactElement = $('*[data-postID="'+id+'"] .post-footer .react-button');
            reactionsCountLabel = $('*[data-postID="'+id+'"] .post-footer .post-reactions-label-count');
            reactionsLabel = $('*[data-postID="'+id+'"] .post-footer .post-reactions-label');
        }
        else{
            reactElement = $('*[data-commentID="'+id+'"] .react-button');
            reactionsCountLabel = $('*[data-commentID="'+id+'"] .comment-reactions-label-count');
            reactionsLabel = $('*[data-commentID="'+id+'"] .comment-reactions-label');
        }
        const didReact = reactElement.hasClass('active');
        if(didReact){
            reactElement.removeClass('active');
            reactElement.html(`<ion-icon name="heart-outline" class="icon-medium"></ion-icon>`);
        }
        else{
            reactElement.addClass('active');
            reactElement.html(`<ion-icon name="heart" class="icon-medium text-primary"></ion-icon>`);
        }
        $.ajax({
            type: 'POST',
            data: {
                'type': type,
                'action': (didReact === true ? 'remove' : 'add'),
                'id': id
            },
            dataType: 'json',
            url: app.baseUrl+'/posts/reaction',
            success: function (result) {
                if(result.success){
                    let count = parseInt(reactionsCountLabel.html());
                    if(didReact){
                        count--;
                    }
                    else{
                        count++;
                    }
                    reactionsCountLabel.html(count);
                    reactionsLabel.html(trans_choice('likes',count));
                    // launchToast('success',trans('Success'),result.message);
                }
                else{
                    launchToast('danger',trans('Error'),result.errors[0]);
                }
            },
            error: function (result) {
                launchToast('danger',trans('Error'),result.responseJSON.message);
            }
        });
    },

    /**
     * Add new reaction
     * Can be used for post or comment reactionn
     * @param type
     * @param id
     */
    reactToPost: function (thisEle, type,id, reaction_type='like') {
        let reactElement = null;
        let reactionsCountLabel = null;
        let reactionsLabel = null;
        var reaction_type_value = (reaction_type === 'dislike' ? 'remove' : 'add');
        if(type === 'post'){
            reactElement = $('*[data-postID="'+id+'"] .upvote_downvote_section .react-button');
            reactionsCountLabel = $('*[data-postID="'+id+'"] .upvote_downvote_section .post-reactions-label-count');
            reactionsLabel = $('*[data-postID="'+id+'"] .upvote_downvote_section .post-reactions-label');
        }
        else{
            reactElement = $('*[data-commentID="'+id+'"] .comment_upvote_downvote_section .react-button');
            reactionsCountLabel = $('*[data-commentID="'+id+'"] .comment_upvote_downvote_section .comment-reactions-label-count');
            reactionsLabel = $('*[data-commentID="'+id+'"] .comment-reactions-label');
        }
        const didReact = $(thisEle).hasClass('active');
        if(didReact){
            reaction_type_value = 'delete';
            reactElement.removeClass('active');
            // reactElement.html(`<ion-icon name="heart-outline" class="icon-medium"></ion-icon>`);
        }
        else{
            reactElement.removeClass('active');
            $(thisEle).addClass('active');
          //  reactElement.addClass('active');
           // reactElement.html(`<ion-icon name="heart" class="icon-medium text-primary"></ion-icon>`);
        }

        $.ajax({
            type: 'POST',
            data: {
                'type': type,
                'action': reaction_type_value,
                'id': id
            },
            dataType: 'json',
            url: app.baseUrl+'/posts/reaction',
            success: function (result) {
                if(result.success){
                    let count = parseInt(reactionsCountLabel.html());
                    if(didReact){
                        count--;
                    }
                    else{
                        count++;
                    }
                   // if(type === 'post'){
                        count = result.reaction_count;
                    //}
                    reactionsCountLabel.html(count);
                    reactionsLabel.html(trans_choice('likes',count));
                    // launchToast('success',trans('Success'),result.message);
                }
                else{
                    launchToast('danger',trans('Error'),result.errors[0]);
                }
            },
            error: function (result) {
                launchToast('danger',trans('Error'),result.responseJSON.message);
            }
        });
    },
    

    /**
     * Appends replied username to comment field
     * @param username
     */
    addReplyUser: function(username, comment_parent_id = null){        
        $('.new-post-comment-area textarea').val($('.new-post-comment-area textarea').val()+ ' @' +username+ ' ');
    },

    toggleReplyForm(commentId, username) {
        // Toggle the visibility of the reply form based on the comment ID
        var replyForm = document.querySelector('.reply-form[data-comment-id="' + commentId + '"]');

        if (replyForm) {
            // The reply form with data-comment-id attribute exists        
            if (replyForm.style.display === 'none' || replyForm.style.display === '') {
                replyForm.style.display = 'block';
            } else {
                replyForm.style.display = 'none';
            }        
            var newPostCommentArea = replyForm.querySelector('.new-post-comment-area textarea');        
            if (newPostCommentArea) {
                // The textarea with class .new-post-comment-area exists within the reply form
                // Use the value property to get or set the value of the textarea
                //newPostCommentArea.value += ' @' + username + ' ';
                newPostCommentArea.value += ' ';
            } else {
                // The textarea with class .new-post-comment-area does not exist within the reply form
                // Handle the case where the element is not found...
            }
        } else {
            // The reply form with data-comment-id attribute does not exist
            // Handle the case where the reply form is not found...
        }
           
    },

    /**
     * Shows up the post removal confirmation box
     * @param post_id
     */
    confirmPostRemoval: function (post_id) {
        Post.postID = post_id;
        $('#post-delete-dialog').modal('show');
    },

    /**
     * Removes user post
     */
    removePost: function(){
        let postElement = $('*[data-postID="'+Post.postID+'"]');
        $.ajax({
            type: 'DELETE',
            data: {
                'id': Post.postID
            },
            dataType: 'json',
            url: app.baseUrl+'/posts/delete',
            success: function (result) {
                if(result.success){
                    if(Post.activePage !== 'post'){
                        $('#post-delete-dialog').modal('hide');
                        postElement.fadeOut("normal", function() {
                            $(this).remove();
                        });
                    }
                    else{
                        if(document.referrer.indexOf('feed') > 0){
                            redirect(app.baseUrl + '/feed');
                        }
                        else{
                            redirect(document.referrer);
                        }
                    }
                    launchToast('success',trans('Success'),result.message);

                }
                else{
                    $('#post-delete-dialog').modal('hide');
                    launchToast('danger',trans('Error'),result.errors[0]);
                }
            },
            error: function (result) {
                launchToast('danger',trans('Error'),result.responseJSON.message);
            }
        });
    },

    /**
     * Adds or removes user bookmarks
     * @param id
     */
    togglePostBookmark: function (id) {
        let reactElement = $('*[data-postID="'+id+'"] .bookmark-button');
        const isBookmarked = reactElement.hasClass('is-active');
        $.ajax({
            type: 'POST',
            data: {
                'action': (isBookmarked === true ? 'remove' : 'add'),
                'id': id
            },
            dataType: 'json',
            url: app.baseUrl+'/posts/bookmark',
            success: function (result) {
                if(result.success){
                    if(isBookmarked){
                        reactElement.removeClass('is-active');
                        reactElement.html(trans('Bookmark this post'));
                    }
                    else{
                        reactElement.addClass('is-active');
                        reactElement.html(trans('Remove this bookmark'));
                    }

                    launchToast('success',trans('Success'),result.message);
                }
                else{
                    launchToast('danger',trans('Error'),result.errors[0]);
                }
            },
            error: function (result) {
                launchToast('danger',trans('Error'),result.responseJSON.message);
            }
        });
    },

    // hide post
    togglePostHide: function (id, type = 'hide') {
        let reactElement = $('*[data-postID="'+id+'"]');
        const isHidden = type == 'hide' ? true : false;
        $.ajax({
            type: 'POST',
            data: {                
                'id': id,
                'type': type,
            },
            dataType: 'json',
            url: app.baseUrl+'/posts/hide_unhide_posts',
            success: function (result) {
                if(result.success){
                    //if hide
                    if(isHidden){
                       // Hide post content
                        reactElement.find(".post-content").hide();
                        // Create the wrapper div with both the anchor tag and span inside
                        var unhidePostsSection = document.createElement('div');
                        unhidePostsSection.setAttribute('class', 'unhide_posts_section');
                        unhidePostsSection.innerHTML = `
                            <span class="dropdown-item unhide-button">Post hidden</span>
                            <a class="dropdown-item unhide-button" href="javascript:void(0);" onclick="Post.togglePostHide('${id}', 'unhide')">Undo</a>
                        `;
                        // Append the wrapper div to the appropriate container
                        reactElement.append(unhidePostsSection);
                    } else {
                        // Find all elements with the class .hide-button
                        reactElement.find(".unhide_posts_section").remove();
                        reactElement.find(".post-content").show();                        
                        reactElement.find(".hide-button").each(function() {
                            // Add onclick event
                            $(this).attr("onclick", "Post.togglePostHide(" + id + ", 'hide');");
                            // Change text
                            $(this).text("Hide this post");
                        });
                    }                    
                    launchToast('success',trans('Success'),result.message);
                } else {
                    launchToast('danger',trans('Error'),result.errors[0]);
                }
            },
            error: function (result) {
                launchToast('danger',trans('Error'),result.responseJSON.message);
            }
        });
    },
    

        // save post
        togglePostSave: function (id, type = 'save') {
            let reactElement = $('*[data-postID="'+id+'"]');
            const isSaved = type == 'save' ? true : false;
            $.ajax({
                type: 'POST',
                data: {                
                    'id': id,
                    'type': type,
                },
                dataType: 'json',
                url: app.baseUrl+'/posts/save_unsave_posts',
                success: function (result) {
                    if(result.success){
                        // Toggle the icon or class of the save button
                        let saveButton = reactElement.find(".save-button");
                        if(isSaved){
                            saveButton.find("span").removeClass("material-symbols-outlined bookmark").addClass("material-symbols-outlined bookmark_added");
                            saveButton.attr("onclick", "Post.togglePostSave(" + id + ", 'unsave');");
                        } else {
                            saveButton.find("span").removeClass("material-symbols-outlined bookmark_added").addClass("material-symbols-outlined bookmark");
                            saveButton.attr("onclick", "Post.togglePostSave(" + id + ", 'save');");
                        }
                        // Optionally, you can update the button text here
                        saveButton.text(isSaved ? "Unsave" : "Save");
                        launchToast('success',trans('Success'),result.message);
                    } else {
                        launchToast('danger',trans('Error'),result.errors[0]);
                    }
                },
                error: function (result) {
                    launchToast('danger',trans('Error'),result.responseJSON.message);
                }
            });
        },


        // User learned post js
        togglePostLearned: function (id, type = 'Learned') {
            let reactElement = $('*[data-postID="'+id+'"]');
            const isLearned = type == 'Learned' ? true : false;
            $.ajax({
                type: 'POST',
                data: {                
                    'id': id,
                    'type': type,
                },
                dataType: 'json',
                url: app.baseUrl+'/posts/learnedPost',
                success: function (result) {
                    if(result.success){
                        // Toggle the icon or class of the save button
                        let learnedButton = reactElement.find(".learned-btn");
                        if(isLearned){
                            learnedButton.find("span").removeClass("material-symbols-outlined bookmark").addClass("material-symbols-outlined bookmark_added");
                            learnedButton.attr("onclick", "Post.togglePostLearned(" + id + ", 'unlearned');");
                        } else {
                            // Hide the button's parent container
                            learnedButton.parent().hide();
                        }
                        // Optionally, you can update the button text here
                        learnedButton.text(isLearned ? "Unlearned" : "Learned");
                        launchToast('success',trans('Success'),result.message);
                    } else {
                        launchToast('danger',trans('Error'),result.errors[0]);
                    }
                },
                error: function (result) {
                    launchToast('danger',trans('Error'),result.responseJSON.message);
                }
            });
        },

        // user shared post js
        
        togglePostShare: function (id, type = 'shared') {
            let reactElement = $('*[data-postID="'+id+'"]');
            const isShared = type == 'shared' ? true : false;
            $.ajax({
                type: 'POST',
                data: {                
                    'id': id,
                    'type': type,
                },
                dataType: 'json',
                url: app.baseUrl+'/posts/share',
                success: function (result) {
                    if(result.success){
                        // Toggle the icon or class of the share button
                        let sharedButton = reactElement.find(".share-button");
                        if(isShared){
                            sharedButton.find("span").removeClass("material-symbols-outlined bookmark").addClass("material-symbols-outlined bookmark_added");
                            sharedButton.attr("onclick", "Post.togglePostShare(" + id + ", 'Already Shared');");
                        } 
                        // Optionally, you can update the button text here
                        sharedButton.text(isShared ? "Share" : "Already Shared");
                        launchToast('success',trans('Success'),result.message);
                    } else {
                        launchToast('danger',trans('Error'),result.errors[0]);
                    }
                },
                error: function (result) {
                    launchToast('danger',trans('Error'),result.responseJSON.message);
                }
            });
        },
        


    /**
     * Function used to pin/unpin a post
     * @param id
     */
    togglePostPin: function (id) {
        let reactElement = $('*[data-postID="'+id+'"] .pin-button');
        const isPinned = reactElement.hasClass('is-active');
        $('.pinned-post-label').addClass('d-none')
        $.ajax({
            type: 'POST',
            data: {
                'action': (isPinned === true ? 'remove' : 'add'),
                'id': id
            },
            dataType: 'json',
            url: app.baseUrl+'/posts/pin',
            success: function (result) {
                if(result.success){
                    if(isPinned){
                        $('*[data-postID="'+id+'"] .pinned-post-label').addClass('d-none')
                        reactElement.removeClass('is-active');
                        reactElement.html(trans('Pin this post'));
                    }
                    else{
                        $('*[data-postID="'+id+'"] .pinned-post-label').removeClass('d-none')
                        reactElement.addClass('is-active');
                        reactElement.html(trans('Un-pin post'));
                    }

                    launchToast('success',trans('Success'),result.message);
                }
                else{
                    launchToast('danger',trans('Error'),result.errors[0]);
                }
            },
            error: function (result) {
                launchToast('danger',trans('Error'),result.responseJSON.message);
            }
        });
    },

    /**
     * Disabling right for posts ( if site wise setting is set to do it )
     */
    disablePostsRightClick: function () {
        $(".post-media, .pswp__item").unbind('contextmenu');
        $(".post-media, .pswp__item").on("contextmenu",function(){
            return false;
        });
    },

    /**
     * Toggles profile's description
     */
    toggleFullDescription:function (postID) {
        let postElement = $('*[data-postID="'+postID+'"]');
        $('*[data-postID="'+postID+'"] .label-less, *[data-postID="'+postID+'"] .label-more').addClass('d-none');
        if(postElement.find('.post-content-data').hasClass('line-clamp-1')){
            postElement.find('.post-content-data').removeClass('line-clamp-1');
            postElement.find('.label-less').removeClass('d-none');
        }
        else{
            postElement.find('.post-content-data').addClass('line-clamp-1');
            postElement.find('.label-more').removeClass('d-none');
        }
        PostsPaginator.scrollToLastPost(postID);
    },

};



