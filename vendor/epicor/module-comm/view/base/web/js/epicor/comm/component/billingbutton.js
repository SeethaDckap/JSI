/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'uiElement',
    'uiRegistry',
    'uiLayout',
    'Magento_Ui/js/modal/modal',
    'mageUtils',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/action/select-billing-address',
    'Epicor_Comm/js/epicor/comm/component/addressformat',
    'mage/url',
], function($, Element, registry, layout, modal, utils, fullScreenLoader, checkoutData, selectBillingAddress, addressformat, urlBuilder) {
    'use strict';

    return Element.extend({
        defaults: {
            additionalClasses: {},
            displayArea: 'outsideGroup',
            displayAsLink: false,
            elementTmpl: 'Epicor_Comm/ui/form/element/button/billingsearchbutton',
            template: 'Epicor_Comm/ui/form/element/button/simple',
            visible: true,
            disabled: false,
            title: ''
        },

        /**
         * Initializes component.
         *
         * @returns {Object} Chainable.
         */
        initialize: function() {
            return this._super()
                ._setClasses();
        },
        showSearchPopup: function() {
            fullScreenLoader.startLoader();
            if ($('#billing-grid-loader-popup-modal').length) {
                $('#billing-grid-loader-popup-modal').remove();
                $('#billing-grid-loader-popup-showmodal').remove();
            }
            var options = {
                type: 'popup',
                responsive: false,
                innerScroll: false,
                title: 'Billing'
            };
            $("#billing-grid-loader").append("<div id='billing-grid-loader-popup-modal'></div>");
            $("#billing-grid-loader-popup-modal").append("<div id='billing-grid-loader-popup-showmodal'></div>");
            var popup = modal(options, $('#billing-grid-loader-popup-modal'));
            $('#billing-grid-loader-popup-modal').modal('openModal');
            var ifr = $('<iframe/>', {
                src: urlBuilder.build('comm/onepage/billingpopup'),
                id: 'shippingpopupiframe',
                style: 'width:740px;height:614px;display:block;border:none;',
                load: function() {
                    fullScreenLoader.stopLoader();
                }
            });
            $('#billing-grid-loader-popup-showmodal').append(ifr);
            //$('#shipping-grid-loader-popup-showmodal').append(data);
            $('.modal-footer').hide();

        },
        /** @inheritdoc */
        initObservable: function() {
            return this._super()
                .observe([
                    'visible',
                    'disabled',
                    'title'
                ]);
        },

        ClosepopulateBillingAddressSelect: function() {
            var shipAddress = $('#jsonBillingfiltervals').val();
            var shipAddressDetails = $('#jsonBillingfilteraddress').val();
            var shippingInfo = {};
            var ret = shipAddress.replace('customer-address', '');
            shippingInfo.id = ret;
            var addressVals = addressformat.formatAddressBranch(shipAddressDetails);
            selectBillingAddress(addressVals);
            checkoutData.setSelectedBillingAddress(shipAddress);
        },


        /**
         * Performs configured actions
         */
        action: function() {
            this.actions.forEach(this.applyAction, this);
        },

        /**
         * Apply action on target component,
         * but previously create this component from template if it is not existed
         *
         * @param {Object} action - action configuration
         */
        applyAction: function(action) {
            var targetName = action.targetName,
                params = action.params || [],
                actionName = action.actionName,
                target;

            if (!registry.has(targetName)) {
                this.getFromTemplate(targetName);
            }
            target = registry.async(targetName);

            if (target && typeof target === 'function' && actionName) {
                params.unshift(actionName);
                target.apply(target, params);
            }
        },

        /**
         * Create target component from template
         *
         * @param {Object} targetName - name of component,
         * that supposed to be a template and need to be initialized
         */
        getFromTemplate: function(targetName) {
            var parentName = targetName.split('.'),
                index = parentName.pop(),
                child;

            parentName = parentName.join('.');
            child = utils.template({
                parent: parentName,
                name: index,
                nodeTemplate: targetName
            });
            layout([child]);
        },

        /**
         * Extends 'additionalClasses' object.
         *
         * @returns {Object} Chainable.
         */
        _setClasses: function() {
            if (typeof this.additionalClasses === 'string') {
                this.additionalClasses = this.additionalClasses
                    .trim()
                    .split(' ')
                    .reduce(function(classes, name) {
                        classes[name] = true;

                        return classes;
                    }, {});
            }

            return this;
        }
    });
});