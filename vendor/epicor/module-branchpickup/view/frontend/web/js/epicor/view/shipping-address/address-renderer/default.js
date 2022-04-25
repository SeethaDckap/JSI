/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/model/quote',
    'Epicor_Comm/epicor/comm/js/order/ddacall',
    'Magento_Checkout/js/model/shipping-address/form-popup-state',
    'Magento_Checkout/js/checkout-data',
    'Magento_Customer/js/customer-data',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/url',
    'Magento_Checkout/js/model/shipping-rate-processor/new-address',
    'Magento_Checkout/js/model/shipping-rate-registry',
    'Magento_Checkout/js/model/cart/totals-processor/default',
    'Magento_Checkout/js/model/cart/cache',
    'mage/validation',
    'jquery/ui'
], function($, ko, Component, selectShippingAddressAction, quote,ddacall ,formPopUpState, checkoutData, customerData, modal, fullScreenLoader, url, defaultProcessor, rateRegistry, defaultTotal, cartCache) {
    'use strict';
    var countryData = customerData.get('directory-data');
    

    return Component.extend({
        defaults: {
            template: 'Epicor_BranchPickup/shipping-address/address-renderer/default'
        },
        currentBranch: ko.observable(window.checkoutConfig.selectedBranch),
        initObservable: function() {
            this._super();
            this.isBranchSelected = ko.computed(function() {
                var isBranchSelected = false;
                var shippingAddress = this.currentBranch();
                if (shippingAddress) {
                    isBranchSelected = shippingAddress == this.branchaddress().getKey();
                    this.setBranchShippingInfo();
                    //this.address().trigger_reload = new Date().getTime();
                    rateRegistry.set(this.branchaddress().getKey(), null);
                    rateRegistry.set(this.branchaddress().getCacheKey(), null);                       
                }
                return isBranchSelected;
            }, this);
            return this;
        },
        setBranchShippingInfo: function() {
            var shippingInfo = {};
            var carrierTitle = window.checkoutConfig.carrierTitle;
            var MethodTitle = window.checkoutConfig.carrierMethodTitle;
            var shippingCode = window.checkoutConfig.branchPickupCode;
            shippingInfo.carrier_code = shippingCode;
            shippingInfo.method_code = shippingCode;
            shippingInfo.carrier_title = carrierTitle;
            shippingInfo.method_title = MethodTitle;
            shippingInfo.amount = 0;
            shippingInfo.available = true;
            shippingInfo.error_message = "";
            shippingInfo.price_excl_tax = 0;
            shippingInfo.price_incl_tax = 0;
            quote.shippingMethod(shippingInfo);
            return true;
        },
        getCountryName: function(countryId) {
            return (countryData()[countryId] != undefined) ? countryData()[countryId].name : "";
        },
        selectBranchPickupAddress: function() {
            var checkAddress = this;
            var locationCode = this.branchaddress().locationcode;
            if(this.branchaddress().error && $("input#branchfieldsmissing_"+this.branchaddress().locationid).val() !== "false") {
              checkAddress.showEditLocationPopup(locationCode); 
              return false;
            }
            fullScreenLoader.startLoader();
            $.ajax({
                showLoader: false,
                data: {
                    locationcode: locationCode
                },
                url: url.build('branchpickup/pickup/changepickuplocation'),
                type: "POST",
                //dataType:'json',
            }).done(function(data) {
                var getType = data.type;
                var getItemValues = data.values;
                if ((getType == 'success') && (Object.keys(getItemValues).length != 0)) {
                    checkAddress.itemNotExistsInLocation(getItemValues, locationCode)
                } else {
                    $('#branch-shipping-address-item-'+checkAddress.branchaddress().locationid).addClass('selected-item selected-branchpickup-item').siblings().removeClass('not-selected-item not-selected-branchpickup-item');
                    checkAddress.currentBranch(locationCode);
                    checkAddress.updateQuoteBranchPickupAddress();
                    checkoutData.setSelectedShippingAddress(checkAddress.branchaddress().getKey());
                }
            });
        },
        showEditLocationPopup: function(locationCode) {
            fullScreenLoader.stopLoader();
            if ($('#branchpickup-edit-popup-modal').length) {
                $('#branchpickup-edit-popup-modal').remove();
                $('#show-branch-editpopup').remove();            
            }            
            $.ajax({
                showLoader: false,
                data: {
                    locationcode: locationCode
                },
                url: url.build('branchpickup/pickup/location'),
                type: "POST",
                //dataType:'json',
            }).done(function(data) {
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: 'Please Fill the Mandatory values'
                };
                $("#branch-pickup-grid").append("<div id='branchpickup-edit-popup-modal'></div>");
                $("#branchpickup-edit-popup-modal").append("<div id='show-branch-editpopup'></div>");
                var popup = modal(options, $('#branchpickup-edit-popup-modal'));
                $('#branchpickup-edit-popup-modal').modal('openModal');
                $('#show-branch-editpopup').append(data);
                $('.modal-footer').hide();
            });            
        },
        itemNotExistsInLocation: function(getItemValues, locationCode) {
            fullScreenLoader.stopLoader();
            $.ajax({
                showLoader: false,
                data: {
                    locationcode: locationCode,
                    removeval: getItemValues,
                    branch: locationCode
                },
                url: url.build('branchpickup/pickup/cartpopup'),
                type: "POST",
                //dataType:'json',
            }).done(function(data) {
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: 'The following items are not available from your selected pickup branch'
                };
                $("#show-branch-cartpopup").empty();
                var popup = modal(options, $('#branchpickup-iframe-popup-modal'));
                $('#branchpickup-iframe-popup-modal').modal('openModal');
                $('#show-branch-cartpopup').append(data);
                $('.modal-footer').hide();
            });
        },
        updateQuoteBranchPickupAddress: function() {
            var checkAddress = this;
            var locationCode = this.branchaddress().locationcode;
            $.ajax({
                showLoader: false,
                data: {
                    locationcode: locationCode
                },
                url: url.build('branchpickup/pickup/SaveLocationQuote'),
                type: "POST",
                //dataType:'json',
            }).done(function(data) {
                checkoutData.setSelectedShippingAddress(checkAddress.branchaddress().getKey());
                fullScreenLoader.stopLoader();
                var data = JSON.stringify(checkAddress.branchaddress());
                ddacall(data,'branch_shippingdates');
                cartCache.set('totals',null);
                defaultTotal.estimateTotals();
            });
        },
        ClosepopulateBranchAddressSelect: function() {
            var checkAddress = this;
            var shipAddress = $('#jsonBranchfiltervals').val();
            var errorCode = $('#jsonBranchfiltererror').val();
            var locationCode = shipAddress;
            if(errorCode =="2") {
              checkAddress.showEditLocationPopup(shipAddress); 
              return false;
            }            
            fullScreenLoader.startLoader();
            $.ajax({
                showLoader: false,
                data: {
                    locationcode: locationCode
                },
                url: url.build('branchpickup/pickup/changepickuplocation'),
                type: "POST",
                //dataType:'json',
            }).done(function(data) {
                var getType = data.type;
                var getItemValues = data.values;
                if ((getType == 'success') && (Object.keys(getItemValues).length != 0)) {
                    checkAddress.itemNotExistsInLocation(getItemValues, locationCode)
                } else {
                    checkAddress.currentBranch(locationCode);
                    checkAddress.updateQuoteBranchPickupAddress();
                    checkoutData.setSelectedShippingAddress(checkAddress.branchaddress().getKey());
                }
            });
        }        
    });
});