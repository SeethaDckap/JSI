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
            //customerData.reload(['branchPickUpLink']);
            this._super();
            this.branchPickUpLink = customerData.get('branchPickUpLink');
            if(this.branchPickUpLink().is_enable === undefined){
                customerData.reload(['branchPickUpLink']);
            }
            //this.branchPickUpLink().is_enable = false;
            this.branchPickUpLink().url = this.url;
            this.branchPickUpLink().lable = this.lable;

        }
    });
});
