/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/quote',
    'underscore',
    'mageUtils',
    'uiLayout',
    'Magento_Checkout/js/model/shipping-service',
    'Magento_Checkout/js/model/address-converter',
    'Epicor_BranchPickup/js/epicor/model/address-list',
    'Magento_Customer/js/model/customer/address',
    'Epicor_SalesRep/epicor/salesrep/js/view/checkout/shipping/contactaddress',
    'Magento_Checkout/js/model/checkout-data-resolver',
    'Magento_Checkout/js/action/select-shipping-address',          
    'Epicor_SalesRep/epicor/salesrep/js/view/checkout/shipping/contact', 
        'uiRegistry',
    'mage/validation',
    'domReady!'

], function ($, ko, Component, checkoutData, quote, _, utils,
        layout, shippingService, addressConverter, addressList, addressModel, contactaddress, checkoutDataResolver,
        selectShippingAddressAction,contact,registry,validation) {
    'use strict';

   
    return {
        defaults: {
            customName: '${ $.parentName }.${ $.index }_input',
            rendererTemplates: [],
            //errorValidationMessage: ko.observable(false)
        },
        initialize: function (config) {
            this._super();
            this.config = config;
        },
        initConfig: function () {
            this._super();
            // the list of child components that are responsible for address rendering
            this.rendererComponents = [];
            this.firstname = '';
            this.lastname = '';


            return this;
        },
        onContactElementRender: function (fileInput) {
           
            var selected = this.config.selected;            
            if(selected){                
                $('select[name="salesrep_contact"]').val(selected);
            }
//            /alert(selected);
            this.value(selected);
           return this;

        },
        chooseContact: function (config) {

            this.config = config;
            contact.defaults.errorValidationMessage(false);

            this.rendererComponents = [];
            this.firstname = window.checkoutConfig.customerData.firstname;
            this.lastname = window.checkoutConfig.customerData.lastname;
            
            if ($('select[name="salesrep_contact"] option:selected').val()) {
                _.each(addressList(), this.resetAddress, this);
                // $('.shipping-address-item').remove();      
                //_.each(addressList(), this.unsetAddress, this);
                var contact_name = $('select[name="salesrep_contact"] option:selected').text();
                contact_name = contact_name.split(' ');
                addressList.removeAll();
                
                this.rendererComponents._each(function (currentAddress, index, addresses) {
                    if (currentAddress) {
                                              
                       if (!(currentAddress.getType() == 'new-customer-address')) {
                            currentAddress.firstname = contact_name[0];
                            currentAddress.lastname = contact_name[1];
                        }
                        // addressList.replace(oldcurrentAddress,currentAddress);
                        addressList.push(currentAddress);
                    }
                });
            } else {
                _.each(addressList(), this.resetAddress, this);
                addressList.removeAll();
                this.rendererComponents._each(function (currentAddress, index, addresses) {
                    
                    if (!(currentAddress.getType() == 'new-customer-address')) {
                        currentAddress.firstname = '';
                        currentAddress.lastname = '';
                    }
                    
                    addressList.push(currentAddress);
                });
                this.resetshippingFormData('choose');
            }
            return this;
        },
        resetshippingFormData: function (type) {
            var selected = this.config.value;
            var contact_name = [];
            if (selected) {
                contact_name = selected;
                contact_name = contact_name.split(' ');
            }
              if(type == 'choose'){
                  if ($('select[name="salesrep_contact"] option:selected').val()) {
                    contact_name = $('select[name="salesrep_contact"] option:selected').text();
                    contact_name = contact_name.split(' ');
                }else{
                    contact_name[0]=''; //window.checkoutConfig.customerData.firstname;
                    contact_name[1]=''; //window.checkoutConfig.customerData.lastname;
                }
            
              }  
             this.rendererComponents._each(function (currentAddress, index, addresses) {
                 if((currentAddress.getType() == 'new-customer-address')){ 
                            contact_name[0]=  currentAddress.firstname;
                            contact_name[1] = currentAddress.lastname;
                        }
              });

        },
        defaultContact: function (config) {
            this.config = config;
            var selected = this.config.value;
            this.rendererComponents = [];
            var contact_name = [];
            _.each(addressList(), this.resetAddress, this);
            if (selected) {
                var contact_name = selected;
                contact_name = contact_name.split(' ');
            } else {
                contact_name[0] = '';
                contact_name[1] = '';
            }
            addressList.removeAll();
            this.rendererComponents._each(function (currentAddress, index, addresses) {
                if (currentAddress) {
                    if (!(currentAddress.getType() == 'new-customer-address')) {
                        currentAddress.firstname = contact_name[0];
                        currentAddress.lastname = contact_name[1];
                    }
                    // addressList.replace(oldcurrentAddress,currentAddress);
                    addressList.push(currentAddress);

                }
            });
            this.contactaddress = false;
            //  }
            addressList.valueHasMutated();

            return this;
        },
        /**
         * Create new component that will render given address in the address list
         *
         * @param address
         * @param index
         */
        resetAddress: function (address, index) {

            var current_key = address.getKey();            
            if (current_key.indexOf("erpaddress_") != -1) {
                this.rendererComponents[index] = address;
                address.firstname = this.firstname;
                address.lastname = this.lastname;
            } else if (address.getType() == 'new-customer-address') {
                this.rendererComponents[index] = address;
                // address.firstname = this.firstname;
                //  address.lastname = this.lastname;
            } else {
                address.deleted = 1;
                this.rendererComponents[index] = address;
            }

            //addressList().splice(index, 1);
        },
        /**
         * Create new component that will render given address in the address list
         *
         * @param address
         * @param index
         */
        unsetAddress: function (address, index) {
            delete addressList()[index];
        }
    };
});