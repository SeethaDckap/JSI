/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * @author aakimov
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/storage',
    'ko',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/step-navigator',
    'Epicor_Comm/epicor/comm/js/order/cartpopajax',
    'Epicor_Comm/epicor/comm/js/order/ddacall',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/checkout-data',
    'Epicor_BranchPickup/js/epicor/model/branch-common-utils',
    'Magento_Ui/js/modal/alert',
    'Epicor_SalesRep/epicor/salesrep/js/model/contact',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Customer/js/model/address-list',
    'Magento_Ui/js/lib/validation/validator',
    'mage/cookies'
], function(
    $,
    storage,
    ko,
    urlBuilder,
    fullScreenLoader,
    setShippingInformationAction,
    stepNavigator,
    cartpopajax,
    ddacall,
    quote,
    checkoutData,
    branchPickupUtils,
    alertbox,
    contact,
    selectShippingAddressAction,
    addressList,
    validator
) {
    'use strict';
    return function(targetModule) {
        //if targetModule is a uiClass based object
        return targetModule.extend({
            showpopup: false,
            isSalesrep: ko.observable(false),
           initialize: function () {
                var result = this._super();

                if(window.checkoutConfig.isSalesRep){
                    this.saveInAddressBook= 0;
                    this.isSalesrep=true;
                }
                if(window.checkoutConfig.isShippingIds.length == 0){
                    this.isFormInline=true;
                }
                return result;
            },
            validateShippingInformation: function() {
                var rates = [];
                var result = this._super();
                var getresult = this._super();
                var shippingCode = window.checkoutConfig.branchPickupCode;

                // Validate Required date Before Next
                var requireDateElement = this.getChild("before-form").getChild("ecc_required_date");
                if(requireDateElement) {
                    var isValid = requireDateElement.validate();
                    if(!isValid.valid){
                        return false;
                    }
                }

                // Validate Shipping method
                if ((!quote.shippingMethod()) || (quote.shippingMethod().carrier_code == shippingCode)) {
                    this.errorValidationMessage('Please specify a shipping method.');
                    return false;
                }
                if(!this.validateCheckoutPurchaseOrder()){
                   // this.errorValidationMessage('Please Select a valid Customer Order Reference');
                    $('input[name="ecc_customer_order_ref"]').filter(":visible").focus();
                    alertbox({
                            title: 'Error',
                            content: 'Please enter a Valid Customer Order Reference'
                        });
                    return false;
                };

                if(window.checkoutConfig.isSalesRep && window.checkoutConfig.isSalesRepContactReq){
                    if (!contact.selectedcontact()) {
                         this.errorValidationMessage('Please Choose a Contact.');
                        return false;
                    }
                }

                var CustomerLoggedIn = window.isCustomerLoggedIn;
                if ($('#normalshipping').length > 0) {
                    var self = this;
                    if(CustomerLoggedIn) {
                        var newCustomerShippingAddress = checkoutData.getNewCustomerShippingAddress();
                        $('#checkout-step-shipping .addresses .control .shipping-address-items').has('.shipping-address-item').each(function() {
                            if ((!$(this).children('.selected-item').length) && (!newCustomerShippingAddress)) {
                                alertbox({
                                    title: 'Error',
                                    content: 'Please select a valid shipping address'
                                });
                                result = false;
                                //self.errorValidationMessage('Please select a valid shipping address');
                                return false;
                            } else {
                                result = getresult;
                                branchPickupUtils.removeBranchPickupSelection();
                            }
                        });
                    } else {
                        result = getresult;
                        branchPickupUtils.removeBranchPickupSelection();
                    }
                }
                //Contract Check
                if (result) {
                    cartpopajax('shipping').done(
                        function() {
                            if (this.showpopup) {} else {

                                setShippingInformationAction().done(
                                    function() {
                                        stepNavigator.next();
                                    }
                                );
                            }
                        }
                    );
                } else {
                    return result;
                }
            },saveNewAddress: function () {
                var addressData = this.source.get('shippingAddress');
                 if(window.checkoutConfig.isSalesRep){
                     addressData.firstname =  $('#co-shipping-form input[name="firstname"]').val();
                     addressData.lastname =  $('#co-shipping-form  input[name="lastname"]').val();
                     this.source.set('shippingAddress',addressData);
                }

                var result = this._super();
                if (!this.source.get('params.invalid')) {
                    ddacall(false,'default_shippingdates');
                    $.cookie('erp_shipping_customer_addressId', "new-address", {expires: 365, path: '/' });
                }
                return result
            },showFormPopUp: function () {
                var result = this._super();
                if (window.checkoutConfig.isB2BHierarchyMasquerade) {
                    $('#co-shipping-form .choice').hide();
                }
                 if(window.checkoutConfig.isSalesRep){
                    $('#co-shipping-form .choice').hide();
                    var contact_name = false;
                     if ($('select[name="salesrep_contact"] option:selected').val()) {
                        contact_name = $('select[name="salesrep_contact"] option:selected').text();
                        contact_name = contact_name.split(' ');
                    }
                    var hasNewAddress = addressList.some(function (address) {
                        return address.getType() == 'new-customer-address';
                    });
                    if(contact_name && !hasNewAddress){
                         $('#co-shipping-form input[name="firstname"]').val(contact_name[0]);
                         $('#co-shipping-form input[name="lastname"]').val(contact_name[1]);
                    }
                }
                return result
            },
            validateCheckoutPurchaseOrder : function () {
                //check admin values if field is compulsory and/or has min/max length
                var checkoutPurchaseOrder = $('input[name="ecc_customer_order_ref"]').filter(":visible");
                var minTextLength = window.checkoutConfig.eccCustomerOrderRefValidation['min_text_length'];
                var maxTextLength = window.checkoutConfig.eccCustomerOrderRefValidation['max_text_length'];
                var requiredEntry = window.checkoutConfig.eccCustomerOrderRefValidation['required-entry'];
                if (checkoutPurchaseOrder.val() !== undefined) {
                    if(checkoutPurchaseOrder.val().length > maxTextLength || requiredEntry && checkoutPurchaseOrder.val().length < minTextLength){
                       return false;
                    }
                }
                return true;
            },
            newAddressButton: function () {
                var erp_canAddNew = window.checkoutConfig.ErpShippingCanAddNew;
                if(erp_canAddNew == false || erp_canAddNew == 0){
                    return false;
                }
                return true;
            }
        });
    };

});
