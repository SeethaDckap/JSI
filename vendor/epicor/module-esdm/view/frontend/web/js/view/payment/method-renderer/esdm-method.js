/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define(
    [
        'jquery',
        'Magento_Payment/js/view/payment/cc-form',
        'Magento_Checkout/js/model/quote',
        'Epicor_Esdm/js/esdm-request',
        'Magento_Ui/js/modal/alert',
        'Magento_Checkout/js/model/full-screen-loader',
        'ko'
    ],
    function($, Component, quote, esdmRequest, alert, fullScreenLoader, ko) {
        'use strict';
        return Component.extend({
            defaults: {
                template: 'Epicor_Esdm/payment/esdm'
            },
            getCode: function() {
                return esdmRequest.method_code;
            },
            context: function() {
                return this;
            },
            defaultEsdmSelect: ko.observable("newcard"),
            shouldShowEsdmSavedCards: ko.observable(false),
            placeOrderHandler: null,
            validateHandler: null,
            setPlaceOrderHandler: function(handler) {
                this.placeOrderHandler = handler;
            },

            setValidateHandler: function(handler) {
                this.validateHandler = handler;
            },

            toggleEsdmAddCard: function() {
                $('#esdm_form_data').slideToggle();
                $("#esdm_saved_cards_list").hide();
                $("#newcard").prop('checked');
                return true;
            },
            toggleEsdmSavedCard: function() {
                $("#newcard").prop('checked', false);
                $("#savedcard").prop('checked', true);
                $("#esdm_saved_cards_list").slideToggle();
                $('#esdm_form_data').hide();
                return true;
            },
            checkRequestFromSavedCard: function() {
                var checkRequest = $("#savedcard").prop('checked');
                return checkRequest;
            },
            toggleEsdmCvc: function(divIds) {
                $('.cvc_field').hide();
                $('#' + divIds).show();
                return true;
            },
            isActive: function() {
                return true;
            },
            getInstruction: function() {
                return window.checkoutConfig.payment.esdm.instructions;
            },
            getCardList: function() {
                var configValues = $.extend({}, window.checkoutConfig.payment[this.getCode()]);
                return configValues.storedCards;
            },
            getData: function() {
                var checkRequest = this.checkRequestFromSavedCard();
                var savedCards = false;
                if (checkRequest) {
                    savedCards = true;
                }
                var savedCardsEntry = false;
                if (savedCards) {
                    var checkFields = this.checkDataCardInfo();
                    var savedCardsEntry = true;
                    var customData = $('#esdm_cvc_field_' + checkFields).data('custom');
                    var cc_exp_month_input = customData.exp_month;
                    var cc_exp_year_input = customData.exp_year;
                    var cvv_input = $('#esdm_cvc_field_' + checkFields).val();
                    var cc_type = customData.cc_type;
                    var credit_number = customData.mask;
                    var esdmTokenId = checkFields;
                    return {
                        'method': this.item.method,
                        'additional_data': {
                            'cc_cid': cvv_input,
                            'cc_ss_start_month': this.creditCardSsStartMonth(),
                            'cc_ss_start_year': this.creditCardSsStartYear(),
                            'cc_type': cc_type,
                            'cc_exp_year': cc_exp_year_input,
                            'cc_exp_month': cc_exp_month_input,
                            'cc_number': credit_number.replace(/\d(?=\d{4})/g, "*")
                        }
                    };
                } else {
                    return {
                        'method': this.item.method,
                        'additional_data': {
                            'cc_cid': this.creditCardVerificationNumber(),
                            'cc_ss_start_month': this.creditCardSsStartMonth(),
                            'cc_ss_start_year': this.creditCardSsStartYear(),
                            'cc_type': this.creditCardType(),
                            'cc_exp_year': this.creditCardExpYear(),
                            'cc_exp_month': this.creditCardExpMonth(),
                            'cc_number': this.creditCardNumber().replace(/\d(?=\d{4})/g, "*")
                        }
                    };
                }
            },
            checkDataCardInfo: function() {
                var selected = $(".selectedsavedcard:checked");
                var idVal = selected.val();
                var selectedCvv = $('#esdm_cvc_field_' + idVal).val();
                var myRe = /^[0-9]{3,4}$/;
                var myArray = myRe.exec(selectedCvv);
                return idVal;
            },
            checkSavedCards: function() {
                var selected = $(".selectedsavedcard:checked");
                if (!selected.val()) {
                    alert({
                        content: 'Please select a valid saved card.'
                    });
                    return false;
                } else {
                    var idVal = selected.val();
                    var selectedCvv = $('#esdm_cvc_field_' + idVal).val();
                    var myRe = /^[0-9]{3,4}$/;
                    var myArray = myRe.exec(selectedCvv);
                    if (selectedCvv != myArray) {
                        alert({
                            content: 'Please select a valid Cvv Number. Please use numbers only in this field. Please avoid spaces or other characters such as dots or commas.'
                        });
                        return false;
                    } else {
                        return idVal;
                    }
                }
            },
            postEsdmData: function() {
                var checkRequest = this.checkRequestFromSavedCard();
                var savedCards = false;
                if (checkRequest) {
                    savedCards = true;
                }
                if ((!this.validateHandler()) && (!savedCards)) {
                    return;
                }
                var checkFields = true;
                var savedCardsEntry = false;
                if (savedCards) {
                    var checkFields = this.checkSavedCards();
                    if (!checkFields) {
                        return false;
                    } else {
                        var savedCardsEntry = true;
                        var customData = $('#esdm_cvc_field_' + checkFields).data('custom');
                        var cc_exp_month_input = customData.exp_month;
                        var cc_exp_year_input = customData.exp_year;
                        var cvv_input = $('#esdm_cvc_field_' + checkFields).val();
                        var cc_type = customData.cc_type;
                        var credit_number = customData.mask;
                        var esdmTokenId = checkFields;
                    }
                }
                fullScreenLoader.startLoader();
                var values_to_post = $.extend({}, window.checkoutConfig.payment[this.getCode()]);
                var post_url = values_to_post.url;
                var submit_order = this.placeOrder.bind(this);
                var save_card = '';
                if (!savedCardsEntry) {
                    var cc_exp_month_input = $('#' + esdmRequest.method_code + '_expiration').val();
                    var cc_exp_year_input = $('#' + esdmRequest.method_code + '_expiration_yr').val();
                    var cvv_input = $('#' + esdmRequest.method_code + '_cc_cid').val();
                    var cc_type = $('#' + esdmRequest.method_code + '_cc_type').val();
                    var credit_number = $('#' + esdmRequest.method_code + '_cc_number').val();
                    var save_card = $('#' + esdmRequest.method_code + '_save_card').prop('checked');
                }
                var billing_address = quote.billingAddress();
                values_to_post.cardholdername = "";
                if (billing_address.firstname) {
                    values_to_post.cardholdername += billing_address.firstname + " ";
                }
                if (billing_address.middlename) {
                    values_to_post.cardholdername += billing_address.middlename + " ";
                }
                if (billing_address.lastname) {
                    values_to_post.cardholdername += billing_address.lastname;
                }
                if (typeof billing_address === 'undefined') {
                    alert({
                        content: 'Please enter a billing address.'
                    });
                    return;
                }
                delete values_to_post.url;
                values_to_post.cc_type = cc_type;
                values_to_post.cc_exp_month = cc_exp_month_input;
                values_to_post.cc_exp_year = cc_exp_year_input;
                values_to_post.method = "esdm";
                values_to_post.save_card = save_card;
                var maskednumber = credit_number;
                if (savedCardsEntry) {
                    values_to_post.esdm_token_id = esdmTokenId;
                }
                values_to_post.cc_number = maskednumber
                if (cvv_input.length > 0) {
                    values_to_post.cc_cid = cvv_input;
                }
                esdmRequest.sendRequest(
                    post_url,
                    values_to_post,
                    submit_order
                );
            }
        });
    }
);
