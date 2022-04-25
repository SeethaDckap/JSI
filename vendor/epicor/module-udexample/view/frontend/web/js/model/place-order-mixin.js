/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function (placeOrderAction) {

        /** Override default place order action and add agreement_ids to request */
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            if ($('input[name="orderconformance[conformance]"]').length ) {
                var checkOrNot = $('input[name="orderconformance[conformance]"]').is(':checked');
                if(checkOrNot) {
                    paymentData.additional_data={conformance:true};
                } else {
                    paymentData.additional_data={conformance:false};
                }
            }
            return originalAction(paymentData, messageContainer);
        });
    };
});