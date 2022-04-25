 /**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/checkout-data'
], function ($, wrapper,checkoutData) {
    'use strict';

    return function (placeOrderAction) {
        return wrapper.wrap(placeOrderAction, function (originalAction, messageContainer, paymentData) {

            if ($('[name="epmpo"]').length && checkoutData.getSelectedPaymentMethod() == 'pay') {
                if (paymentData['extension_attributes'] === undefined) {
                    paymentData['extension_attributes'] = {};
                }
                paymentData['extension_attributes']['ecc_customer_order_ref'] = $('[name="epmpo"]').val();
            }

            return originalAction(messageContainer, paymentData);
        });
    };
}); 
 
