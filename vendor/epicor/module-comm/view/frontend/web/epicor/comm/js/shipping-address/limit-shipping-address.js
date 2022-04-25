/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */

define([
    'underscore',
    'Magento_Customer/js/model/address-list',
], function (_, addressList) {
    'use strict';
    return {
        reducedAddressList: [],
        addressCounter: 0,
        maxNumber: window.checkoutConfig.maxAddresses,
        defaultShippingId: window.checkoutConfig.defaultShippingId,
        validShippingIds: window.checkoutConfig.isShippingIds,
        billingAddressIncluded: false,
        addressLimited: window.checkoutConfig.isAddressLimited,
        getAddressList: function (addressId) {
            if (this.addressLimited) {
                this.reduceList();
                // an active address id has been passed as a result of the default shipping address changing
                if(addressId){
                    this.setAdditionalAddress(addressId)
                }
                return this.reducedAddressList;
            } else {
                return false;
            }
        },
        reduceList: function () {
            this.reducedAddressList = [];
            this.addressCounter = 0;
            this.billingAddressIncluded = false;
            _.each(addressList(), function (address) {
                if (this.isDefaultShippingId(address) && !this.billingAddressIncluded && this.isValidAddressId(address)) {
                    this.billingAddressIncluded = true;
                }
                if (this.isAtCounterLimit() && !this.billingAddressIncluded && this.isValidAddressId(address)) {
                    this.reducedAddressList.push(address.customerAddressId);
                    this.addressCounter++;
                } else if (this.isMaxCount() && this.billingAddressIncluded && this.isValidAddressId(address)) {
                    this.reducedAddressList.push(address.customerAddressId);
                    this.addressCounter++;
                }
            }, this);
        },
        getReducedListCount: function () {
            this.reduceList();
            return this.reducedAddressList.length;
        },
        isDefaultShippingId: function (address) {
            return address.customerAddressId === window.checkoutConfig.selectShippingAddress;
        },
        isValidAddressId: function (address) {
            return _.contains(this.validShippingIds, address.customerAddressId)
        },
        isAtCounterLimit: function () {
            return this.addressCounter < this.maxNumber;
        },
        isMaxCount: function () {
            return this.addressCounter < this.maxNumber;
        },
        /**
         * When an address is selected from search, then the default shipping address changes, this sets up
         * an updated limited address set placing the new shipping address first
         * @param newAddressId
         */
        setAdditionalAddress: function (newAddressId) {
            let newAddressSet = [];
            let currentDefaultShippingId = window.checkoutConfig.selectShippingAddress;

            newAddressSet.push(newAddressId);
            if (newAddressId !== currentDefaultShippingId) {
                newAddressSet.push(currentDefaultShippingId);
            }

            _.each(this.reducedAddressList, function (addressId) {
                if ((newAddressSet.length < this.maxNumber)
                    && (addressId !== newAddressId && addressId !== currentDefaultShippingId)) {
                    newAddressSet.push(addressId);
                }
            }.bind(this));
            this.reducedAddressList = newAddressSet;
        }
    };
});
