define([
        'jquery',
        'Magento_Ui/js/modal/modal'
    ], function ($, modal) {
        'use strict';

        return {
            emailPopUp: $('.email-massaction-popup'),
            modalOption: {
                type: 'popup',
                modalClass: 'modal-popup',
                responsive: true,
                innerScroll: true,
                clickableOverlay: true,
                title: 'Sender Information',
                buttons: [],
            },
            showPopup: function (entity_document = null) {
                var dateTime = this.getCurrDateTime();
                var url = window.location.href;
                var msgEl = $('#email-massaction-popup-form textarea[name="email-msg"]');
                var subEl =  $('#email-massaction-popup-form input[name="email-subject"]');
                msgEl.val("Document Request For "+entity_document+", "+dateTime);
                if(url.indexOf('customerconnect/dashboard') !== -1){
                    var oldMsgVal = msgEl.val();
                    if(entity_document == 'Order'){
                        var newMsgValO = oldMsgVal.replace('Invoice', 'Order');
                        msgEl.val(newMsgValO);
                    }else if(entity_document == 'Invoice'){
                        var newMsgValI = oldMsgVal.replace('Order', 'Invoice');
                        msgEl.val(newMsgValI);
                    }
                }
                subEl.val("Document Request For "+entity_document+", "+dateTime);
                if(url.indexOf('customerconnect/dashboard') !== -1){
                    var oldMsgVal = subEl.val();
                    if(entity_document == 'Order'){
                        var newMsgValO = oldMsgVal.replace('Invoice', 'Order');
                        subEl.val(newMsgValO);
                    }else if(entity_document == 'Invoice'){
                        var newMsgValI = oldMsgVal.replace('Order', 'Invoice');
                        subEl.val(newMsgValI);
                    }
                }
                modal(this.modalOption, this.emailPopUp);
                this.emailPopUp.modal('openModal');
            },
            closePopup: function () {
                $('.email-massaction-popup').modal('closeModal');
            },
            getCurrDateTime: function () {
                const date = new Date();
                return date.toLocaleDateString('en-GB', {
                    day: 'numeric', month: 'numeric', year: 'numeric'
                }).replace(/ /g, '/');
            }
        };
    }
);
