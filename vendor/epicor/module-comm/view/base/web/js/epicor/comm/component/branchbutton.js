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
    'mage/url',
], function($, Element, registry, layout, modal, utils, fullScreenLoader, checkoutData, selectBillingAddress, urlBuilder) {
    'use strict';

    return Element.extend({
        defaults: {
            additionalClasses: {},
            displayArea: 'outsideGroup',
            displayAsLink: false,
            elementTmpl: 'Epicor_Comm/ui/form/element/button/branchsearchbutton',
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
            if ($('#branch-grid-loader-popup-modal').length) {
                $('#branch-grid-loader-popup-modal').remove();
                $('#branch-grid-loader-popup-showmodal').remove();
            }
            var options = {
                type: 'popup',
                responsive: false,
                innerScroll: false,
                title: 'Store Pickup Address Search'
            };
            $("#branch-grid-loader").append("<div id='branch-grid-loader-popup-modal'></div>");
            $("#branch-grid-loader-popup-modal").append("<div id='branch-grid-loader-popup-showmodal'></div>");
            var popup = modal(options, $('#branch-grid-loader-popup-modal'));
            $('#branch-grid-loader-popup-modal').modal('openModal');
            var ifr = $('<iframe/>', {
                src: urlBuilder.build('branchpickup/pickup/pickupsearch'),
                id: 'branchpopupiframe',
                style: 'width:740px;height:614px;display:block;border:none;',
                load: function() {
                    fullScreenLoader.stopLoader();
                }
            });
            $('#branch-grid-loader-popup-showmodal').append(ifr);
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

        ClosepopulateBranchAddressSelect: function() {
            var shipAddress = $('#jsonBranchfiltervals').val();
            var shipAddressDetails = $('#jsonBranchfilteraddress').val();
            var shippingInfo = {};
            var ret = shipAddress.replace('customer-address', '');
            shippingInfo.id = ret;
            var addressVals = this.formatAddressBranch(shipAddressDetails);
            selectBillingAddress(addressVals);
            checkoutData.setSelectedBillingAddress(shipAddress);
        },

        formatAddressBranch: function(jsonData) {
            var addressDatas = jsonData;
            var addressData = $.parseJSON(addressDatas);
            var returnObjs = {
                customerAddressId: addressData.entity_id,
                email: addressData.email,
                countryId: addressData.country_id,
                regionId: addressData.region_id,
                regionCode: addressData.region_id,
                region: addressData.region,
                customerId: addressData.customer_id,
                street: addressData.street,
                company: addressData.company,
                telephone: addressData.telephone,
                fax: addressData.fax,
                postcode: addressData.postcode,
                city: addressData.city,
                firstname: addressData.firstname,
                lastname: addressData.lastname,
                middlename: addressData.middlename,
                prefix: addressData.prefix,
                suffix: addressData.suffix,
                vatId: addressData.vat_id,
                sameAsBilling: addressData.same_as_billing,
                saveInAddressBook: addressData.save_in_address_book,
                customAttributes: addressData.custom_attributes,
                isDefaultShipping: function() {
                    return addressData.default_shipping;
                },
                isDefaultBilling: function() {
                    return addressData.default_billing;
                },
                getAddressInline: function() {
                    return addressData.inline;
                },
                getType: function() {
                    return 'customer-address'
                },
                getKey: function() {
                    return this.getType() + this.customerAddressId;
                },
                getCacheKey: function() {
                    return this.getKey();
                },
                isEditable: function() {
                    return false;
                },
                canUseForBilling: function() {
                    return true;
                }
            }
            return returnObjs;
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