/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    "jquery",
    'jquery/ui',
], function ($) {
    return {
        insertMessage: function (message, type) {
            let messageContainer = $('.page.messages');
            if(messageContainer.length > 0){
                messageContainer.html('<div class="message"><div></div></div>')
            }
            let messageType = '';
            switch (type) {
                case 'success-msg':
                    messageType = 'message success';
                    break;
                case 'error-msg':
                    messageType = 'message error';
                    break;
                case 'warning-msg':
                    messageType = 'message warning'
                    break;
            }

            let messageOuter = messageContainer.find('div.message')
            messageOuter.addClass(messageType);
            let messageInner = messageContainer.find('div.message > div')
            messageInner.html(message);
        },
        clearMessages: function(){
            $('div.page.messages div.message').remove();
        }
    }
});