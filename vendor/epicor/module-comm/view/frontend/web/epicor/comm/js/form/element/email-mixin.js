/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define(function () {
    'use strict';

    var mixin = {
        /*
         * get Home Customer registration value is enabled
         */
        isHomeCustomerRegistrationEnabled: function () {
            return window.checkoutConfig.homeCustomerRegistrationEnabled;
        },
    };

    return function (target) { // target == Result that Magento_Ui/.../columns returns.
        return target.extend(mixin); // new result that all other modules receive
    };
});