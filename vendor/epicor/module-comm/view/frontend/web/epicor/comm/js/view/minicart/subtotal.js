/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define([
    'ko',
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (ko, Component, customerData) {
    'use strict';

    return Component.extend({
        canDisplay: function () {
            return !(window.checkout && window.checkout.isHidePrices)
                && !(window.checkout && window.checkout.isPriceDisplayDisabled);
        },
        /**
         * @override
         */
        initialize: function () {
            this._super();
            this.cart = customerData.get('cart');
        }
    });
});
