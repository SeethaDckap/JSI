/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * @author aakimov
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (setShippingInformationAction) {

        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var shippingAddress = quote.shippingAddress();
            if (shippingAddress['extension_attributes'] === undefined) {
                shippingAddress['extension_attributes'] = {};
            }

            // you can extract value of extension attribute from any place (in this example I use customAttributes approach)
            shippingAddress['extension_attributes']['salesrep_contact'] = jQuery('[name="salesrep_contact"]').val();
            shippingAddress['extension_attributes']['salesrep_newaddress'] = $.cookie('erp_shipping_customer_addressId');
            // pass execution to original action ('Magento_Checkout/js/action/set-shipping-information')
            return originalAction();
        });
    };
});