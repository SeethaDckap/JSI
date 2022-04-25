/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

document.observe('dom:loaded', function(){
    $('name').observe('change', function() {       
        name = this.value;
        baseurl = location.protocol + "//" + location.hostname;
        url = baseurl + '/admin/epicorcomm_mapping_remotelinks/setremotelink';				
        url = url + (url.match(new RegExp('\\?')) ? '&isAjax=true' : '?isAjax=true');
        this.ajaxRequest = new Ajax.Request(url,{
            method: 'post',
            parameters: {'name':name},
            onComplete: function(request){
                Variables.resetData();
                MagentovariablePlugin.variables = null;
            }.bind(this)
         });
    }); 
});