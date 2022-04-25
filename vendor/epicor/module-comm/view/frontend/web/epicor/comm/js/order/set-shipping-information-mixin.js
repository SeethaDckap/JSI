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
            shippingAddress['extension_attributes']['ecc_customer_order_ref'] = jQuery('[name="ecc_customer_order_ref"]').val();
            if(jQuery('[name="ecc_tax_exempt_reference"]')) {
                shippingAddress['extension_attributes']['ecc_tax_exempt_reference'] = jQuery('[name="ecc_tax_exempt_reference"]').val();
            }
            if(jQuery('[name="ecc_ship_status_erpcode"]')) {
                shippingAddress['extension_attributes']['ecc_ship_status_erpcode'] = jQuery('[name="ecc_ship_status_erpcode"]').val();
            }
            if(jQuery('#default_shippingdates [name="ecc_required_date"]').attr('type')=="radio"){
                shippingAddress['extension_attributes']['ecc_required_date'] = jQuery('#default_shippingdates [name="ecc_required_date"]:checked').val();                
            }else{
                shippingAddress['extension_attributes']['ecc_required_date'] = jQuery('#default_shippingdates [name="ecc_required_date"]').val();
            }

            if(jQuery('div.date [name="ecc_required_date"]').length) {
                shippingAddress['extension_attributes']['ecc_required_date'] = jQuery('.ecc_required_date [name="ecc_required_date"]').val();
            }

            // pass execution to original action ('Magento_Checkout/js/action/set-shipping-information')
            return originalAction();
        });
    };
});