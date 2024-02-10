/**
 *
 * Main App Component
 *
 */
"use strict";
/* global app, user, pusher, Pusher, PostsPaginator, notifications, filterXSS, soketi, socketsDriver, messenger */

// Init
$(function () {

    log('🚀 © JustFans Loaded © 🚀');

    if (app.showCookiesBox !== null) {
        var br = bootstrapDetectBreakpoint();
        if (br === null) {
            br = { name: 'lg' };
        }
        let cookiesConsetOptions = {
            "theme": "classic",
            "position": (br.name !== 'xs' ? "bottom-right" : "bottom"),
            dismissOnScroll: 100,
            dismissOnWindowClick: true,
            "palette": {
                "popup": {
                    "background": "#efefef",
                    "text": "#404040"
                },
                "button": {
                    "background": "#007BFF",
                    "text": "#ffffff"
                }
            },
            content: {
                message: trans(`🍪 ${trans('This website uses cookies to improve your experience.')}`),
                dismiss: trans(`Got it!`),
                link: trans('Learn more'),
            },
        };
        if (br.name === 'xs') {
            cookiesConsetOptions.dismissOnScroll = 100;
        }
        window.cookieconsent.initialise(cookiesConsetOptions);
    }


    if (app.enable_age_verification_dialog) {
        if (!getCookie('site_entry_approval')) {
            $('#site-entry-approval-dialog').modal('show');
            $('body .flex-fill').addClass('blurred');
        }
        $('#site-entry-approval-dialog').on('hidden.bs.modal', function () {
            $('body .flex-fill').removeClass('blurred');
        });
    }

    // Auto-including the CSRF token in all AJAX Requests
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
    });

    // Globally handling AJAX requests, especially for handling expired tokens and sesisions
    // TODO: Decide if this should be left enabled on prod mode or if it would help clients more
    $(document).ajaxError(function (event, jqXHR) {
        if (jqXHR.status === 0) {
            log('Not connect.n Verify Network.', 'error');
        } else if (jqXHR.status === 404) {
            log('Requested page not found. [404]', 'error');
        } else if (jqXHR.status === 500) {
            log('Internal Server Error [500].', 'error');
        } else if (jqXHR.status === 401) {
            log('Session expired. Redirecting you to refresh the session.', 'error');
            redirect(app.baseUrl);
        } else if (jqXHR.status === 408) {
            reload();
        } else {
            log('Uncaught Error.n' + jqXHR.responseText, 'error');
        }
    });

    // Displaying error messages for expired sessions
    if (app.sessionStatus === 'expired') {
        launchToast('info', 'Session expired ', 'Page refreshed', 'now');
    }

    // Dark mode switcher event
    $('.dark-mode-switcher').on('click', function () {
        let currentTheme = getCookie('app_theme');
        if (currentTheme === 'dark') {
            setCookie('app_theme', 'light', 365);
        } else {
            setCookie('app_theme', 'dark', 365);
        }
        reload();
    });

    // RTL mode switcher event
    $('.rtl-mode-switcher').on('click', function () {
        let currentTheme = getCookie('app_rtl');
        if (currentTheme === 'rtl') {
            setCookie('app_rtl', 'ltr', 365);
        } else {
            setCookie('app_rtl', 'rtl', 365);
        }
        reload();
    });

    // Initialize tooltips
    initTooltips();

    // Initialize user connection to pusher
    try {
        // Enable pusher logging - don't include this in production
        Pusher.logToConsole = pusher.logging;
        let params = {
            cluster: pusher.cluster
        };
        if (socketsDriver === 'soketi') {
            params = {
                wsHost: soketi.host,
                wsPort: soketi.port,
                forceTLS: soketi.useTSL ? true : false,
            };
        }
        var pusherClient = new Pusher(socketsDriver === 'soketi' ? soketi.key : pusher.key, params);
        var channel = pusherClient.subscribe(user.username);

        // Binding the new notifications
        channel.bind('new-notification', function (data) {
            let toastTitle = trans('Notification');
            if (data.type === 'new-message') {
                toastTitle = 'New message';
                incrementNotificationsCount('.menu-notification-badge.chat-menu-count');
            }
            incrementNotificationsCount('.menu-notification-badge.notifications-menu-count');
            const location = window.location.href;

            if (window.location.href !== null && window.location.href.indexOf('/my/notifications') >= 0) {
                notifications.updateUserNotificationsList(this.getNotificationsActiveFilter());
            }
            if (location.indexOf('my/messenger') >= 0 && data.type === 'new-message') {
                return true;
            }
            launchToast('success', trans(toastTitle), filterXSS(data.message));
        });

        // Binding global messenger events
        channel.bind('messenger-actions', function (data) {
            if (data.type === 'new-messenger-conversation' && window.location.href.indexOf('my/messenger') >= 0) {
                messenger.fetchContacts();
                messenger.fetchConversation(data.notification.fromUserID);
                messenger.hideEmptyChatElements();
                messenger.reloadConversationHeader();
            }
        });

    } catch (e) {
        // eslint-disable-next-line no-console
        // console.warn(e);
    }
});

$(window).scroll(function () {
    if (typeof skipDefaultScrollInits === 'undefined') {
        if ($('.side-menu').length) {
            initStickyComponent('.side-menu', 'sticky');
        }
    }
});

/**
 * Log function sugar syntax
 * @param v
 */
function log(v, type = 'log') {
    if (app.debug) {
        switch (type) {
            case 'info':
                // eslint-disable-next-line no-console
                console.info(v);
                break;
            case 'log':
                // eslint-disable-next-line no-console
                console.log(v);
                break;
            case 'warn':
                // eslint-disable-next-line no-console
                console.warn(v);
                break;
            case 'error':
                // eslint-disable-next-line no-console
                console.error(v);
                break;
        }
    }
    return true;
}

/**
 * Instantiates tooltips
 */
function initTooltips() {
    $('[data-toggle="tooltip"]').tooltip();
    $('.to-tooltip').tooltip();
}

/**
 * Redirect function
 * @param url
 */
function redirect(url) {
    window.location.href = url;
}

/**
 * Submits the search form
 */
// eslint-disable-next-line no-unused-vars
function submitSearch() {
    $('.search-box-wrapper').submit();
}

/**
 * Page reload function
 */
function reload() {
    return window.location.reload();
}

/**
 * Copy to clipboard function
 * @param textToCopy
 */
function copyToClipboard(textToCopy, container = 'body') {
    let $temp = $("<textarea>");
    $(container).append($temp);
    $temp.val(textToCopy).select();
    document.execCommand("copy");
    $temp.remove();
}

/**
 * Attaches scroll handlers & sticky behaviour to desired components
 * @param component
 * @param stickyClass
 */
function initStickyComponent(component, stickyClass) {
    let sticky = false;
    let top = $(window).scrollTop();
    if ($(".main-wrapper").offset().top < top) {
        $(component).addClass(stickyClass);
        // eslint-disable-next-line no-unused-vars
        sticky = true;
    } else {
        $(".side-menu, .suggestions-box").removeClass(stickyClass);
    }
}

/**
 * Go to login via UI redirect
 */
// eslint-disable-next-line no-unused-vars
function goToLogin() {
    redirect(app.baseUrl + '/login');
}

/**
 * Accepts adult content confirm dialog
 */
// eslint-disable-next-line no-unused-vars
function acceptSiteEntry() {
    setCookie('site_entry_approval', true, 90);
    $('#site-entry-approval-dialog').modal('hide');
}

/**
 * Set cookie
 * @param key
 * @param value
 * @param expiry
 */
function setCookie(key, value, expiry) {
    var expires = new Date();
    expires.setTime(expires.getTime() + (expiry * 24 * 60 * 60 * 1000));
    document.cookie = key + '=' + value + ';expires=' + expires.toUTCString() + ';path=/';
}

/**
 * Get cookie value
 * @param key
 * @returns {any}
 */
function getCookie(key) {
    var keyValue = document.cookie.match('(^|;) ?' + key + '=([^;]*)(;|$)');
    return keyValue ? keyValue[2] : null;
}

/**
 * Delete cookie
 * @param key
 */
// eslint-disable-next-line no-unused-vars
function eraseCookie(key) {
    var keyValue = getCookie(key);
    setCookie(key, keyValue, '-1');
}

/**
 * Reload themes on the fly
 */
// eslint-disable-next-line no-unused-vars
function reloadTheme() {
    let appTheme = 'css/bootstrap/bootstrap';
    let currentTheme = getCookie('app_theme');
    let currentRTLSetting = getCookie('app_rtl');
    if (currentRTLSetting === 'rtl') {
        appTheme += '.rtl';
    }

    if (currentTheme === 'dark') {
        appTheme += '.dark';
    }
    appTheme += ".css";
    $('#app-theme').attr('href', appTheme);
}

/**
 * Launches custom, stackable and dismisable toasts
 * @param type
 * @param title
 * @param message
 * @param subtitle
 */
function launchToast(type, title, message, subtitle = '') {
    $.toast({
        type: '',
        title: title,
        subtitle: subtitle,
        content: message,
        dismissible: true,
        indicator: {
            type: type
        },
        delay: 5000,
    });
}

/**
 * Opens up device share API or fallbacks to URL copy
 * @param url
 */
// eslint-disable-next-line no-unused-vars
function shareOrCopyLink(url = false) {
    if (url === false) {
        url = window.location.href;
    }
    if (navigator.share) {
        navigator.share({
            title: document.title,
            url: url
        })
            // eslint-disable-next-line no-console
            .then(() => console.log('Successful share'))
            // eslint-disable-next-line no-console
            .catch(error => console.log('Error sharing:', error));
    } else {
        copyToClipboard(url);
        launchToast('success', trans('Success'), trans('Link copied to clipboard') + '.', 'now');
    }
}

/**
 * Auto Adjusts textareas on resize
 * @param el
 */
// eslint-disable-next-line no-unused-vars
function textAreaAdjust(el) {
    el.style.height = (el.scrollHeight > el.clientHeight) ? (el.scrollHeight) + "px" : "45px";
}

/**
 * Filters up user received notifications ( via sockets )
 * @returns {string}
 */
// eslint-disable-next-line no-unused-vars
function getNotificationsActiveFilter() {
    let activeType = '';
    // get active filter if exists
    if (window.location.href.indexOf('/likes') >= 0) {
        activeType = '/likes';
    } else if (window.location.href.indexOf('/messages') >= 0) {
        activeType = '/messages';
    } else if (window.location.href.indexOf('/subscriptions') >= 0) {
        activeType = '/subscriptions';
    } else if (window.location.href.indexOf('/tips') >= 0) {
        activeType = '/tips';
    } else if (window.location.href.indexOf('/promos') >= 0) {
        activeType = '/promos';
    }

    return activeType;
}

/**
 * Method used for translating locale strings
 * @param key
 * @param replace
 * @returns {T|*}
 */
// eslint-disable-next-line no-unused-vars
function trans(key, replace = {}) {
    let translation = window.translations[key];
    if (translation === null || typeof translation === 'undefined') { // If no translation available, return the ( default - en ) key
        return key;
    }
    for (var placeholder in replace) {
        translation = translation.replace(`:${placeholder}`, replace[placeholder]);
    }
    if (typeof translation === 'undefined') {
        return key;
    }
    return translation;
}

/**
 * Method used for translating locale strings
 * Supports multiple choices translations
 * @param key
 * @param replace
 * @returns {T|*}
 */
// eslint-disable-next-line no-unused-vars
function trans_choice(key, count = 1, replace = {}) {
    let keyValue = window.translations[key];
    if (typeof keyValue === 'undefined') {
        return key;
    }
    const translations = keyValue.split('|');
    let translation = count > 1 || count === 0 ? translations[1] : translations[0];
    translation = translation.replace('[2,*]', '');

    for (var placeholder in replace) {
        translation = translation.replace(`:${placeholder}`, replace[placeholder]);
    }
    return translation;
}

/**
 * Updates button state, adding loading icon to it and disabling it
 * @param state
 * @param buttonElement
 */
// eslint-disable-next-line no-unused-vars
function updateButtonState(state, buttonElement, buttonContent = false, loadingColor = 'primary') {
    if (state === 'loaded') {
        if (buttonContent) {
            buttonElement.html(buttonContent);
        }
        else {
            buttonElement.html('<div class="d-flex justify-content-center align-items-center"><ion-icon name="paper-plane"></ion-icon></div>');
        }
        buttonElement.removeClass('disabled');
    }
    else {
        buttonElement.html(`<div class="d-flex justify-content-center align-items-center">
            <div class="spinner-border text-${loadingColor} spinner-border-sm" role="status">
            <span class="sr-only">${trans('Loading...')}</span>
            </div>
            ${(buttonContent !== false ? '<div class="ml-2">' + buttonContent + '</div>' : '')}
            </div>`);
        buttonElement.addClass('disabled');
    }
}

/**
 * Re-sends the user email verification
 * @param callback
 */
// eslint-disable-next-line no-unused-vars
function sendEmailConfirmation(callback = function () { }) {
    $('.unverified-email-box').attr('onClick', '');
    $.ajax({
        url: app.baseUrl + '/resendVerification',
        type: 'POST',
        success: function () {
            $('.unverified-email-box').fadeOut();
            launchToast('success', trans('Success'), trans('Confirmation email sent. Please check your inbox and spam folder.'), 'now');
            callback();
        },
        error: function () {

        }
    });
}

/**
 * Preps a data beacon data sample, to be saved before page unload
 * @returns {FormData}
 */
// eslint-disable-next-line no-unused-vars
function prepBeaconDataSample() {
    var fd = new FormData();
    fd.append('prevPage', PostsPaginator.currentPage);
    return fd;
}

/**
 * Returns current bootstrap breakpoint to the JS side
 * @returns {{name: (string|string), index: number}|null}
 */
// eslint-disable-next-line no-unused-vars
function bootstrapDetectBreakpoint() {
    // cache some values on first call
    let breakpointNames = ["xl", "lg", "md", "sm", "xs"];
    let breakpointValues = [];
    for (const breakpointName of breakpointNames) {
        breakpointValues[breakpointName] = window.getComputedStyle(document.documentElement).getPropertyValue('--breakpoint-' + breakpointName);
    }
    let i = breakpointNames.length;
    for (const breakpointName of breakpointNames) {
        i--;
        if (window.matchMedia("(min-width: " + breakpointValues[breakpointName] + ")").matches) {
            return { name: breakpointName, index: i };
        }
    }
    return null;
}

/**
 * Increments the notifications badge by 1 or adds it if it doesnt exist
 */
function incrementNotificationsCount(selector, value = 1) {
    if (parseInt($(selector).html()) + (value) > 0) {
        $(selector).removeClass('d-none');
        $(selector).html(parseInt($(selector).html()) + (value));
    }
    else {
        $(selector).addClass('d-none');
    }
}

/**
 * Checks if creator can post a PPV content within the limits
 */
function passesMinMaxPPVContentCreationLimits(price) {
    let hasError = false;
    if (parseInt(price) < app.min_ppv_content_price) {
        hasError = true;
    }
    if (parseInt(price) > app.max_ppv_content_price) {
        hasError = true;
    }
    if (price.length <= 0) {
        hasError = true;
    }
    return !hasError;
}

function showDialog(dialogID) {
    $('#' + dialogID).modal('show');
}

function hideDialog(dialogID) {
    $('#' + dialogID).modal('hide');
}

function getWebsiteFormattedAmount(amount) {
    let currencyPosition = app.currencyPosition;
    let currency = app.currencySymbol;

    return currencyPosition === 'left' ? currency + amount : amount + currency;
}


// js for dorpdown menu

document.addEventListener('DOMContentLoaded', function () {
    // Toggle dropdown on click
    document.querySelector('.dropdown-menu-header').addEventListener('click', function () {
        document.getElementById('myDropdown').style.display =
            (document.getElementById('myDropdown').style.display === 'block') ? 'none' : 'block';
    });

    // Close the dropdown if the user clicks outside of it
    window.addEventListener('click', function (event) {
        if (!event.target.matches('.dropdown-menu-header')) {
            var dropdown = document.getElementById('myDropdown');
            if (dropdown.style.display === 'block') {
                dropdown.style.display = 'none';
            }
        }
    });
});


// js for dropdown filter
$(document).ready(function () {
    $("#searchInput").on("keyup", function () {
        var value = $(this).val().toLowerCase();
        $("#myDropdown li").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});



document.addEventListener('DOMContentLoaded', function () {
    const accordionItems = document.querySelectorAll('.accordion-item');

    accordionItems.forEach(function (item) {
        const header = item.querySelector('.accordion-header');

        header.addEventListener('click', function () {
            item.classList.toggle('active');
            const content = item.querySelector('.accordion-content');
            if (item.classList.contains('active')) {
                content.style.display = 'block';
            } else {
                content.style.display = 'none';
            }
            header.classList.toggle('rotate-arrow'); // Toggle the rotate class
        });
    });
});



//   js for wiki and post tabs

document.addEventListener('DOMContentLoaded', function () {

    var lastSelectedContent = localStorage.getItem('lastSelectedContent');

    var defaultContentType = lastSelectedContent || 'post';

    showContent(defaultContentType);
});

function showContent(contentType) {
    var postContent = document.querySelector('.content-post');
    var wikiContent = document.querySelector('.content-wiki');

    if (contentType === 'post') {
        postContent.style.display = 'block';
        wikiContent.style.display = 'none';
    } else if (contentType === 'wiki') {
        postContent.style.display = 'none';
        wikiContent.style.display = 'block';
    }

    localStorage.setItem('lastSelectedContent', contentType);
}

//   js for wiki and post tabs end



// js for intrest cocmunity
// document.getElementById('comunity_theameClick').addEventListener('click', function () {
//     // Get references to all elements with the 'side-bar-heading' class
//     var addThereDivs = document.querySelectorAll('.side-bar-heading');

//     // Loop through each element
//     addThereDivs.forEach(function (addThereDiv) {
//         // Check if the div already has the 'bg-white' class
//         if (addThereDiv.classList.contains('bg-white')) {
//             // If it has, remove the 'bg-white' class
//             addThereDiv.classList.remove('bg-white');
//         } else {
//             // If it doesn't have, add the 'bg-white' class
//             addThereDiv.classList.add('bg-white');
//         }
//     });
// });

function execCommand(command, value = null) {
    document.execCommand(command, false, value);
}



function openDialog() {
    document.getElementById("dialog-container").style.display = "flex";
}

function closeDialog() {
    document.getElementById("dialog-container").style.display = "none";
}


var selectBox = document.getElementById('mySelectBox');

selectBox.addEventListener('click', function (event) {
    var div = event.target;
    if (div.tagName === 'DIV') {
        div.classList.toggle('selectedIntrest');
    }
});

// js for profile nav

// js for profile nav
function showTab(tabId) {
    // Hide all tab content
    var tabContents = document.getElementsByClassName('profile-history-content');
    for (var i = 0; i < tabContents.length; i++) {
        tabContents[i].style.display = 'none';
    }

    // Display the selected tab content
    document.getElementById(tabId).style.display = 'block';
}

// Call the showTab function with the default tab ID at page load
document.addEventListener('DOMContentLoaded', function () {
    // Assuming 'defaultTab' is the ID of the tab you want to be active by default
    showTab('overview-history');
});

function toggleDropdownLayout() {
    var dropdown = document.querySelector('.dropdown');
    dropdown.classList.toggle('active');
}

function selectOption(iconName, clickedAnchor) {
    var defaultOption = document.getElementById('defaultOption');
    defaultOption.innerHTML = '<span class="material-symbols-outlined">' + iconName + '</span>';

 
    var allAnchors = document.querySelectorAll('.dropdown-content a');
    allAnchors.forEach(function (anchor) {
        anchor.classList.remove('active');
    });

 
    clickedAnchor.classList.add('active');

   
    let section = document.querySelector('#top-wrapper-section');

    
    section.classList.remove('card-layout', 'classic-layout', 'compact-layout');

 
    let card;
    if (iconName === 'bottom_sheets') {
        card = 'card-layout';
    } else if (iconName === 'density_medium') {
        card = 'classic-layout';
    } else if (iconName === 'density_small') {
        card = 'compact-layout';
    }

   
    section.classList.add(card);

    var dropdown = document.querySelector('.dropdown');
    dropdown.classList.remove('active');

    document.querySelector('.dropdown-content').addEventListener('click', function (event) {
        event.stopPropagation();
    });
}

    // document.cookie = "name=Pankaj; expires=Sun, 10 Feb 2024 12:00:00 UTC; path=/";

    //     // Get a cookie
    //     function getCookie(name) {
    //         const cookieString = document.cookie;
    //         const cookies = cookieString.split('; ');
    //         for (let i = 0; i < cookies.length; i++) {
    //             const cookie = cookies[i].split('=');
    //             if (cookie[0] === name) {
    //                 return cookie[1];
    //             }
    //         }
    //         return null;
    //     }

    //     const myCookie = getCookie('name');
    //     console.log(myCookie); // Output: Pankaj

    //     // Delete a cookie
    //     function deleteCookie(name) {
    //         document.cookie = `${name}=; expires=Thu, 01 Jan 1970 00:00:00 UTC; path=/;`;
    //     }

    //     deleteCookie('name');


