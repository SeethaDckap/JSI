define([
    'underscore',
    'ko',
    'jquery',
    'uiComponent',
    'Epicor_Comm/epicor/comm/js/shipping-address/limit-shipping-address',
    'Magento_Customer/js/model/address-list',
    'Epicor_Comm/epicor/comm/js/shipping-address/allowed-addresses'
], function (_,  ko, $, Component, limitAddress, addressList, allowedAddresses) {
    'use strict';
    return Component.extend({
        addressCount: 0,
        shippingAddressList: [],
        isNoticeVisible: ko.observable(true),
        customerAddressCount: ko.observable(0),
        allShippingIds: window.checkoutConfig.isShippingIds,
        defaults: {
            template: 'Epicor_Comm/checkout/shipping/address-limit'
        },
        initialize: function () {
            this._super();
            return this;

        },
        isAddressesLimited: function () {
            this.getUnreducedListCount();
            return limitAddress.addressLimited;
        },
        setRenderedComponentCount: function(renderedComponents) {
            this.currentCount = 0;
            _.each(renderedComponents, function(item, index){
                if(typeof(item) !== 'undefined'){
                    if(item.address().getType() !== 'new-customer-address'){
                        this.currentCount ++;
                    }
                }
            }, this);

            this.customerAddressCount(this.currentCount);
        },

        setNoticeVisibility: function() {
            if(this.customerAddressCount() < this.addressCount){
                this.isNoticeVisible(true);
            }else{
                this.isNoticeVisible(false);
            }
        },

        /**
         * calculates the number of addresses that would be displayed without limiting
         * @returns {number}
         */
        getUnreducedListCount: function () {
            this.addressCount = 0;
            _.each(addressList(), function (address, index) {
                if (allowedAddresses.isValidAddress(address, window.checkoutConfig.isShippingIds)
                    && this.isCustomerExistingAddress(address)) {
                    this.addressCount++;
                }
            }, this);

            this.setNoticeVisibility();
            return this.addressCount;
        },
        isCustomerExistingAddress: function (address) {
            return address.hasOwnProperty('customerAddressId');
        }

    });
});
