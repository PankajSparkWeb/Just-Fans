/**
 * Subscription settings component
 */
"use strict";
/* global app, updateButtonState, trans */

var SubscriptionsSettings = {
    selectedSubID: null,
    redirectTo: 'subscriptions',
    confirmSubCancelation: function (subIDToCancel, redirectTo = 'subscriptions') {
        SubscriptionsSettings.redirectTo = redirectTo;
        SubscriptionsSettings.selectedSubID = subIDToCancel;
        $('#subscription-cancel-dialog').modal('show');
    },
    cancelSubscription: function () {
        updateButtonState('loading',$('#subscription-cancel-dialog .btn'), trans('Confirm'));
        window.location.href = app.baseUrl + '/subscriptions/'+SubscriptionsSettings.selectedSubID+'/cancel/'+SubscriptionsSettings.redirectTo;
    }
};
