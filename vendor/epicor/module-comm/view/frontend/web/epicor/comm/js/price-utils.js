/**
 * @api
 */
define([
    'jquery',
    'underscore'
], function ($, _) {
    'use strict';

    return function (target) {
        var disablePrice = window.checkoutConfig && window.checkoutConfig.isPriceDisplayDisabled;
        var checkoutVars = window.checkout && window.checkout.isHidePrices;
        var checkoutConfigVars = window.checkoutConfig && window.checkoutConfig.isHidePrices;
        if ((checkoutVars) || (checkoutConfigVars) || disablePrice) {
            target.formatPrice = function formatPrice(amount, format, isShowSign) {
                return '';
            };
        }
        return target;
    };
});