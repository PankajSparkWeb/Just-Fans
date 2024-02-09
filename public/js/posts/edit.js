/*
* Post create page
 */
"use strict";
/* global FileUpload, PostCreate, PostCreate, postData */
$(function () {
    // Initing button save
    $('.post-create-button').on('click', function () {
        PostCreate.save('update', postData.id);
    });

    // Set the content of the Quill editor with the post text
    quill.root.innerHTML = postData.text;

    // Populate input fields with last values of the post
    $('#post_text').val(postData.text); // Assuming your input field has the id 'post_text'
    $('#external_post_link').val(postData.external_post_link); // Assuming your input field has the id 'external_post_link'

    PostCreate.initPostDraft(postData, 'edit');
    PostCreate.postPrice = postData.price;
    FileUpload.initDropZone('.dropzone', '/attachment/upload/post');
});
