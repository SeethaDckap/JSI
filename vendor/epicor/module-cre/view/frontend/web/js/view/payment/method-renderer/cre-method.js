/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define(
    ['jquery',
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Ui/js/modal/alert',
        'Magento_Checkout/js/model/payment/additional-validators'
    ],
    function ($, Component, fullScreenLoader, alert, additionalValidators) {
        var error_alert_params = {
            title: 'Payment Error',
            content: 'An error occurred while attempting to verify payment information. '
        };
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Epicor_Cre/payment/cre',
                container: 'payment_form_cre',
                cre: null
            },
            getMailingAddress: function () {
                return window.checkoutConfig.payment.checkmo.mailingAddress;
            },
            sdkUrl: function () {
                var self = this;
                var configValues = window.checkoutConfig.payment[self.getCode()];
                return configValues.sdkUrl;

            },
            getInstruction: function() {
                return window.checkoutConfig.payment.cre.instructions;
            },
            getCrePaymentInfo: function () {
                var self = this;
                var configValues = window.checkoutConfig.payment[self.getCode()];
                return configValues.data.configurations;

            },
            getCrePaymentUrl: function () {
                var self = this;
                var configValues = window.checkoutConfig.payment[self.getCode()];
                return configValues.data.urlAjax;
            },
            getAllowedCardList: function() {
                var self = this;
                var configValues = window.checkoutConfig.payment[self.getCode()];
                return configValues.data.allowedCardsLogo;
            },
            /**
             * Load external CRE SDK
             */
            loadEsdmScript: function () {
                var self = this;
                if (additionalValidators.validate()) {
                    fullScreenLoader.startLoader();
                    if (!self.sdkUrl()) {
                        fullScreenLoader.stopLoader();
                        var error_message = 'An error occurred while attempting to verify payment information. Please choose another payment method';
                        alert({
                            title: 'Payment Configuration Error',
                            content: error_message
                        });
                        return false;
                    } else {
                        try {
                            require([self.sdkUrl()], function (cre) {
                                self.cre = cre;
                                fullScreenLoader.startLoader();
                                self.initCre();
                            });
                        } catch (e) {
                            var error_message = 'An error occurred while attempting to verify payment information. Please choose another payment method';
                            alert({
                                title: 'Payment Configuration Error',
                                content: error_message
                            });
                            return false;
                        }
                    }
                }
            },
            placeCreOrder: function () {
                this.placeOrder.bind(this);
            },
            /**
             * Setup CRE SDK
             */
            initCre: function () {
                var self = this;
                try {
                    fullScreenLoader.stopLoader();
                    var getCrePaymentInfo = self.getCrePaymentInfo();
                    var paymentHandler = HostedPayment.setup({
                        response: function (response) {
                            var stringify = JSON.stringify(response);
                            var getCreResponse = stringify.success;
                            if (response.success == true) {
                                fullScreenLoader.startLoader();
                                var JsonParams = JSON.stringify(response);
                                var placeOrder = self.sendCreAjax(JsonParams, submit_order);
                            } else {
                                fullScreenLoader.stopLoader();
                                var error_message = response.error + '. Please choose another payment method';
                                alert({
                                    title: 'Payment Gateway Error',
                                    content: error_message
                                });
                            }
                        },
                        service_uri: getCrePaymentInfo.short_url
                    });

                    var submit_order = this.placeOrder.bind(this);
                    let allowedCardTypesAsCre = [];
                    const eccCardMapWithCre = JSON.parse(getCrePaymentInfo.ecc_cre_card_type_map);
                    const allowedCardTypes = getCrePaymentInfo.cctypes.match(/(".*?"|[^",\s]+)(?=\s*,|\s*$)/g);
                    if (allowedCardTypes) {
                        allowedCardTypesAsCre = allowedCardTypes.map(function addOne(cardTypes) {
                            return eccCardMapWithCre[cardTypes];
                        });
                    }
                    paymentHandler.open({
                        public_key: getCrePaymentInfo.public_key,
                        namespace: getCrePaymentInfo.namespace,
                        name: (getCrePaymentInfo.payment_title) ? getCrePaymentInfo.payment_title : 'Epicor Retail Cloud',
                        button: (getCrePaymentInfo.button_name) ? getCrePaymentInfo.button_name : 'Purchase',
                        card_type_config: {
                            allowed_payment_card_types: allowedCardTypesAsCre
                        },
                        lang: '',
                        style_config: {
                            body_background: (getCrePaymentInfo.body_background) ? getCrePaymentInfo.body_background : "#fffff",
                            header_border: '1px solid green',
                            control_font: 'verdana',
                            control_font_size: '15px',
                            control_font_color: '#000',
                            control_background: '#fff',
                            btn_font: 'verdana',
                            btn_font_size: '18px',
                            btn_font_color: (getCrePaymentInfo.font_color) ? getCrePaymentInfo.font_color : '#fff',
                            btn_font_background: (getCrePaymentInfo.font_background) ? getCrePaymentInfo.font_background : '#5cb85c'
                        },
                        translation_config: {
                            ccnum_lbl: "Card Number",
                            ccnum_plh: "Enter card number",
                            expiry_lbl: "Expiration",
                            expiry_plh: "MMYY",
                            cvv_lbl: "CVV/CVC",
                            cvv_phl: 'CVV',
                            expiry_err1: "Expiration Date Required",
                            ccnum_err: "Please specify a valid credit card number.",
                            cvv_err: "Invalid CVV/CVC."
                        }
                    });
                    setTimeout(function () {
                        fullScreenLoader.stopLoader();
                    }, 4000);
                    window.addEventListener('popstate', function () {
                        paymentHandler.close();
                    });
                } catch (e) {
                    alert({
                        title: 'Payment Configuration Error',
                        content: 'An error occurred while attempting to verify payment information. Please choose another payment method'
                    });
                    fullScreenLoader.stopLoader();
                }
            },
            sendCreAjax: function (data_to_post, submit_order) {
                var self = this;
                var post_url = self.getCrePaymentUrl();
                $.ajax(post_url, {
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        payment: data_to_post
                    },
                    success: function (response) {
                        var obj = response;
                        var errorStep = obj.errorStep;
                        var skipError = obj.skipError;
                        var errorMsg = obj.errorMsg;
                        if (errorStep != undefined && skipError == undefined) {
                            fullScreenLoader.stopLoader();
                            var error_message = errorStep + " failed" + errorMsg + ' Please try again or choose another payment method';
                            alert({
                                title: error_alert_params.title,
                                content: error_alert_params.content + $.mage.__(error_message)
                            });
                            return false;
                        } else {
                            submit_order();
                            fullScreenLoader.stopLoader();
                        }
                    },
                    error: function (jqXHR) {
                        fullScreenLoader.stopLoader();
                        var error_message;
                        error_message = jqXHR + 'Please try again or choose another payment method';
                        alert({
                            title: error_alert_params.title,
                            content: error_alert_params.content + $.mage.__(error_message)
                        });
                    }
                });
            },
            getCode: function () {
                return 'cre';
            },
        });
    }
);
