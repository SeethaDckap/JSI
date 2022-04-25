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

    Epicor.quote = Class.create();
    Epicor.quote.prototype = {

        initialize: function(){

        },
        save: function(url) {   
           // $('messages').update();

            var is_global = 'false';
            if($('quote_is_global')) {
                var is_global = $('quote_is_global').checked;
                if($('quote_is_global').type == 'hidden') {
                    is_global = $('quote_is_global').value;
                }
            }
            if($('send_reminders') == undefined){
                sendReminders = null; 
                sendComments = null; 
                sendUpdates = null;  
            }else{
                sendReminders = $('send_reminders').checked;
                sendComments  = $('send_comments').checked;
                sendUpdates  = $('send_updates').checked;            
            }
            this.ajaxRequest = new Ajax.Request(url,{
                method: 'post',
                parameters: {
                    qtys: this.getData('.qty'),
                    prices: this.getData('.price'),
                    send_reminders: sendReminders,
                    send_comments : sendComments,
                    send_updates  : sendUpdates,
                    is_global: is_global,
                    note: $('note').value
                },
                onComplete: function(request){
                    this.ajaxRequest = false;
                }.bind(this),
                onSuccess: function(request){
                    var json = request.responseText.evalJSON();
                    if(json.error || typeof json.redirectUrl == 'undefined')
                        this.displayMsg(json.errorMsg);
                    else
                        window.location = json.redirectUrl;
                }.bind(this),
                onFailure: function(request){
                    this.displayMsg();
                }.bind(this),
                onException: function(request,e){
                    this.displayMsg();
                }.bind(this)
            });
        },
        displayMsg: function(msq, error) {
          jQuery('.loading-mask').hide();
            var msgclass = 'error-msg';
            if(msq == null)
                msq = 'Error occured while updating the quote';

            if(error == false)
                msgclass = 'success-msg';

            //$('messages').update('<ul class="messages"><li class="'+msgclass+'"><ul><li><span>'+msq+'</span></li></ul></li></ul>');
            alert(msq);
        },
        getData: function(type) {
            var dataArray = {};
            $('product-lines').select(type).each(function(item) {
                dataArray[item.name] = item.value;
            });
            return Object.toJSON(dataArray);
        },
        updateTotals: function(url) {
          //  $('messages').update();
            this.ajaxRequest = new Ajax.Request(url,{
                method: 'post',
                parameters: {
                    qtys: this.getData('.qty'),
                    prices: this.getData('.price')
                },
                onComplete: function(request){
                    this.ajaxRequest = false;
                }.bind(this),
                onSuccess: function(request){
                    var json = request.responseText.evalJSON();
                    if(json.error)
                        this.displayMsg(json.errorMsg);
                    else {
                        for(id in json.products) {
                            $('product-'+id).select('.rowtotal')[0].update(json.products[id]);
                        }
                        $('subtotal').update(json.subtotal);
                        $('taxTotal').update(json.taxTotal);
                        $('grandTotal').update(json.grandTotal);
                    }
                }.bind(this),
                onFailure: function(request){
                    this.displayMsg();
                }.bind(this),
                onException: function(request,e){
                    this.displayMsg();
                }.bind(this)
            });
        },
        accept: function(url) {
            this.save(url);

        },
        submitNewComment: function(url) {
         //   $('messages').update();
            this.ajaxRequest = new Ajax.Request(url,{
                method: 'post',
                parameters: {
                    note: $('note').value,
                    state: $('note_state').value
                },
                onComplete: function(request){
                    this.ajaxRequest = false;
                }.bind(this),
                onSuccess: function(request){
                    var json = request.responseText.evalJSON();
                    if(!json.error)
                        $(json.replace).update(json.html);

                    this.displayMsg(json.message, json.error);
                }.bind(this),
                onFailure: function(request){
                    this.displayMsg();
                }.bind(this),
                onException: function(request,e){
                    this.displayMsg();
                }.bind(this)
            });
        },
        publishComment: function(url) {
        //    $('messages').update();
            this.ajaxRequest = new Ajax.Request(url,{
                method: 'post',
                onComplete: function(request){
                    this.ajaxRequest = false;
                }.bind(this),
                onSuccess: function(request){
                    var json = request.responseText.evalJSON();
                    if(!json.error)
                        $(json.replace).update(json.html);

                    this.displayMsg(json.message, json.error);
                }.bind(this),
                onFailure: function(request){
                    this.displayMsg();
                }.bind(this),
                onException: function(request,e){
                    this.displayMsg();
                }.bind(this)
            });
        },
        changeCommentState: function(url, state) {
         //  $('messages').update();
            this.ajaxRequest = new Ajax.Request(url,{
                method: 'post',
                parameters: {
                    state: state
                },
                onComplete: function(request){
                    this.ajaxRequest = false;
                }.bind(this),
                onSuccess: function(request){
                    var json = request.responseText.evalJSON();
                    if(!json.error)
                        $(json.replace).update(json.html);

                    this.displayMsg(json.message, json.error);
                }.bind(this),
                onFailure: function(request){
                    this.displayMsg();
                }.bind(this),
                onException: function(request,e){
                    this.displayMsg();
                }.bind(this)
            });
        }
    };

    var quoteform = 'test';
    document.observe('dom:loaded', function() { 
        quoteform = new Epicor.quote();
        window.quoteform = quoteform;
    });
});