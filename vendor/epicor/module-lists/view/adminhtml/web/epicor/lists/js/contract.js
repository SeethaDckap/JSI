/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

function fetchAddressInList(reloadurl) {
  
    new Ajax.Request(reloadurl, {
        method: 'post',
        parameters: {'id': $('ecc_default_contract').value, 'customer_id': $('entity_id').value},
        onLoading: function (transport) {
        },
        onComplete: function (transport) {
            $('ecc_default_contract_address').update(transport.responseText);
        }
    });
}


function contractAddressChange(action_value) {
    var ajax_url = document.getElementById('ajax_url').value;
    var customer = document.getElementById('user_id').value;
    var url = decodeURIComponent(ajax_url);
    this.ajaxRequest = new Ajax.Request(url, {
        method: 'post',
        parameters: {'id': action_value, 'customerId': customer},
        onComplete: function (request) {
            this.ajaxRequest = false;
        }.bind(this),
        onSuccess: function (data) {
            var json = data.responseText.evalJSON();
            var theHTML = json.html;
            if (json.type == 'success') {
                var append_address = document.getElementById('appendcontractaddress');
                append_address.innerHTML = theHTML;
                $('loading-mask').hide();
            } else {
            }
        }.bind(this),
        onFailure: function (request) {
            alert('Error occured in Ajax Call');
        }.bind(this),
        onException: function (request, e) {
            alert(e);
        }.bind(this)
    });
} 