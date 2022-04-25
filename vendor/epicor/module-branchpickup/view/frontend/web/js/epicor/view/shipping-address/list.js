/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define([
    'jquery',
    'underscore',
    'ko',
    'mageUtils',
    'uiComponent',
    'uiLayout',
    'Epicor_BranchPickup/js/model/address-list',
    'Epicor_BranchPickup/js/epicor/model/branch-common-utils'
], function($, _, ko, utils, Component, layout, addressList,Branchutils) {
    'use strict';
    var defaultRendererTemplate = {
        parent: '${ $.$data.parentName }',
        name: '${ $.$data.name }',
        component: 'Epicor_BranchPickup/js/epicor/view/shipping-address/address-renderer/default'
    };

    return Component.extend({
        defaults: {
            template: 'Epicor_BranchPickup/shipping-address/list',
            visible: addressList().length > 0,
            rendererTemplates: []
        },

        initialize: function() {
            this._super()
                .initChildren();

            addressList.subscribe(
                function(changes) {
                    var self = this;
                    changes.forEach(function(change) {
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

        initConfig: function() {
            this._super();
            // the list of child components that are responsible for address rendering
            this.rendererComponents = [];
            return this;
        },

        initChildren: function() {
            _.each(addressList(), this.createRendererComponent, this);
            return this;
        },
        checkBranchPickupSelected: function() {
            var shippingAddress = window.checkoutConfig.selectedBranch;
            if (!shippingAddress) {
                this.showShippingAddress();
            }
            return shippingAddress;
        },
        showShippingAddress: function() {
            Branchutils.showShippingAddress();
            return true;
        },

        showBranchPickupAddress: function() {
            Branchutils.showBranchPickupAddress();
            return true;
        },
        /**
         * Create new component that will render given address in the address list
         *
         * @param address
         * @param index
         */
        createRendererComponent: function(branchaddress, index) {
            if (index in this.rendererComponents) {
                this.rendererComponents[index].branchaddress(branchaddress);
            } else {
                // rendererTemplates are provided via layout
                var rendererTemplate = (branchaddress.getType() != undefined && this.rendererTemplates[branchaddress.getType()] != undefined) ?
                    utils.extend({}, defaultRendererTemplate, this.rendererTemplates[branchaddress.getType()]) :
                    defaultRendererTemplate;
                var templateData = {
                    parentName: this.name,
                    name: index
                };
                var rendererComponent = utils.template(rendererTemplate, templateData);
                utils.extend(rendererComponent, {
                    branchaddress: ko.observable(branchaddress)
                });
                layout([rendererComponent]);
                this.rendererComponents[index] = rendererComponent;
            }
        },
    });
});