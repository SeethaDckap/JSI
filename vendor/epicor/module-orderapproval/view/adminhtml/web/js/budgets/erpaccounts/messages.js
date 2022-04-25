/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    "jquery",
    'jquery/ui',
], function ($) {
    return {
        insertMessage: function (message, type) {
            if ($('#anchor-content #messages').length) {
                $('#anchor-content #messages').each(function () {
                    $('#anchor-content #messages').remove();
                });
            }
            var messageType = '';
            switch (type) {
                case 'success-msg':
                    messageType = 'message-success success';
                    break;
                case 'error-msg':
                    messageType = 'message-error error';
                    break;
                case 'warning-msg':
                    messageType = 'message-warning warning'
                    break;
            }

            jQuery('<div id="messages">' +
                '<div class="messages">' +
                '<div class="message ' + messageType + '">' +
                '<div data-ui-id ="' + messageType + '">' +
                message + '</div></div></div></div>').insertAfter('.page-main-actions');
        },
        clearMessages: function(){
            $('div#messages').remove();
            $('a.budget-information-tab').removeClass('_changed _error')
        }
    }
});