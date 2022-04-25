/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function resendReturn() {
    jQuery('.loading-mask').show();

    var url = $('resend_url').value;
    var data = {'return_id': $('return_id').value}
    new Ajax.Request(url, {
        method: 'post',
        parameters: data,
        onSuccess: function (transport) {
            alert(transport.responseText);
            document.location.reload(true);
        }
    });
}

function updateReturn() {
    jQuery('.loading-mask').show();

    var url = $('update_url').value;
    var data = {'return_id': $('return_id').value}

    new Ajax.Request(url, {
        method: 'post',
        parameters: data,
        onSuccess: function (transport) {
            alert(transport.responseText);
            document.location.reload(true);
        }
    });
}
