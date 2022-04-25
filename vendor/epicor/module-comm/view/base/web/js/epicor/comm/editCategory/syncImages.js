/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*
function syncFtpImages()
{
    new Ajax.Request(syncFtpImageUrl, {
        method: 'get',
        onSuccess: function (transport) {
            if (transport.responseText == 'true') {
                alert("Ftp images Sync Sucessful");
                var concatSymbol = syncFtpSuccessUrl.indexOf('?') > -1 ? '&' : '?';
                categoryReset(syncFtpSuccessUrl + concatSymbol + 'isAjax=true', true);
            } else {
                alert("Image Sync Failed: \n".transport.responseText);
            }
        }
    });
}
*/
/* above code is outdated*/

if(typeof Epicor == 'undefined') {
    var Epicor = {};
}

require([
    'jquery',
    'prototype'
], function (jQuery) {

    Epicor.catynchimage = Class.create();
    Epicor.catynchimage.prototype = {

        initialize: function(){
           
        },
        
        syncFtpImages: function(syncFtpImageUrl) {
            this.ajaxRequest = new Ajax.Request(syncFtpImageUrl,{
                method: 'get',
                onComplete: function(request){
                    this.ajaxRequest = false;
                }.bind(this),
                onSuccess: function(request){
                    if (request.responseText=='true'){
                        alert("Ftp images Sync Sucessful");
                        location.reload();
                    }
                    else
                    {
                        alert("Image Sync Failed: \n".request.responseText);
                    }
                }.bind(this),
                onFailure: function(request){
                     alert("Image Sync Failed: \n".request.responseText);
                }.bind(this),
                onException: function(request,e){
                    alert("Image Sync Failed: \n".request.responseText);
                      this.displayMsg();
                }.bind(this)
            });
        },
       
    };

    var catynchimage = 'test';
    document.observe('dom:loaded', function() { 
        catynchimage = new Epicor.catynchimage();
        window.catynchimage = catynchimage;
    });
});
