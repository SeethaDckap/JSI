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
            //customerData.reload(['quickOrderPadLink']);
            this._super();
            this.quickOrderPadLink = customerData.get('quickOrderPadLink');
            if(this.quickOrderPadLink().is_enable === undefined){
                customerData.reload(['quickOrderPadLink']);
            }
            this.quickOrderPadLink().url = this.url;
            this.quickOrderPadLink().lable = this.lable;

        }
    });
});
