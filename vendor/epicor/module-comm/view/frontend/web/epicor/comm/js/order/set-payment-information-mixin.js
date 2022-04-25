/**
  * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
  */

/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Epicor_Comm/epicor/comm/js/order/ordercomment-assigner',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, ordercommentAssigner, quote) {
    'use strict';

    return function (placeOrderAction) {

        /** Override place-order-mixin for set-payment-information action as they differs only by method signature */
        return wrapper.wrap(placeOrderAction, function (originalAction, messageContainer, paymentData) {
            ordercommentAssigner(paymentData);

            var customerAddressId = null;
            if (quote.billingAddress()) {
                customerAddressId = quote.billingAddress().customerAddressId;
            }

            if (customerAddressId && customerAddressId != undefined && customerAddressId !== null && customerAddressId.indexOf("erpaddress_") !== -1) {
                $.cookie('erp_billing_customer_addressId', customerAddressId, {expires: 365, path: '/'});
                quote.billingAddress().customerAddressId = null;
            } else if ($.cookie('erp_billing_customer_addressId') == 'new-address') {
                //nothing to do
            }

            return originalAction(messageContainer, paymentData);
        });
    };
}); 
 
