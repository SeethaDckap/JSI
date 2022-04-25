define([
    'jquery',
    'Epicor_SalesRep/epicor/salesrep/js/model/contact'
], function ($, contact) {
    'use strict';
    return {
        isValidAddress: function (address, shippingIds) {
            let forceAddressTypes = window.checkoutConfig.forceAddressTypes;
            let isContractEnabled = window.checkoutConfig.isContractEnabled;
            return ((forceAddressTypes || isContractEnabled) && this.isValidShippingId(address, shippingIds)) ||
                (!forceAddressTypes && !isContractEnabled)
        },
        isValidShippingId: function (address, shippingIds) {
            return $.inArray(address.customerAddressId, shippingIds) !== -1 ||
                $.inArray(address.customerAddressId, contact.shippingAddressids()) !== -1 ||
                address.getType() === 'new-customer-address';
        }
    };
});