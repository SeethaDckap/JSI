/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define(
    ['jquery',
    'Magento_Checkout/js/view/payment/default',
    'Epicor_Elements/js/action/set-payment-method',
    'Epicor_Elements/js/action/set-billing-address',
    'Magento_Checkout/js/model/payment/additional-validators',
    'Magento_Ui/js/modal/modal',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/full-screen-loader',
    'iframeElementsSetup',
    'mage/url',
    'jquery/ui',
    'mage/mage',
    ],
    function($, Component, setPaymentMethodAction, setBillingAddressAction, additionalValidators, modal, customerData, quote, fullScreenLoader, iframeElementsSetup, url) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Epicor_Elements/payment/elements',
            },
            /** Redirect to Elements */
            continueToElements: function() {
                var self = this;
                var archeckout = false;
                if(window.location.href.indexOf("archeckout") > -1) {
                    self.continueToArpaymentElements();
                } else {
                    if (additionalValidators.validate()) {

                                //update payment method information if additional data was changed
                                self.selectPaymentMethod();
                                //We need to save the billing address before proceeding to the payment gateway(before place order)
                                //Because we are showing the payment gateway in a iframe. So we need to update the quote
                                //before redirecting to the payment gateway
                                setBillingAddressAction(self.messageContainer).done(
                                    function() {
                                        self.showFullScreenloader();
                                        customerData.invalidate(['cart']);
                                        self.checkElementExist();
                                        self.getIframeUrl();
                                    }
                                 );

                                return this;
                            }
                    }
                    },
            /** Redirect to Elements */
            continueToArpaymentElements: function() {
                if (additionalValidators.validate()) {
                    var self = this;
                            //update payment method information if additional data was changed
                            self.selectPaymentMethod();
                            self.showFullScreenloader();
                            customerData.invalidate(['cart']);
                            self.checkElementExist();
                            self.getIframeUrl();
                            return this;
                        }
                    },
                    getMailingAddress: function() {
                        return window.checkoutConfig.payment.checkmo.mailingAddress;
                    },
                    getInstruction: function() {
                        return window.checkoutConfig.payment.elements.instructions;
                    },
                    getIdCode: function() {
                        return 'payment_elements';
                    },
                    showFullScreenloader: function() {
                        fullScreenLoader.startLoader();
                    },
                    hideFullScreenloader: function() {
                        fullScreenLoader.stopLoader();
                    },
                    checkElementExist: function() {
                        if ($('#elementsFrame').length) {
                            $('#elementsFrame').remove();
                        }
                    },
                    openElementsPopup: function() {
                        var options = {
                            type: 'popup',
                            responsive: true,
                            innerScroll: true,
                            modalClass: 'elementspopup',
                   // title: 'Elements Payment'
               };
               var popup = modal(options, $('#elements-iframe-popup-modal'));
               $('#elements-iframe-popup-modal').modal('openModal');
               $('.modal-footer').hide();
           },
           showElementsError: function(msg) {
            this.hideFullScreenloader();
            alert(msg);
        },
        getIframeUrl: function() {
            var iframeself = this;
            iframeself.showFullScreenloader();

            let isMobile = 0;
            if (screen.width <= 1024) {
                isMobile = 1;
            }

            let captcha_string  = "";
            let captcha_element = $('.payment-method._active input[name="captcha_string"]');
            if (captcha_element !== undefined) {
                if (captcha_element.val() !== undefined && captcha_element.val() != "") {
                    captcha_string = captcha_element.val();
                }
            }

            let postData = {
                'isMobile': isMobile,
                'captcha_string': captcha_string
            }
            $.ajax({
                    showLoader: false,
                    url: url.build('elements/payment/Opcsavereview'),
                    type: "POST",
                    dataType: 'json',
                    data: postData
            }).done(function(data) {
                if ($('.payment-method._active .captcha-reload') !== undefined) {
                    $('.payment-method._active .captcha-reload').trigger("click");
                }
                var obj = $.parseJSON(data);
                var getSuccess = obj.setupSuccess;
                fullScreenLoader.stopLoader();
                if(getSuccess =="Y") {
                    if(!obj.debug.tokenInfo.error) {
                        iframeself.openElementsPopup();
                        var ifr=$('<iframe/>', {
                         src: obj.transactionSetupUrl,
                         id:  'elementsFrame',
                         style:'width:600px;height:614px;display:block;border:none;',
                         load:function(){
                            iframeself.hideFullScreenloader();
                        }
                    });
                        $('#show-elements-iframe').append(ifr);
                        return true;
                    } else {
                     iframeself.showElementsError(obj.debug.tokenInfo.error);
                 }
             } else if(getSuccess =="C") {
                    iframeself.showElementsError("Incorrect Captcha");
             } else {
                    iframeself.showElementsError("Error occured while setting up Card payment.Please use another payment method");
            }
        });
        },
        isCaptchaEnabled: function() {
                return window.checkoutConfig.element-payment-form.isRequired;
        }
    });
    }
    );
