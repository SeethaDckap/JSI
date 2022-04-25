/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * @api
 */
define([
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/url-builder',
        'mageUtils'
    ], function (customer, urlBuilder, utils) {
        'use strict';

        return function (targetModule) {
            /**
             * @param {Object} quote
             * @return {*}
             */
            targetModule.getUrlForErpEstimationShippingMethodsByAddressId = function (quote) {
                var params = {},
                    urls = {
                        'default': '/carts/mine/erp-estimate-shipping-methods-by-address-id'
                    };

                return this.getUrl(urls, params);

            }
            return targetModule;
        };
    }
);
