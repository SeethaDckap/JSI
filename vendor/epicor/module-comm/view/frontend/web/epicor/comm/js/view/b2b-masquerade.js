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
            customerData.reload(['b2bmasquerade']);
            this._super();
            this.b2bmasquerade = customerData.get('b2bmasquerade');
        }
    });
});
