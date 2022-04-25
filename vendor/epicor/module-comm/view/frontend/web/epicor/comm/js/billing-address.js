/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*jshint browser:true*/
/*global define*/
define(
    [   'jquery',
        'ko',
        'underscore',
        'Magento_Ui/js/form/form',
        'Magento_Customer/js/model/customer',
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/create-billing-address',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/action/set-billing-address',
        'Magento_Ui/js/model/messageList',
        'mage/translate',
        'Magento_Customer/js/model/customer/address',
        'Epicor_SalesRep/epicor/salesrep/js/model/salesrep-address-list',
    ],
    function (
        $,
        ko,
        _,
        Component,
        customer,
        addressList,
        quote,
        createBillingAddress,
        selectBillingAddress,
        checkoutData,
        checkoutDataResolver,
        customerData,
        setBillingAddressAction,
        globalMessageList,
        $t,
        addressModel,
        addressListcustom
    ) {
        'use strict';        
        //var addressOptions   = ko.observableArray([]);
        var lastSelectedBillingAddress = null,
            newAddressOption = {
                /**
                 * Get new address label
                 * @returns {String}
                 */
                getAddressInline: function () {
                    return $t('New Address');
                },
                customerAddressId: null
            },
            countryData = customerData.get('directory-data'),
            billingIds = window.checkoutConfig.isBillingIds,
            addressOptions = addressList().filter(function (address) {
               if(window.checkoutConfig.forceAddressTypes){
                return ((address.getType() == 'customer-address') && jQuery.inArray(address.customerAddressId, billingIds) != -1);   
               }else {
                return address.getType() == 'customer-address';   
               } 
            
                
            });
            var erp_canAddNew= window.checkoutConfig.ErpBillingCanAddNew;
            if(erp_canAddNew == true || erp_canAddNew ==1){
                addressOptions.push(newAddressOption);
            }
            //registered guest can have 0, null or undefined values
            var registeredGuestValues = ["0", null, undefined];
            var registeredGuest = jQuery.inArray(window.checkoutConfig.customerEccErpaccountId, registeredGuestValues);  
            var salesRep = window.checkoutConfig.isSalesRep;
            if(registeredGuest >= 0 && !salesRep){
                window.registeredGuest = true;
            }else{
                window.registeredGuest = false;
            }   
             
        return Component.extend({
            defaults: {
                template: 'Epicor_Comm/checkout/billing-address'
            },
            isSalesrep: ko.observable(false),
            currentBillingAddress: quote.billingAddress,
            addressOptions: addressOptions,
            customerHasAddresses: addressOptions.length > 1,

            /**
             * Init component
             */
            initialize: function () {
                this._super();
                quote.paymentMethod.subscribe(function () {
                    checkoutDataResolver.resolveBillingAddress();
                }, this);
                if(window.checkoutConfig.isSalesRep){
                    this.isSalesrep(true);
                }else{
                    this.isSalesrep(false);
                }                
                              
            },
            onBillingElementRender: function (lastSelectedBillingAddress) {

                    if(addressListcustom().length>0  && window.checkoutConfig.isSalesRep){
                        var mySelect = $('select[name="billing_address_id"]');
                         $('select[name="billing_address_id"]').empty();
                          $.each(addressListcustom(), function(index, address) {
                            if(typeof lastSelectedBillingAddress  === 'object' && lastSelectedBillingAddress.getCacheKey() == address.getCacheKey()){
                              mySelect.append(
                                  $('<option selected></option>').val(address.getCacheKey()).html(address.getAddressInline())
                              );
                            }else{
                              mySelect.append(
                                  $('<option></option>').val(address.getCacheKey()).html(address.getAddressInline())
                              );
                            }
                           });
                           var erp_canAddNew= window.checkoutConfig.ErpBillingCanAddNew;
                            if(erp_canAddNew == true || erp_canAddNew ==1){
                                        mySelect.append(
                                                $('<option></option>').val(newAddressOption).html($t('New Address'))
                                       );
                           }
                        $('#billing-new-address-form input[name="firstname"]').val(addressListcustom()[0].firstname);
                        $('#billing-new-address-form input[name="lastname"]').val(addressListcustom()[0].lastname);
                                                
                        //quote.billingAddress.firstname = '';
                        //this.isAddressSameAsShipping(false);
                    
                    }else{
                        var mySelect = $('select[name="billing_address_id"]');
                         addressListcustom.removeAll();
                          $.each(addressOptions, function(index, address) {
                              if(address.getAddressInline() != $t('New Address')){
                                    addressListcustom.push(address);
                                }
                           });
                           
                           
                    }
            },

            /**
             * @return {exports.initObservable}
             */
            initObservable: function () {
                this._super()
                    .observe({
                        selectedAddress: null,
                        isAddressDetailsVisible: quote.billingAddress() != null,
                        isAddressFormVisible: !customer.isLoggedIn() || addressOptions.length == 1,
                        isAddressSameAsShipping: false,
                        saveInAddressBook: 1,
                        editAddressAvailable: !this.isAddressSameAsShipping && (!window.checkoutConfig.forceAddressTypes || (window.checkoutConfig.forceAddressTypes && !window.checkoutConfig.customerEccErpaccountId))
                    });

                quote.billingAddress.subscribe(function (newAddress) {
                    if (quote.isVirtual()) {
                        this.isAddressSameAsShipping(false);
                    } else {
                        this.isAddressSameAsShipping(
                                //make false here
                            newAddress != null &&
                            newAddress.getCacheKey() == quote.shippingAddress().getCacheKey()
                        );
                    }
                    if(this.isAddressSameAsShipping() && window.checkoutConfig.isSalesRep){
                        
                        quote.shippingAddress().sameAsBilling = 1;
                        this.updateAddresses(); 
                    }
                    if (newAddress != null && newAddress.saveInAddressBook !== undefined) {
                        this.saveInAddressBook(newAddress.saveInAddressBook);
                    } else {
                        this.saveInAddressBook(1);
                    }
                    
                    if(window.checkoutConfig.isSalesRep){
                       this.saveInAddressBook(0);                        
                    }
                    this.isAddressDetailsVisible(true);
                    
                }, this);

                return this;
            },

            canUseShippingAddress: ko.computed(function () {
                var shippingCode = window.checkoutConfig.branchPickupCode;
                if(window.checkoutConfig.isCustomer){  
                    
                    if(window.checkoutConfig.forceAddressTypes && !window.registeredGuest || (quote.shippingMethod() && quote.shippingMethod().method_code === shippingCode)){
                        return false;
                    }
                }else{
                    if(quote.shippingMethod() && quote.shippingMethod().method_code === shippingCode){
                        return false;
                    }
                }
                return !quote.isVirtual() && quote.shippingAddress() && quote.shippingAddress().canUseForBilling();
            }),

            /**
             * @param {Object} address
             * @return {*}
             */
            addressOptionsText: function (address) {
                return address.getAddressInline();
            },

            /**
             * @return {Boolean}
             */
            useShippingAddress: function () {

                if (this.isAddressSameAsShipping()) {
                    selectBillingAddress(quote.shippingAddress());

                    this.updateAddresses();
                    this.isAddressDetailsVisible(true); 
                } else { 
                    lastSelectedBillingAddress = quote.billingAddress();
                    quote.billingAddress(null);
                    this.isAddressDetailsVisible(false);
                }
                
                if(window.checkoutConfig.isSalesRepContactenabled){
                      this.onBillingElementRender(quote.billingAddress());
                }
                
                checkoutData.setSelectedBillingAddress(null);

                return true;
            },

            /**
             * Update address action
             */
            updateAddress: function () {
               var finalresult = false;
                var self = this; 
                if(addressListcustom().length>0 && window.checkoutConfig.isSalesRepContactenabled){  
                    finalresult =  $.grep(addressListcustom(), function(address) {
                        if(typeof self.selectedAddress()  === 'string' && self.selectedAddress() == address.getCacheKey()){  
                 
                            return address;
                        }else if(typeof self.selectedAddress()  === 'object' && self.selectedAddress().getCacheKey() == address.getCacheKey()){  
                    
                            return address;
                        }else{
                            return false;
                        }
                    });
                }
                if(finalresult && finalresult[0]){    
                    selectBillingAddress(finalresult[0]);
                    checkoutData.setSelectedBillingAddress(finalresult[0]); 
                    
                }else if (this.selectedAddress() && this.selectedAddress() != newAddressOption) {
                    selectBillingAddress(this.selectedAddress());
                    checkoutData.setSelectedBillingAddress(this.selectedAddress().getKey());
                } else {
                    this.source.set('params.invalid', false);
                    this.source.trigger(this.dataScopePrefix + '.data.validate');

                    if (this.source.get(this.dataScopePrefix + '.custom_attributes')) {
                        this.source.trigger(this.dataScopePrefix + '.custom_attributes.data.validate');
                    }

                    if (!this.source.get('params.invalid')) {
                        var addressData = this.source.get(this.dataScopePrefix),
                            newBillingAddress;

                        if (customer.isLoggedIn() && !this.customerHasAddresses) {
                            this.saveInAddressBook(1);
                        }
                        addressData['save_in_address_book'] = this.saveInAddressBook() ? 1 : 0;
                        newBillingAddress = createBillingAddress(addressData);
                        
                        // New address must be selected as a billing address
                        selectBillingAddress(newBillingAddress);
                        checkoutData.setSelectedBillingAddress(newBillingAddress.getKey());
                        checkoutData.setNewCustomerBillingAddress(addressData);
                    }
                }
                this.updateAddresses();
            },

            /**
             * Edit address action
             */
            editAddress: function () {
                lastSelectedBillingAddress = quote.billingAddress();
                quote.billingAddress(null);
                this.isAddressDetailsVisible(false);
                
                if(window.checkoutConfig.isSalesRep){
                      $('#billing-new-address-form .field.firstname').hide();
                      $('#billing-new-address-form .field.lastname').hide();
                      $('#billing-new-address-form .choice').hide();                                    
                    if(window.checkoutConfig.isSalesRepContactenabled){                    
                          this.onBillingElementRender(lastSelectedBillingAddress);
                    }
               } 
                
            },

            /**
             * Cancel address edit action
             */
            cancelAddressEdit: function () {
                this.restoreBillingAddress();
                if (quote.billingAddress()) {
                    // restore 'Same As Shipping' checkbox state
                    this.isAddressSameAsShipping(
                        quote.billingAddress() != null &&
                            quote.billingAddress().getCacheKey() == quote.shippingAddress().getCacheKey() &&
                            !quote.isVirtual()
                    );
                    this.isAddressDetailsVisible(true);
                }
            },

            /**
             * Restore billing address
             */
            restoreBillingAddress: function () {
                if (lastSelectedBillingAddress != null) {
                    selectBillingAddress(lastSelectedBillingAddress);
                }
            },

            /**
             * @param {Object} address
             */
            onAddressChange: function (address) {
                this.isAddressFormVisible(address == newAddressOption);
                if(window.checkoutConfig.isMasqurading){
                    $('#billing-save-in-address-book').parents('div:eq(0)').html('');
                } 
                $('#billing-new-address-form .field.firstname').hide();
                $('#billing-new-address-form .field.lastname').hide();
                if(window.checkoutConfig.isSalesRep && addressListcustom().length>0){                
                    $('#billing-new-address-form input[name="firstname"]').val(addressListcustom()[0].firstname);
                    $('#billing-new-address-form input[name="lastname"]').val(addressListcustom()[0].lastname);
                }
                        
            },

            /**
             * @param {int} countryId
             * @return {*}
             */
            getCountryName: function (countryId) {
                return countryData()[countryId] != undefined ? countryData()[countryId].name : '';
            },

            /**
             * Trigger action to update shipping and billing addresses
             */
            updateAddresses: function () {
            
            var customerAddressId =  quote.billingAddress().customerAddressId;  
             if (customerAddressId!= undefined && customerAddressId!=null && customerAddressId.indexOf("erpaddress_") != -1) 
             { 
                  $.cookie('erp_billing_customer_addressId', customerAddressId, {expires: 365, path: '/' });
                  quote.billingAddress().customerAddressId = null;
             }else{                 
                  $.cookie('erp_billing_customer_addressId', 'new-address', {expires: 365, path: '/' });
             }   
                
                if (window.checkoutConfig.reloadOnBillingAddress ||
                    !window.checkoutConfig.displayBillingOnPaymentMethod
                ) {
                    setBillingAddressAction(globalMessageList);
                }
                quote.billingAddress().customerAddressId = customerAddressId;
                                 
                // don't un tick the 'billing address same as shipping' if an unregistered guest(ie not logged in), a registered guest(ie erpaccountId == 0,null or undefined)  or forceaddresstype not set   
                if(customer.isLoggedIn() && !window.registeredGuest && window.checkoutConfig.forceAddressTypes){
                    this.isAddressSameAsShipping(false);
                }                
            },

            /**
             * Get code
             * @param {Object} parent
             * @returns {String}
             */
            getCode: function (parent) {
                return _.isFunction(parent.getCode) ? parent.getCode() : 'shared';
            },
            /**
             * Get code
             * @param {Object} parent
             * @returns {String}
             */
            isEditAvailable: function () {
                if( !customer.isLoggedIn() || window.registeredGuest || !window.checkoutConfig.forceAddressTypes){      
                    return true;
                }
                return false;
             //   return _.isFunction(parent.getCode) ? parent.getCode() : 'shared';
            },            
        });
    }
);
