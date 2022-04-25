/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define(
        [
            'jquery',
            'uiComponent',
            'Magento_Checkout/js/model/quote',
            'Magento_Checkout/js/model/step-navigator',
            'Magento_Checkout/js/model/sidebar',
            'Magento_Catalog/js/price-utils'
        ],
        function ($, Component, quote, stepNavigator, sidebarModel, priceUtils) {
            'use strict';
            return function (targetModule) {
                return targetModule.extend({
                    getShippingMethodTitle: function () {
                        var locationCode = '';
                        var shippingMethod = quote.shippingMethod();
                        var containter = $("#branchpickup-addresses").find("div.selected-branchpickup-item");
                        var branchPickupCode = window.checkoutConfig.branchPickupCode;
                        if (containter && shippingMethod.method_code === branchPickupCode) {
                            locationCode = $('.selected-branchpickup-item').children().eq(0).text();
                            return shippingMethod ? shippingMethod.carrier_title + " - " + shippingMethod.method_title + " : " + locationCode : '';
                        }
                        var result = this._super();
                        return result;
                    }
                });
            };

        }
);