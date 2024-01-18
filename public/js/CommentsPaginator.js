/**
 * Paginator component - used for posts (feed+profile) pagination
 */
"use strict";
/* global trans, launchToast, initTooltips */

var CommentsPaginator = {

    nextPageUrl: '',
    container: '',

    init: function (route,container) {
        CommentsPaginator.nextPageUrl = route;
        CommentsPaginator.container = container;
    },

    /**
     * Loads up paginated results and appends them to the page
     * @param post_id
     * @param limit
     */

   loadResults: function (post_id, limit = 9) {
    let postElement = $('*[data-postID="'+post_id+'"] .post-comments');
    $.ajax({
        type: 'GET',
        data: {
            'post_id': post_id,
            'limit': limit,
        },
        url: CommentsPaginator.nextPageUrl,
        success: function (result) {
            if (result.data.comments.length > 0) {
                let commentsHtml = [];                
                result.data.comments.forEach(function (comment) {
                    commentsHtml.push(printComment(comment));
                });
                CommentsPaginator.appendCommentsResults(commentsHtml); // Pass the array of comments HTML
                if (result.data.hasMore) {
                    postElement.find('.show-all-comments-label').removeClass('d-none');
                    CommentsPaginator.nextPageUrl = result.data.next_page_url;
                } else {
                    postElement.find('.show-all-comments-label').addClass('d-none');
                }
            } else {
                postElement.find('.no-comments-label').removeClass('d-none');
            }
            $(CommentsPaginator.container).find('.comments-loading-box').addClass('d-none'); // Hiding out the loading element
            initTooltips();
        },
        error: function (result) {
            launchToast('danger', trans('Error'), result.responseJSON.message);
        }
    });

    function printComment(comment) {
        let html = comment.html;
        let repliesHtml = '';
        if (comment.replies && comment.replies.length > 0) {
            repliesHtml += '<ul class="replies-list">';
            comment.replies.forEach(function (reply) {
                repliesHtml += '<li>' + printComment(reply) + '</li>';
            });
            repliesHtml += '</ul>';
        }
        html += repliesHtml;
        return html;
    }
},

/**
 * Appends the new comments to the comments box
 * @param commentsHtml - Array of comments HTML
 */
appendCommentsResults: function(commentsHtml){
    // Appending the output
    if(typeof CommentsPaginator.container === 'string'){
        $(CommentsPaginator.container).append(commentsHtml.join("\n")).fadeIn('slow');
    }
    else{
        CommentsPaginator.container.append(commentsHtml.join("\n")).fadeIn('slow');
    }
},


};


