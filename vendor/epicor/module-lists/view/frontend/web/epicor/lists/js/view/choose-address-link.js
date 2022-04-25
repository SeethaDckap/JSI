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
            this._super();
            this.chooseAddressLink = customerData.get('choose-address-link');
            if(this.chooseAddressLink().is_enable === undefined){
                customerData.reload(['choose-address-link']);
            }
            this.chooseAddressLink().url = this.url;
            this.chooseAddressLink().lable = this.lable;

        }
    });
});
