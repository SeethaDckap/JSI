/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

require([
    'jquery',
    'mage/url',
    'prototype'
], function (jQuery, urlBuilder) {
    jQuery(function() {
        // Orders New List Page / confirm changes page
        $$(".rfq_confirm").invoke('observe', 'click', function() {
            var rfqId = this.readAttribute('id').replace('rfq_confirm_', '');
            if (this.checked) {
                id = this.readAttribute('id').replace('confirm', 'reject');
                $(id).checked = false;
                $('rfq_' + rfqId + '_customer_reference_box').show();
            } else {
                $('rfq_' + rfqId + '_customer_reference_box').hide();
                $('rfq_' + rfqId + '_customer_reference').value = '';
            }
        });

        $$(".rfq_reject").invoke('observe', 'click', function() {
            if (this.checked) {
                id = this.readAttribute('id').replace('reject', 'confirm');
                $(id).checked = false;
                var rfqId = this.readAttribute('id').replace('rfq_reject_', '');
                $('rfq_' + rfqId + '_customer_reference_box').hide();
                $('rfq_' + rfqId + '_customer_reference').value = '';
            }
        });

        if ($('rfq_confirmreject_save')) {
            $('rfq_confirmreject_save').observe('click', function() {
                $('rfq_confirmreject').submit();
            });
        }
        
        if ($('claim_rfq_confirmreject_save')) {
            $('claim_rfq_confirmreject_save').observe('click', function() {
               dealerClaimQuotesUpdate();
            });
        }
        
        function dealerClaimQuotesUpdate(){
            var url = urlBuilder.build('dealerconnect/claims/rfqconfirmreject/');
            var dconfirm  = [];
            var dreject = [];
            var drfq =[];
            var all_rfqList = [];
           var message ='';
            jQuery('input:checkbox.rfq_confirm').each(function () {
                    if(this.checked){ 
                        dconfirm.push(jQuery(this).val());
                        drfq.push(jQuery(this).val());
                    }
            });
            
            jQuery('input:checkbox.rfq_reject').each(function () {
                    if(this.checked){
                        dreject.push(jQuery(this).val());
                       drfq.push(jQuery(this).val());
                    }
            });
            
            if(dconfirm.length > 0 || dreject.length > 0){
               var index =0; 
               for (index = 0; index < drfq.length; index++) {
                       var rfqvalues = []; 
                       rfqvalues.push({
                            quote_number: jQuery('input[name="rfq['+ drfq[index] +'][quote_number]"]').val(), 
                            quote_sequence:  jQuery('input[name="rfq['+ drfq[index] +'][quote_sequence]"]').val(),
                            recurring_quote:  jQuery('input[name="rfq['+ drfq[index] +'][recurring_quote]"]').val(),
                            amount:  jQuery('input[name="rfq['+ drfq[index] +'][amount]"]').val(),
                            customer_reference:  jQuery('input[name="rfq['+ drfq[index] +'][customer_reference]"]').val()
                        });
                     if(rfqvalues.length >0){
                         all_rfqList.push(rfqvalues);
                     }
                }
               // send Ajax request for Confirm& Reject claim RFQ's
                
                var formKey = jQuery.cookie("form_key");
                var postData = {'form_key':formKey, 'confirmed[]': dconfirm, 'rejected[]':dreject, 'rfq':JSON.stringify(all_rfqList) }

                   this.ajaxRequest = new Ajax.Request(url, {
                        method: 'post',
                        parameters: postData,
                        onComplete: function (request) {
                                this.ajaxRequest = false;
                        }.bind(this),
                        onSuccess: function (data) {
                                var result = data.responseText.evalJSON();
                                //window.parent.$('loading-mask').hide();
                                if (result.success)
                                {      
                                        jQuery(window).scrollTop(0);
                                        window.location.reload();
                                }else{
                                        window.parent.$('loading-mask').hide();
                                        message += '\n\n' + jQuery.mage.__('No RFQs selected, quotes updated Failed.');
                                        alert(message);
                                }
                        }.bind(this),
                        onFailure: function (request) {
                                window.parent.$('loading-mask').hide();
                                alert(jQuery.mage.__('Error occured in Ajax Call'));
                        }.bind(this),
                        onException: function (request, e) {
                                window.parent.$('loading-mask').hide();
                                console.log(e);
                                alert(e);
                        }.bind(this)
                });
                
            }else{
                alert('No RFQ selected.')
            }

        }
        
    });
});