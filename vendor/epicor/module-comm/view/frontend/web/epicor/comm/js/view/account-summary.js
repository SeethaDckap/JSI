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
            customerData.reload(['accountSummary']);
            this._super();
            this.accountSummary = customerData.get('accountSummary');
        }
    });
});
