/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.wishlist = customerData.get('wishlist');
            if(this.wishlist().counter){
                customerData.reload(['wishlist']);
                var items = this.wishlist().items;
                items.forEach(function (item, index) {
                    items[index]["isLoading"] = false;
                });
            }
        }
    });
});
