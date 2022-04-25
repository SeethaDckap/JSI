/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*global define*/
define([
    'underscore',
    'ko',
    'mageUtils',
    'uiComponent',
    'uiLayout',
    'Magento_Customer/js/model/address-list',
    'Epicor_SalesRep/epicor/salesrep/js/model/contact',
    'Epicor_Comm/epicor/comm/js/shipping-address/limit-shipping-address',
    'Magento_Checkout/js/model/quote',
    'Epicor_Comm/epicor/comm/js/view/checkout/shipping/limit-address'
], function (_, ko, utils, Component, layout, addressList, contact, limitShippingAddress, quote, limitAddress) {
    'use strict';
    var defaultRendererTemplate = {
        parent: '${ $.$data.parentName }',
        name: '${ $.$data.name }',
        component: 'Magento_Checkout/js/view/shipping-address/address-renderer/default'
    };

    return Component.extend({
        allShippingIds: [],
        currentAddresses: ko.observableArray([]),
        currentCount: 0,
        renderedAddresses: [],
        defaults: {
            template: 'Magento_Checkout/shipping-address/list',
            visible: addressList().length > 0,
            rendererTemplates: []
        },

        initialize: function () {
            this._super()
                    .initChildren();
            //watches for changes to which address is the default shipping address
            quote.shippingAddress.subscribe(function (address) {
                this.initChildren(address);
            }.bind(this));
            addressList.subscribe(
                    function (changes) {
                        var self = this;
                        changes.forEach(function (change) {
                            if (change.status === 'added') {
                                self.createRendererComponent(change.value, change.index);
                            }
                        });
                    },
                    this,
                    'arrayChange'
                    );
            return this;
        },

        initConfig: function () {
            this._super();
            // the list of child components that are responsible for address rendering
            this.rendererComponents = [];
            return this;
        },

        initChildren: function (address) {
            let addressId = null;
            if(typeof(address) !== 'undefined' && address.hasOwnProperty('customerAddressId')){
                addressId = address.customerAddressId
            }
            var reducedAddressList = limitShippingAddress.getAddressList(addressId);
            this.allShippingIds = window.checkoutConfig.isShippingIds;
            if(reducedAddressList){
                this.allShippingIds = reducedAddressList;
            }
            _.each(addressList(), this.createRendererComponent, this);
            limitAddress().setRenderedComponentCount(this.rendererComponents);
            return this;
        },

        /**
         * Create new component that will render given address in the address list
         *
         * @param address
         * @param index
         */
        createRendererComponent: function (address, index) {
            var shippingIds = this.allShippingIds;
            var customerAddressId = address.getKey().replace(address.getType(), "");
            if (( (window.checkoutConfig.forceAddressTypes || window.checkoutConfig.isContractEnabled) &&
                 ( jQuery.inArray(address.customerAddressId, shippingIds) != -1 ||
                     jQuery.inArray(address.customerAddressId, contact.shippingAddressids()) != -1 ||
                  address.getType() == 'new-customer-address')
                ) ||
                (!window.checkoutConfig.forceAddressTypes && !window.checkoutConfig.isContractEnabled) ) {
                if (limitShippingAddress.addressLimited
                    && ( (jQuery.inArray(address.customerAddressId, shippingIds) === -1) ||
                        (jQuery.inArray(customerAddressId, shippingIds) === -1 ) )
                    && address.getType() !== 'new-customer-address') {
                    return false;
                }
                if (index in this.rendererComponents) {
                    this.rendererComponents[index].address(address);
                } else {
                    // rendererTemplates are provided via layout
                    var rendererTemplate = (address.getType() != undefined && this.rendererTemplates[address.getType()] != undefined)
                            ? utils.extend({}, defaultRendererTemplate, this.rendererTemplates[address.getType()])
                            : defaultRendererTemplate;
                    var templateData = {
                        parentName: this.name,
                        name: index
                    };
                    var rendererComponent = utils.template(rendererTemplate, templateData);
                    utils.extend(rendererComponent, {address: ko.observable(address)});
                    layout([rendererComponent]);
                    this.rendererComponents[index] = rendererComponent;
                }
            }

        }
    });
});
