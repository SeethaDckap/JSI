/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'jquery'
], function (Component, customerData, $) {
    'use strict';

    return Component.extend({
        
        initialize: function () {
            var isShowAddToCart = false;
            this._super();
            this.lastOrderedItems = customerData.get('last-ordered-items');
            
            $(this.lastOrderedItems().items).each(function(index, item){
                if(item['is_saleable']) {
                    isShowAddToCart = true;
                }
            });
            this.lastOrderedItems().isShowAddToCart = isShowAddToCart;
        }
    });
});
