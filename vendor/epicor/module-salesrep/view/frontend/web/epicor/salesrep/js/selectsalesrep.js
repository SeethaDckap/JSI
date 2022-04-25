/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


function selectMasquerade(masquerade_as) {
    var postVal = masquerade_as.getAttribute('url_id');
    var return_url = document.getElementById('return_url').value;
    var ajax_url = document.getElementById('ajax_url').value;
    var url = decodeURIComponent(ajax_url);
    var returns = document.getElementById('jreturn_url').value;    
    this.ajaxRequest = new Ajax.Request(url, {
        method: 'post',
        showLoader: true,
        parameters: {'masquerade_as': postVal,'return_url' :return_url ,'isAjax':'true' },
        onComplete: function(request) {
            this.ajaxRequest = false;
        }.bind(this),
        onSuccess: function(data) {
            var json = data.responseText.evalJSON();
            var theHTML = json.html;
            if (json.type == 'success') {              
              window.location.href = returns; 
            }
        }.bind(this),
        onFailure: function(request) {
            alert('Error occured in Ajax Call');
        }.bind(this),
        onException: function(request, e) {
            alert(e);
        }.bind(this)
    });
} 