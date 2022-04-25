/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
define([
    'uiComponent',
    'Magento_Customer/js/customer-data'
], function (Component, customerData) {
    'use strict';

    return Component.extend({
        /** @inheritdoc */
        initialize: function () {
            //customerData.reload(['delearShopperLink']);
            this._super();
            this.delearShopperLink = customerData.get('delearShopperLink');
            // if(this.delearShopperLink().is_enable === undefined){
            //     customerData.reload(['delearShopperLink']);
            // }
            this.delearShopperLink().id = this.id;
            this.delearShopperLink().currentMode = this.currentMode;
            this.delearShopperLink().delearImagePath = this.delearImagePath;
            this.delearShopperLink().shopperImagePath = this.shopperImagePath;
        }
    });
});
