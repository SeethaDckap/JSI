/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define([
    'jquery',
    'Magento_Customer/js/customer-data'
], function($, customerData) {
    'use strict';
    return function(targetModule) {
        return targetModule.extend({
            /** @inheritdoc */
            initialize: function () {
                this._super();
                this.wishlist = customerData.get('wishlist');
                if (this.wishlist().counter) {
                    var items = this.wishlist().items;
                    items.forEach(function (item, index) {
                        items[index]["isLoading"] = false;
                    });
                }
            }
        });
    };
});
