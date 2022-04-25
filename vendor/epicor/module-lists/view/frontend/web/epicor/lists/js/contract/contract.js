/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

 document.observe('dom:loaded', function() {

    if ($('contract_default')) {
            var contract_default = document.getElementById("contract_default");
            var getcontractDefault = contract_default.options[contract_default.selectedIndex].value;
            $('loading-mask').show();
            contractListChange(getcontractDefault);        

    }

    $('contract_default').observe('change',function(e){
             var action_value = this[this.selectedIndex].value;
             var append_address = document.getElementById('appendaddress');
             append_address.show(); 
             $('loading-mask').show();
             contractListChange(action_value);
    });

    $('contract_filter').observe('change',function(e){
            var selectContracts = document.getElementById('contract_filter');
            for (i = 0; i < selectContracts.options.length; i++) {
                var currentOption = selectContracts.options[i];
                if (currentOption.selected && currentOption.value == '') {
                    for (var i=1; i<selectContracts.options.length; i++) {
                        selectContracts.options[i].selected = false;
                    }
                }
            }
    });     
    

 });   


function contractListChange(action_value) {
    var ajax_url = document.getElementById('ajax_url').value;
    var url = decodeURIComponent(ajax_url);
    this.ajaxRequest = new Ajax.Request(url, {
        method: 'post',
        parameters: {'id': action_value},
        onComplete: function(request) {
            this.ajaxRequest = false;
        }.bind(this),
        onSuccess: function(data) {
            var json = data.responseText.evalJSON();
            var theHTML = json.html;
            if (json.type == 'success') {
                var append_address = document.getElementById('appendaddress');
                append_address.innerHTML = theHTML;
                var contract_default = document.getElementById("contract_default");
                var getcontractDefault = contract_default.options[contract_default.selectedIndex].value; 
                if(getcontractDefault ==""){
                 $('ulcontract_default_address').hide();
                } else {
                 $('ulcontract_default_address').show();   
                }
                }
                $('loading-mask').hide();
        }.bind(this),
        onFailure: function(request) {
            alert('Error occured in Ajax Call');
        }.bind(this),
        onException: function(request, e) {
            alert(e);
        }.bind(this)
    });
} 