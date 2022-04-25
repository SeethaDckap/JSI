/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*browser:true*/
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/quote',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/url-builder',
        'mage/storage',
        'Epicor_Pay/js/form-builder',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/translate'
    ],
    function ($, Component, quote, customer, urlBuilder, storage, formBuilder, errorProcessor, fullScreenLoader, $t) {
        return Component.extend({

            defaults: {
                template: 'Epicor_Pay/payment/form',
                redirectAfterPlaceOrder: false
            },
            /** Open window with  */
            showAcceptanceWindow: function(data, event) {
                window.open(
                    $(event.target).attr('href'),
                    'olcwhatispaypal',
                    'toolbar=no, location=no,' +
                    ' directories=no, status=no,' +
                    ' menubar=no, scrollbars=yes,' +
                    ' resizable=yes, ,left=0,' +
                    ' top=0, width=400, height=350'
                );
                return false;
            },
            afterPlaceOrder: function () {
                var self = this;
                /*$.get(self.getUrl())
                 .done(function(response) {
                 formBuilder.build(response).submit();
                 }).fail(function (response) {
                 errorProcessor.process(response, self.messageContainer);
                 fullScreenLoader.stopLoader();
                 });*/
                window.location.replace(self.getUrl());
            },
            validate: function () {
                if (window.checkoutConfig.payment[this.getCode()].poMandatory) {
                    var self = this;
                    var element = $('.payment-method._active input[name="epmpo"]');

                    if ($('#ponerror').length) {
                        $("span").remove("#ponerror");
                    }

                    if(!element.val()){
                        element.filter(":visible").focus();
                        message = $t('This field is required.');
                        if (!$('#ponerror').length) {
                            var errorElement = '<div id="ponerror" class="mage-error" generated="true">' + message + '</div>';
                            element.after(errorElement);
                        }
                        return false;
                    }
                }
                if (window.checkoutConfig.payment[this.getCode()].poVisibility) {
                    var self = this;
                    var element = $('.payment-method._active input[name="epmpo"]');

                    var poMin = 1;
                    var poMax = window.checkoutConfig.payment[this.getCode()].poMaxLength;

                    if ($('#ponerror').length) {
                        $("div").remove("#ponerror");
                    }

                    if(element.val()){
                        element.filter(":visible").focus();
                        message = $t('Length should be between ' + poMin + ' and ' + poMax);
                        if ((element.val().length < poMin) || (element.val().length > poMax) ) {
                            var errorElement = '<div id="ponerror" class="mage-error" generated="true">' + message + '</div>';
                            element.after(errorElement);
                            return false;
                        }
                    }
                }

                return true;
            },
            getCode: function () {
                return 'pay';
            },
            getMessage: function () {
                return window.checkoutConfig.payment[this.getCode()].message;
            },
            getUrl: function () {
                return window.checkoutConfig.payment[this.getCode()].redirectUrl;
            },
            getPoTitle: function () {
                return window.checkoutConfig.payment[this.getCode()].poTitle;
            },
            isPoVisible: function () {
                return window.checkoutConfig.payment[this.getCode()].poVisibility;
            },
            isPoMandatory: function () {
                return window.checkoutConfig.payment[this.getCode()].poMandatory;
            },
            getPoValue: function () {
                return $('[name="ecc_customer_order_ref"]').val();
            },
            getPoClass: function () {
                if (window.checkoutConfig.payment[this.getCode()].poMandatory) {
                    return 'required';
                }
                return '';
            },
            isPoDisable: function () {
                if($('[name="ecc_customer_order_ref"]').val() != '') {
                    return true;
                }
                return false;
            },
            poMaxText: function () {
                var maxLen = window.checkoutConfig.payment[this.getCode()].poMaxLength;
                $msg = ''
                if (maxLen == '255') {

                } else {
                    $msg = '(Max ' + maxLen + ' chars)';
                }
                return $msg;
            },
            poMaxLength: function () {
                return window.checkoutConfig.payment[this.getCode()].poMaxLength;
            }
        });
    }
);
