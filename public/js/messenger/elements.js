/**
 *
 * Messages Elements
 *
 */
"use strict";
/* global app, user, messengerVars, trans, filterXSS, messenger, getWebsiteFormattedAmount  */

/**
 * Messenger contact component
 * @param contact
 * @returns {string}
 */
// eslint-disable-next-line no-unused-vars
function contactElement(contact){
    const avatar = contact.receiverID === user.user_id ? contact.senderAvatar : contact.receiverAvatar;
    const name = contact.receiverID === user.user_id ? contact.senderName : contact.receiverName;
    return `
      <div class="col-12 d-flex pt-2 pb-2 contact-box contact-${contact.contactID}" onclick="messenger.fetchConversation(${contact.contactID})">
        <img src="${ avatar }" class="contact-avatar rounded-circle"/>
        <div class="m-0 ml-md-3 d-none d-lg-flex d-md-flex d-xl-flex justify-content-center flex-column text-truncate">
            <div class="m-0 text-truncate overflow-hidden contact-name ${contact.lastMessageSenderID !== user.user_id && contact.isSeen === 0 ? 'font-weight-bold' : ''}">${filterXSS(name)}</div>
            <small class="message-excerpt-holder d-flex text-truncate">
                <span class="text-muted mr-1 ${contact.lastMessageSenderID !== user.user_id ? 'd-none' : ''}"> ${trans('You')}: </span>
                <div class="m-0 text-muted contact-message text-truncate ${contact.lastMessageSenderID !== user.user_id && contact.isSeen === 0 ? 'font-weight-bold' : ''}" >${filterXSS(contact.lastMessage)}</div>
                <div class="d-flex"> <div class="font-weight-bold ml-1">${(contact.created_at !== null ? '∙' :'')}</div>${(contact.created_at !== null ? '&nbsp;' + contact.created_at : '')}</div>
            </small>
        </div>
      </div>
    `;
}

/**
 * Messenger message component
 * @param message
 * @returns {string}
 */
// eslint-disable-next-line no-unused-vars
function messageElement(message){
    let isSender = false;
    if(parseInt(message.sender_id) === parseInt(user.user_id)){
        isSender = true;
    }

    let attachmentsHtml = '';
    message.attachments.map(function (file) {
        attachmentsHtml += messenger.parseMessageAttachment(file);
    });

    /* Paid message preview */
    if(message.hasUserUnlockedMessage === false && message.price > 0 && !isSender){
        return `
          <div class="col-12 no-gutters pt-1 pb-1 message-box px-0" data-messageid="${message.id}" id="m-${message.id}">
                    <div class="m-0 paid-message-box message-box text-break alert ${isSender ? 'alert-primary text-white' : 'alert-default'}">
                        <div class="col-12 d-flex mb-2 ${isSender ? 'sender d-flex flex-row-reverse pr-1' : 'pl-0'}">
                            ${message.message === null ? '' : messenger.parseMessage(message.message)}
                        </div>
                        <div class="d-flex justify-content-center">
                        ${lockedMessagePreview({'id' : message.id, 'price': message.price},message.sender)}
                        </div>
                    </div>
                </div>
          </div>
        `;
    }
    else{
        /* Regular message preview */
        return `
          <div class="col-12 no-gutters pt-1 pb-1 message-box px-0" data-messageid="${message.id}" id="m-${message.id}">
            ${message.message === null ? '' : messageBubble(isSender, message)}
            ${messageAttachments(isSender, attachmentsHtml, message)}
          </div>
    `;
    }

}

/**
 * Message bubble component
 * @param isSender
 * @param message
 * @returns {string}
 */
function messageBubble(isSender, message) {
    return `
        <div class="d-flex flex-row">
                <div class="col-12 d-flex  ${isSender ? 'sender d-flex flex-row-reverse pr-1' : 'pl-0'}">
                    <div class="m-0 message-bubble text-break alert ${isSender ? 'alert-primary text-white' : 'alert-default'}">${messenger.parseMessage(message.message)}</div>
                    ${isSender ? messageActions(true, message) : ''}
                </div>
        </div>
    `;
}

function messageAttachments(isSender, attachmentsHtml, message){
    return `
             <div class="col-12 d-flex  ${isSender ? 'sender d-flex flex-row-reverse pr-1' : 'pl-0'}">
                <div class="attachments-holder row no-gutters flex-row-reverse">
                    ${attachmentsHtml}
                </div>
                ${attachmentsHtml.length && isSender ? messageActions(true, message) : ''}
            </div>
     `;
}

function messageActions(showDeleteButton, message){
    return `
        <div class="d-flex message-actions-wrapper">
            ${showDeleteButton ? `
                <div class="d-flex justify-content-center align-items-center pointer-cursor mr-2">
                    <div class="to-tooltip message-action-button d-flex justify-content-center align-items-center"  data-placement="top" title="${trans('Delete')}" onClick="messenger.showMessageDeleteDialog(${message.id})">
                        <ion-icon name="trash-outline"></ion-icon>
                    </div>
                </div>
            ` : ``}

           ${message.price > 0 ? `
            <div class="d-flex justify-content-center align-items-center mr-2">
                <div class="to-tooltip message-action-button d-flex justify-content-center align-items-center"  data-placement="top" title="${trans('Paid message')}">
                    <ion-icon name="cash-outline"></ion-icon>
                 </div>
            </div>
        ` : ``}
      </div>
    `;
}

/**
 * Locked message preview element
 * @param messageData
 * @param senderData
 * @returns {string}
 */
function lockedMessagePreview(messageData, senderData) {
    return `
            <div class="card ${app.theme === 'light' ? 'bg-gradient-faded-light-vertical' : 'bg-gradient-faded-dark-vertical'}">
              <div>
              <div class="lockedPreviewWrapper">
                  <img class="card-img" src="${messengerVars.lockedMessageSVGPath}" >
              </div>
                  <div class="card-img-overlay d-flex flex-column-reverse">
                           ${lockedMessagePaymentButton(messageData, senderData)}
                    </div>
                  </div>
              </div>
            </div>
`;
}

/**
 * Locked message payment button
 * @param messageData
 * @param senderData
 * @returns {string}
 */
function lockedMessagePaymentButton(messageData, senderData) {
    let modalData = `
                        data-toggle="modal"
                        data-target="#checkout-center"
                        data-type="message-unlock"
                        data-recipient-id="${senderData.id}"
                        data-amount="${messageData.price}"
                        data-first-name="${user.billingData.first_name}"
                        data-last-name="${user.billingData.last_name}"
                        data-billing-address="${user.billingData.billing_address}"
                        data-country="${user.billingData.country}"
                        data-city="${user.billingData.city}"
                        data-state="${user.billingData.state}"
                        data-postcode="${user.billingData.postcode}"
                        data-available-credit="${user.billingData.credit}"
                        data-username="${senderData.username}"
                        data-name="${senderData.first_name}"
                        data-avatar="${senderData.avatar}"
                        data-message-id="${messageData.id}"
    `;

    if(senderData.canEarnMoney === false) {
        modalData = `
            data-placement="top"
            title="${trans('This creator cannot earn money yet')}"
        `;
    }

    return `
                <button class="btn btn-round btn-primary btn-block d-flex align-items-center justify-content-center justify-content-lg-between mt-2 mb-0 to-tooltip" ${modalData}>
                <span class="d-none d-md-block">${trans('Locked message')}</span>  <span>${trans('Unlock for')} ${getWebsiteFormattedAmount(messageData.price)}</span>
                </button>
    `;
}


// eslint-disable-next-line no-unused-vars
function noMessagesLabel() {
    return `
        <div class="d-flex h-100 align-items-center justify-content-center">
            <div class="d-flex"><span>👋 ${trans('You got no messages yet.')} </span><span class="d-none d-md-block d-lg-block d-xl-block">&nbsp;${trans("Say 'Hi!' to someone!")}</span></div>
        </div>
    `;
}

// eslint-disable-next-line no-unused-vars
function noContactsLabel() {
    return `<div class="d-flex mt-3 mt-md-2 pl-3 pl-md-0 mb-3 pl-md-0"><span>${trans("Click the text bubble to send a new message.")}</span></div>`;
}
