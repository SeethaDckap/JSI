/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

if(typeof Epicor == 'undefined') {
    var Epicor = {};
}


require([
    'jquery',
    'prototype'
], function (jQuery) {

    Epicor.managequote = Class.create();
    Epicor.managequote.prototype = {

        initialize: function(){

        },
        resubmit: function(url) {
            $('noteform').action = url;

            if($('note').value != '') 
                $('noteform').submit();
            else
                this.displayMsg('You can\' re-submit the quote without a new comment.');

        },
        displayMsg: function(msq, error) {
            var msgclass = 'error-msg';
            if(msq == null)
                msq = 'Error occured while updating the quote';

            if(error == false)
                msgclass = 'success-msg';

            //var html = '<ul class="messages"><li class="'+msgclass+'"><ul><li><span>'+msq+'</span></li></ul></li></ul>';
            alert(msq);
            /*
            if($('messages'))
                $('messages').update(html);
            else {
                var messageWrapper = new Element('div');
                messageWrapper.id = "messages";
                messageWrapper.update(html);
                $('quote-btns').insertBefore(messageWrapper, $('quote-btns').firstChild);
            } */
        }
    }

    var managequote;
    document.observe('dom:loaded', function() { 
        managequote = new Epicor.managequote();
          window.managequote = managequote;
    });

});