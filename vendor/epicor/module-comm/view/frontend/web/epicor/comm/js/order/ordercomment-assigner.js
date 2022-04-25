/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'Magento_Checkout/js/checkout-data'
], function ($,checkoutData) {
    'use strict';
    if (window.checkoutConfig.limitCommentLength == "1") {
        if (window.checkoutConfig.maxCommentLength > 0) {
            $(document.body).on('focus', 'textarea[name *="ordercomment"]', function (commentbox) {
                //restrict chars that can be entered in textbox
                commentbox.target.maxLength = window.checkoutConfig.maxCommentLength;
            });
            $(document.body).on('keyup', 'textarea[name *="ordercomment"]', function (event) {
                //update notice with correct length
                var charsRemaining = window.checkoutConfig.maxCommentLength - event.target.textLength;
                if (charsRemaining < 0) {
                    charsRemaining = 0;
                }
                $('#notice-' + event.target.id + ' span').text(charsRemaining + ' chars remaining');
            });
        } else {
            if (window.checkoutConfig.maxCommentLength == 0) {
                $(document.body).on('focus', 'textarea[name *="ordercomment"]', function (commentbox) {
                    //prevent anything being entered in textbox
                    commentbox.target.maxLength = 0;
                });
            }
        }
    }

    /** Override default place order action and add comment to request */
    return function (paymentData) {
        if (paymentData['extension_attributes'] === undefined) {
            paymentData['extension_attributes'] = {};
        }
        paymentData['extension_attributes']['comment'] = $('[name="ordercomment[comment]"]').val();
        if ($('[name="additional[reference]"]')) {
            paymentData['extension_attributes']['ecc_additional_reference'] = $('[name="additional[reference]"]').val();
        }
        if ($('[name="epmpo"]').length && checkoutData.getSelectedPaymentMethod() == 'pay') {
            if (paymentData['extension_attributes'] === undefined) {
                paymentData['extension_attributes'] = {};
            }
            paymentData['extension_attributes']['ecc_customer_order_ref'] = $('[name="epmpo"]').val();
        }
    };
});