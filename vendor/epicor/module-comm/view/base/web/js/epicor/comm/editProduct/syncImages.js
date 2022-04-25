/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if(typeof Epicor == 'undefined') {
    var Epicor = {};
}

require([
    'jquery',
    'prototype'
], function (jQuery) {

    Epicor.msynchimage = Class.create();
    Epicor.msynchimage.prototype = {

        initialize: function(){
           
        },
        /*
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
      */
        syncFtpImages: function(syncFtpImageUrl) {
        //    $('messages').update();
            this.ajaxRequest = new Ajax.Request(syncFtpImageUrl,{
                method: 'get',
                onComplete: function(request){
                    this.ajaxRequest = false;
                }.bind(this),
                onSuccess: function(request){
                    /*var json = request.responseText.evalJSON();
                    if(!json.error)
                        $(json.replace).update(json.html);
                    */
                    if (request.responseText=='true'){
                        alert("Ftp images Sync Sucessful");
                        //window.location.self ='';// syncFtpSuccessUrl;
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
        
        syncRelatedDocs: function() {
            this.ajaxRequest = new Ajax.Request(syncRelatedDocUrl,{
                method: 'get',
                onComplete: function(request){
                    this.ajaxRequest = false;
                }.bind(this),
                onSuccess: function(request){
                    if (request.responseText=='true'){
                        alert("Related Douments Sync Sucessful");
                        location.reload();
                    }
                    else
                    {
                        alert("Related Douments Sync Failed: \n".request.responseText);
                    }
                }.bind(this),
                onFailure: function(request){
                     alert("Related Douments Sync Failed: \n".request.responseText);
                }.bind(this),
                onException: function(request,e){
                    alert("Related Douments Sync Failed: \n".request.responseText);
                      this.displayMsg();
                }.bind(this)
            });
        }
       
    };

    var msynchimage = 'test';
    document.observe('dom:loaded', function() { 
        msynchimage = new Epicor.msynchimage();
        window.msynchimage = msynchimage;
         /*
         $('media_gallery_content').after('<button id="" title="Sync Images" type="button" class="scalable" onclick="msynchimage.syncFtpImages()" style=""><span><span><span>Sync Images</span></span></span></button>');
        */
    });
});

// below Old JS code
/*
document.observe('dom:loaded', function() {
    if ($('media_gallery_content_grid')) {
        $('media_gallery_content_grid').select('div.buttons')[0].insert({after: '<button id="" title="Sync Images" type="button" class="scalable" onclick="syncFtpImages()" style=""><span><span><span>Sync Images</span></span></span></button>'}
        );
    }
});

function syncFtpImages()
{
    new Ajax.Request(syncFtpImageUrl, {
            method:     'get',
            onSuccess: function(transport){
                if (transport.responseText=='true'){
                    alert("Ftp images Sync Sucessful");
                   window.location = syncFtpSuccessUrl;
                   
                }
                else
                {
                    alert("Image Sync Failed: \n".transport.responseText);
                }
            }
        });
}
*/