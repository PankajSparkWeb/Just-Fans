/**
 *
 * Messages Component
 *
 */
"use strict";
/* global app, messengerVars, pusher, FileUpload,
  Lists, Pusher, PusherBatchAuthorizer, updateButtonState,
  mswpScanPage, trans, bootstrapDetectBreakpoint, incrementNotificationsCount, passesMinMaxPPVContentCreationLimits
  EmojiButton, filterXSS, launchToast, initTooltips, soketi, socketsDriver, showDialog, hideDialog, noMessagesLabel,
  contactElement, noContactsLabel, messageElement */

$(function () {

    if(messengerVars.bootFullMessenger){
        messenger.boot();
        messenger.fetchContacts();
        messenger.initAutoScroll();
        messenger.initMarkAsSeen();
        messenger.resetTextAreaHeight();
        messenger.initEmojiPicker();
        if(messengerVars.lastContactID !== false && messengerVars.lastContactID !== 0){
            messenger.fetchConversation(messengerVars.lastContactID);
        }
        else{
            $('.conversation-content').html(noMessagesLabel());
        }
        FileUpload.initDropZone('.dropzone','/attachment/upload/message');
        messenger.initSelectizeUserList();
    }

    messenger.initNewConversationUI();

});

var messenger = {

    state : {
        contacts:[],
        conversation:[],
        activeConversationUserID:null,
        activeConversationUser:null,
        currentBreakPoint: 'lg',
        redirectedToMessage: false,
        messagePrice: 5,
        isPaidMessage: false,
        activeMessageID: null,
        receiverIDs: [],
        newConversationMode: false,
        newConversationSelectAllToggle: false
    },

    pusher: null,
    selectizeInstance: null,

    /**
     * Boots up the main messenger functions
     */
    boot: function(){
        Pusher.logToConsole = typeof messengerVars.pusherDebug !== 'undefined' ? messengerVars.pusherDebug : false;
        let params = {
            authorizer: PusherBatchAuthorizer,
            authDelay: 200,
            authEndpoint: app.baseUrl + '/my/messenger/authorizeUser',
            auth: {
                headers: {
                    'X-CSRF-Token': $('meta[name="csrf-token"]').attr('content')
                }
            }
        };
        if(socketsDriver === 'soketi'){
            params.wsHost = soketi.host;
            params.wsPort = soketi.port;
            params.forceTLS = soketi.useTSL ? true : false;
        }
        else{
            params.cluster = messengerVars.pusherCluster;
        }
        messenger.pusher = new Pusher(socketsDriver === 'soketi' ? soketi.key : pusher.key, params);
    },

    /**
     * Instantiates pusher sockets for each conversation (batched)
     */
    initLiveSockets: function(){
        // TODO: Optimization: When fetchContacts is call, only re-init sockets for required channels
        $.each(messenger.state.contacts, function (k,v) {
            const minID = Math.min(v.receiverID,v.senderID);
            const maxID = Math.max(v.receiverID,v.senderID);
            const keyID = ("" + minID + '-' + maxID);
            let channel = messenger.pusher.subscribe('private-chat-channel-'+keyID);
            channel.unbind('new-message');
            channel.bind('new-message', function(data) {
                const message = jQuery.parseJSON(data.message);
                if(message.sender_id === messenger.state.activeConversationUserID){
                    messenger.state.conversation.push(message);
                    messenger.reloadConversation();
                }
                messenger.updateUnreadMessagesCount(parseInt($('#unseenMessages').html()) + 1);
                messenger.addLatestMessageToConversation(message.sender_id,message);
                messenger.markConversationAsRead(message.sender_id,'unread');
                messenger.fetchContacts();
            });
        });
    },

    /**
     * Initiate chatbox scroll to bottom event
     */
    initAutoScroll: function(){
        $(".messageBoxInput").keydown(function(e){
            // Enter was pressed without shift key
            if (e.keyCode === 13)
            {
                if(!e.shiftKey){
                    e.preventDefault();
                    $('.send-message').trigger('click');
                }
            }
        });
    },

    /**
     * Fetches all messenger contacts
     */
    fetchContacts: function (callback = function(){}) {
        $.ajax({
            type: 'GET',
            url: app.baseUrl + '/my/messenger/fetchContacts',
            dataType: 'json',
            success: function (result) {
                if(result.status === 'success'){
                    messenger.state.contacts = result.data.contacts;
                    messenger.reloadContactsList();
                    messenger.initLiveSockets();
                    callback();
                }
                else{
                    // messenger.state.contacts = result.data
                }
            }
        });
    },

    /**
     * Switches between layout having horiznatal scroll for contacts or not
     */
    makeContactsHeaderResponsive: function(){
        const breakPoint = bootstrapDetectBreakpoint();
        if(breakPoint.name === 'xs'){
            $('.conversations-list').mCustomScrollbar({
                theme: "minimal-dark",
                axis:'x',
                scrollInertia: 200,
            });
            $('.conversations-list').addClass('border-top');
        }
        else{
            $('.conversations-list').mCustomScrollbar("destroy");
            $('.conversations-list').removeClass('border-top');
        }
    },

    /**
     * Fetches conversation with certain user
     * @param userID
     */
    fetchConversation: function (userID) {
        messenger.closeNewConversationUI();
        // Setting up loading and clearign up conv content
        $('.conversation-loading-box').removeClass('d-none');
        $('.conversation-header-loading-box').removeClass('d-none');
        $('.conversation-header').addClass('d-none');

        // Setting up loading and clearign up conv content
        $('.conversation-loading-box').removeClass('d-none');
        $('.conversation-content').html('');
        $.ajax({
            type: 'GET',
            url: app.baseUrl + '/my/messenger/fetchMessages/' + userID,
            dataType: 'json',
            success: function (result) {
                if(result.status === 'success'){
                    messenger.state.conversation = result.data.messages;
                    messenger.reloadConversation();
                    messenger.state.activeConversationUserID = userID;
                    messenger.setActiveContact(userID);
                    messenger.reloadConversationHeader();
                    initTooltips();
                }
                else{
                    // messenger.state.contacts = result.data
                }
            }
        });
    },

    /**
     * Sends the message
     * @returns {boolean}
     */
    sendMessage: function(forceSave = false) {

        // Checking if files are being uploaded
        if(FileUpload.isLoading === true && forceSave === false){
            $('.confirm-post-save').unbind('click');
            $('.confirm-post-save').on('click',function () {
                messenger.sendMessage(true);
            });
            $('#confirm-post-save').modal('show');
            return false;
        }

        // Check if locked message has at least one attachment
        if(messenger.state.isPaidMessage && FileUpload.attachaments.length === 0){
            $('#no-attachments-locked-post').modal('show');
            return false;
        }

        updateButtonState('loading',$('.send-message'));
        // Validation
        if($('.messageBoxInput').val().length === 0 && FileUpload.attachaments.length === 0){
            updateButtonState('loaded',$('.send-message'));
            return false;
        }

        $.ajax({
            type: 'POST',
            url: app.baseUrl + '/my/messenger/sendMessage',
            data: {
                'message': $('.conversation-writeup .messageBoxInput').val(),
                'attachments' : FileUpload.attachaments,
                'receiverIDs' : messenger.state.receiverIDs,
                'price': messenger.state.isPaidMessage ? messenger.state.messagePrice : 0
            },
            dataType: 'json',
            success: function (result) {
                messenger.clearMessageBox();
                messenger.clearMessagePrice();
                messenger.resetTextAreaHeight();
                messenger.clearFileUploadsState();
                if(messenger.state.receiverIDs.length === 1){
                    // Single message
                    messenger.state.conversation.push(result.data.message);
                    messenger.addLatestMessageToConversation(result.data.message.receiverID,result.data.message);
                    if(messenger.state.newConversationMode){
                        messenger.fetchContacts(function () {});
                        messenger.state.activeConversationUserID = result.data.message.receiver_id;
                        messenger.fetchConversation(result.data.message.receiver_id);
                    }
                    messenger.reloadConversation();
                    messenger.closeNewConversationUI();
                }
                else{
                    // Mass messages
                    const latestContactId = result.data[result.data.length - 1].message.receiver_id;
                    if(messenger.state.newConversationMode){
                        messenger.fetchContacts();
                    }
                    messenger.state.activeConversationUserID = latestContactId;
                    messenger.fetchConversation(latestContactId);
                    initTooltips();
                    if(result.errors){
                        launchToast('danger',trans('Error'),result.errors);
                    }
                }
                $('#confirm-post-save').modal('hide');
                messenger.hideEmptyChatElements();
                updateButtonState('loaded', $('.send-message'));
                initTooltips();
            },
            error: function (result) {
                launchToast('danger',trans('Error'),result.responseJSON.message);
            }
        });
    },

    /**
     * Clears up uploaded files
     */
    clearFileUploadsState: function(){
        FileUpload.attachaments = [];
        $('.dropzone-previews').html('');
    },

    /**
     * Method used for starting a conversation from the profile page
     */
    sendDMFromProfilePage: function(){
        let submitButton = $('.new-conversation-label');
        updateButtonState('loading',submitButton, trans('Send'), 'white');
        $.ajax({
            type: 'POST',
            url: app.baseUrl + '/my/messenger/sendMessage',
            data: {'receiverIDs':[$('#receiverID').val()], 'message' : $('#messageText').val()},
            success: function () {
                $("textarea[name=message]").val("");
                $('#messageModal').modal('hide');
                window.location.assign(app.baseUrl + '/my/messenger');
                updateButtonState('loaded',submitButton, trans('Save'));
            },
            error: function (result) {
                launchToast('danger',trans('Error'),result.responseJSON.message);
                updateButtonState('loaded',submitButton, trans('Save'));
            },
        });
    },

    /**
     * Marks message as seen
     */
    initMarkAsSeen:function(){
        $( ".messageBoxInput" ).on('click', function() {
            if($('#unseenValue').val() !== 0){
                $.ajax({
                    type: 'POST',
                    url: app.baseUrl + '/my/messenger/markSeen',
                    data: {userID:messenger.state.activeConversationUserID},
                    dataType: 'json',
                    success: function (result) {
                        messenger.markConversationAsRead(messenger.state.activeConversationUserID,'read');
                        messenger.updateUnreadMessagesCount(parseInt($('#unseenMessages').html()) - result.data.count);
                        incrementNotificationsCount('.menu-notification-badge.chat-menu-count', (-parseInt(result.data.count)));
                        messenger.reloadContactsList();
                    }
                });
            }
        });
    },

    /**
     * Checks if user already has a conversation with certain user
     * @param contactID
     * @returns {boolean}
     */
    isExistingContact: function(contactID){
        // Search if contact is present
        let isNewContact = false;
        $.map(messenger.state.contacts,function (contact) {
            if(contactID === contact.contactID){
                isNewContact = true;
            }
        });
        return isNewContact;
    },

    /**
     * Reloads conversation list
     */
    reloadContactsList: function () {
        let contactsHtml = '';
        $.each( messenger.state.contacts, function( key, value ) {
            contactsHtml += contactElement(value);
        });
        if(messenger.state.contacts.length > 0){
            $('.conversations-list').html('<div class="row">'+contactsHtml+'</div>');
        }
        else{
            $('.conversations-list').html(noContactsLabel());
        }
        $('.contact-'+messenger.state.activeConversationUserID).addClass('contact-active');
    },

    /**
     * Reloads convesation header
     */
    reloadConversationHeader: function(){
        if(typeof messenger.state.conversation[0] !== 'undefined'){
            const contact = messenger.state.conversation[0];
            const userID = (contact.receiver_id !== messenger.state.activeConversationUserID ? contact.sender.id : contact.receiver.id);
            const username = (contact.receiver_id !== messenger.state.activeConversationUserID ? contact.sender.username : contact.receiver.username);
            const avatar = (contact.receiver_id !== messenger.state.activeConversationUserID ? contact.sender.avatar : contact.receiver.avatar);
            const name = contact.receiver_id !== messenger.state.activeConversationUserID ? `${contact.sender.name} ` : `${contact.receiver.name}`;
            const profile = contact.receiver_id !== messenger.state.activeConversationUserID ? contact.sender.profileUrl : contact.receiver.profileUrl;
            $('.conversation-header').removeClass('d-none');
            $('.conversation-header-loading-box').addClass('d-none');
            $('.conversation-header-avatar').attr('src',avatar);
            $('.conversation-header-user').html(name);
            $('.conversation-profile-link').attr('href',profile);

            $('.details-holder .unfollow-btn').unbind('click');
            $('.details-holder .block-btn').unbind('click');
            $('.details-holder .report-btn').unbind('click');

            $('.details-holder .unfollow-btn').on('click',function () {
                Lists.showListManagementConfirmation('unfollow', userID);
            });
            $('.details-holder .block-btn').on('click',function () {
                Lists.showListManagementConfirmation('block', userID);
            });
            $('.details-holder .report-btn').on('click',function () {
                Lists.showReportBox(userID,null);
            });
            if(contact.sender.canEarnMoney === false) {
                $('.details-holder .tip-btn').addClass('hidden');
            } else {
                $('.details-holder .tip-btn').attr('data-username','@'+username);
                $('.details-holder .tip-btn').attr('data-name',name);
                $('.details-holder .tip-btn').attr('data-avatar',avatar);
                $('.details-holder .tip-btn').attr('data-recipient-id',userID);
            }

        }
    },

    /**
     * Reloads conversation
     */
    reloadConversation: function () {
        let conversationHtml = '';
        $.each( messenger.state.conversation, function( key, value ) {
            conversationHtml += messageElement(value);
        });
        $('.conversation-content').html(conversationHtml);

        // Navigating to last message or last paid mesage
        let urlParams = new URLSearchParams(window.location.search);
        // Scrolling to newly unlocked message if this redirect comes from a message-unlock payment
        if(urlParams.has('token') && !messenger.state.redirectedToMessage) {
            let token = '#m-'.concat(urlParams.get('token'));
            if($('.conversation-content .message-box').length && $('.conversation-content').find(token).length){
                let offset = $('.conversation-content').find(token).offset().top - $('.conversation-content').offset().top + $('.conversation-content').scrollTop();
                $(".conversation-content").animate({scrollTop: offset}, 'slow');
            }

            $('.conversation-content').find(token).animate({
                backgroundColor: "rgba(203,12,159,.2)",
            }, 1000).delay(2000).queue(function() {
                $('.conversation-content').find(token).animate({
                    backgroundColor: "rgba(0,0,0,0)",
                }, 1000).dequeue();
            });

            messenger.state.redirectedToMessage = true;
        } else {
            // Scrolling down to last message
            if($('.conversation-content .message-box').length){
                $(".conversation-content").animate({ scrollTop: $('.conversation-content')[0].scrollHeight + 100}, 800);
            }
        }
        $('.conversation-loading-box').addClass('d-none');
        messenger.initLinks();
        messenger.initMessengerGalleries();
    },

    /**
     * Method used for auto adjusting textarea message height on resize
     * @param el
     */
    textAreaAdjust: function(el) {
        el.style.height = (el.scrollHeight > el.clientHeight) ? (el.scrollHeight)+"px" : "40px";
    },

    /**
     * Resets the send new message text area height
     */
    resetTextAreaHeight: function(){
        $(".messageBoxInput").css('height',45);
    },

    /**
     * Set currently active contact
     * @param userID
     */
    setActiveContact: function (userID) {
        $('.messageBoxInput').focus();
        $('#receiverID').val(userID);// TODO: Not used anymore
        messenger.state.receiverIDs = [userID];
        $('.contact-box').each(function (k,el) {
            $(el).removeClass('contact-active');
        });
        $('.contact-'+messenger.state.activeConversationUserID).addClass('contact-active');
    },

    /**
     * Clears up the new message field
     */
    clearMessageBox: function(){
        $(".messageBoxInput").val('');
    },

    /**
     * Updates the unread messages count
     * @param val
     * @returns {boolean}
     */
    updateUnreadMessagesCount: function (val) {
        $("#unseenMessages").html(val);
        return true;
    },

    /**
     * Marks conversation as being read
     * @param userID
     * @param type
     */
    markConversationAsRead: function (userID, type) {
        $.map(messenger.state.contacts,function (contact,k) {
            if(userID === contact.contactID){
                let newContact = contact;
                newContact.isSeen = type === 'read' ? 1 : 0;
                messenger.state.contacts[k] = newContact;
            }
        });
        // eslint-disable-next-line no-unused-vars
        let newContactsList = messenger.state.contacts; // These kinds of stuff should be immutable
    },

    /**
     * Appends latest message to the conversation
     * @param contactID
     * @param message
     */
    addLatestMessageToConversation: function (contactID, message) {
        // add latest contact details
        let contactKey = null;
        // eslint-disable-next-line no-unused-vars
        let contactObj = null;
        let newContact = null;
        $.map(messenger.state.contacts,function (contact,k) {
            if(contactID === contact.contactID){
                newContact = contact;
                contactKey = k;
                newContact.lastMessage = message.message;
                newContact.dateAdded = message.dateAdded;
                newContact.dateAdded = message.dateAdded;
                newContact.senderID = message.sender_id;
                newContact.lastMessageSenderID = message.sender_id;
                messenger.state.contacts[k] = newContact;
            }
        });

        let newContactsList = messenger.state.contacts; // These kinds of stuff should be immutable
        if(contactKey !== null){
            newContactsList.splice(contactKey, 1);
            newContactsList.unshift(newContact);
            messenger.state.contacts = newContactsList;
        }

    },

    /**
     * Globally instantiates all href links within a conversation
     */
    initLinks: function(){
        $('.conversation-content .message-bubble').html(function(i, text) {
            var body = text.replace(
                // eslint-disable-next-line no-useless-escape
                /\bhttps:\/\/([\w\.-]+\.)+[a-z]{2,}\/.+\b/gi,
                '<a target="_blank" class="text-white" href="$&">$&</a>'
            );
            return body.replace(
                // eslint-disable-next-line no-useless-escape
                /\bhttp:\/\/([\w\.-]+\.)+[a-z]{2,}\/.+\b/gi,
                '<a target="_blank" class="text-white" href="$&">$&</a>'
            );
        });
    },

    /**
     * Globally instantiates all message attachments and groups them into individual galleries
     */
    initMessengerGalleries: function(){
        $('.message-box').each(function (index, item) {
            if($(item).find('.attachments-holder').children().length > 0){
                mswpScanPage($(item),'mswp');
            }
        });
    },

    /**
     * Replaces message's newlines with html break lines
     * @param text
     * @returns {*}
     */
    parseMessage: function(text){
        return filterXSS(text.replaceAll('\n','<br/>'));
    },

    /**
     * Loads UI elements for loaded messenger
     */
    hideEmptyChatElements: function () {
        $('.conversation-writeup').removeClass('hidden');
        $('.no-contacts').addClass('hidden');
    },

    /**
     * Instantiates & applies selectize on the new conversation modal
     */
    initSelectizeUserList: function(){
        if(typeof Selectize !== 'undefined') {
            messenger.selectizeInstance = $('#select-repo').selectize({
                valueField: 'id',
                searchField: 'label',
                options: messengerVars.availableContacts,
                create: false,
                render: {
                    option: function (item, escape) {
                        return '<div>' +
                            '<img class="searchAvatar ml-3 my-1" src="' + escape(item.avatar) + '" alt="">' +
                            '<span class="name ml-2">' + escape(item.name) + '</span>' +
                            '</div>';
                    },
                    item: function (item, escape) {
                        return '<div>' +
                            '<img class="searchAvatar ml-1" src="' + escape(item.avatar) + '" alt="">' +
                            '<span class="name ml-2">' + escape(item.name) + '</span>' +
                            '</div>';
                    }
                },
                onChange(value) {
                    messenger.state.receiverIDs = value.map(function (x) {
                        return parseInt(x, 10);
                    });
                }
            });
        }
    },

    /**
     * Shows up new conversation modal in UI
     */
    showNewMessageDialog: function () {
        $('#messageModal').modal('show');
    },

    /**
     * Instantiates the emoji picker messenger
     * @param post_id
     */
    initEmojiPicker: function(){
        try{
            const button = document.querySelector('.conversation-writeup .trigger');
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
                $('.messageBoxInput').val($('.messageBoxInput').val() + emoji);

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

    showSetPriceDialog: function () {
        $('#message-set-price-dialog').modal('show');
    },

    clearMessagePrice: function(){
        messenger.state.messagePrice = 5;
        messenger.state.isPaidMessage = false;
        $('#message-price').val(5);
        $('.message-price-lock').removeClass('d-none');
        $('.message-price-close').addClass('d-none');
        $('#message-set-price-dialog').modal('hide');
    },

    saveMessagePrice: function(){
        messenger.state.isPaidMessage = true;
        messenger.state.messagePrice = $('#message-price').val();
        if(!passesMinMaxPPVContentCreationLimits(messenger.state.messagePrice)){
            $('#message-price').addClass('is-invalid');
            return false;
        }
        $('.message-price-lock').addClass('d-none');
        $('.message-price-close').removeClass('d-none');
        $('#message-set-price-dialog').modal('hide');
        $('#message-price').removeClass('is-invalid');
    },

    /**
     * Parses messenger's attachment previews
     * @param file
     * @returns {string}
     */
    parseMessageAttachment: function(file){
        let attachmentsHtml = '';
        switch (file.type) {
            case 'avi':
            case 'mp4':
            case 'wmw':
            case 'mpeg':
            case 'm4v':
            case 'moov':
            case 'mov':
                attachmentsHtml = `
                <a href="${file.path}" rel="mswp" title="" class="mr-2 mt-2">
                    <div class="video-wrapper">
                     <video class="video-preview" src="${file.path}" width="150" height="150" controls autoplay muted></video>
                    </div>
                 </a>`;
                break;
            case 'mp3':
            case 'wav':
            case 'ogg':
                attachmentsHtml = `
                <a href="${file.path}" rel="mswp" title="" class="mr-2 mt-2 d-flex align-items-center">
                    <div class="video-wrapper">
                         <audio id="video-preview" src="${file.path}" controls type="audio/mpeg" muted></audio>
                    </div>
                 </a>`;
                break;
            case 'png':
            case 'jpg':
            case 'jpeg':
                attachmentsHtml = `
                    <a href="${file.path}" rel="mswp" title="">
                        <img src="${file.thumbnail}" class="mr-2 mt-2">
                    </a>`;
                break;
            default:
                attachmentsHtml = `<img src="${file.thumbnail}" class="mr-2 mt-2">`;
                break;
        }
        return attachmentsHtml;
    },

    /**
     * Shows up message delete confirmation dialog
     * @param messageID
     */
    showMessageDeleteDialog: function(messageID){
        showDialog('message-delete-dialog');
        messenger.state.activeMessageID = messageID;
    },

    /**
     * Removes own comments
     */
    deleteMessage: function () {
        $.ajax({
            type: 'DELETE',
            dataType: 'json',
            url: app.baseUrl + '/my/messenger/delete/' + messenger.state.activeMessageID,
            success: function (result) {
                let element = $('*[data-messageid="'+messenger.state.activeMessageID+'"]');
                element.remove();
                hideDialog('message-delete-dialog');
                launchToast('success',trans('Success'),trans('Message removed'));
                if(result.isLastMessage === true){
                    messenger.fetchContacts(function () {
                        if(messenger.state.contacts.length >= 1){
                            messenger.state.activeConversationUserID = messenger.state.contacts[0].contactID;
                            messenger.fetchConversation(messenger.state.activeConversationUserID);
                        }
                        else{
                            messenger.fetchContacts();
                            $('.conversation-content').html(noMessagesLabel());
                            $('.conversation-writeup').addClass('hidden');
                            $('.conversation-header').addClass('d-none');
                        }

                    });
                }
                else{
                    messenger.fetchConversation(messenger.state.activeConversationUserID);
                }
            },
            error: function (result) {
                hideDialog('message-delete-dialog');
                launchToast('danger',trans('Error'),result.responseJSON.message);
            }
        });
    },


    /**
     * Inits the new conversation UI events
     */
    initNewConversationUI: function(){
        $('.new-conversation-toggle').on('click', function () {
            if(messenger.state.newConversationMode){
                messenger.closeNewConversationUI();
            }
            else{
                messenger.openNewConversationUI();
            }
        });

        $('.new-conversation-close').on('click', function () {
            messenger.closeNewConversationUI();
        });

        $('.new-conversation-toggle-all').on('click', function () {
            messenger.toggleAllContacts();
        });



    },

    /**
     * Closes the new conversation UI
     * @returns {boolean}
     */
    closeNewConversationUI: function () {
        $('.conversation-header').removeClass('d-none');
        $('.new-conversation-header').addClass('d-none');
        if(messenger.selectizeInstance !== null){
            messenger.selectizeInstance[0].selectize.clear();
        }
        if(messenger.state.contacts.length === 0 && messengerVars.lastContactID === 0){
            $('.conversation-content').html(noMessagesLabel());
            $('.conversation-writeup').addClass('hidden');
            $('.conversation-header').addClass('d-none');
        }
        else{
            messenger.reloadConversation();
        }
        messenger.state.newConversationMode = false;
        return true;
    },

    /**
     * Toggles all contacts in new create message dialog | mass message
     */
    toggleAllContacts: function(){
        if(messenger.state.newConversationSelectAllToggle === false){
            var el = messenger.selectizeInstance[0].selectize;
            var optKeys = Object.keys(el.options);
            optKeys.forEach(function (key) {
                el.addItem(key);
            });
            messenger.state.newConversationSelectAllToggle = true;
        }
        else{
            messenger.selectizeInstance[0].selectize.clear();
            messenger.state.newConversationSelectAllToggle = false;
        }
    },

    /**
     * Opens up the new conversation dialog
     * @returns {boolean}
     */
    openNewConversationUI: function () {
        if(messengerVars.availableContacts.length === 0) {
            return false;
        }
        messenger.hideEmptyChatElements();
        $('.conversation-header').addClass('d-none');
        $('.new-conversation-header').removeClass('d-none');
        $('.conversation-content').html('');
        messenger.state.newConversationMode = true;
        return true;
    }

};
