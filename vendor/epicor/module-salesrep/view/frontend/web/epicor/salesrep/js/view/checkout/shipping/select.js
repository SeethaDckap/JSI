/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


define([
    'jquery',
    'ko',
    'Magento_Ui/js/form/element/select',
    'Magento_Checkout/js/checkout-data',
    'Magento_Checkout/js/model/quote',
    'underscore',
    'Magento_Customer/js/model/address-list',
    'Magento_Customer/js/model/customer/address',
    'Epicor_SalesRep/epicor/salesrep/js/view/checkout/shipping/contactaddress',
    'Magento_Checkout/js/action/select-shipping-address',
    'Magento_Checkout/js/action/select-billing-address',
    'Magento_Checkout/js/action/set-billing-address',
    'Magento_Ui/js/model/messageList',
    'Epicor_SalesRep/epicor/salesrep/js/model/salesrep-address-list',
    'Epicor_SalesRep/epicor/salesrep/js/view/checkout/shipping/contact',
    'Epicor_SalesRep/epicor/salesrep/js/view/checkout/shipping/contactbp',
    'domReady!'
], function ($, ko, Select, checkoutData, quote, _, addressList, addressModel, contactaddress,
        selectShippingAddressAction, selectBillingAddress,
        setBillingAddressAction, globalMessageList, addressListcustom, contact, contactBp) {
    'use strict';

    return Select.extend({
        defaults: {
            customName: '${ $.parentName }.${ $.index }_input',
            rendererTemplates: [],
            errorValidationMessage: ko.observable(false)
        },
        initialize: function (config) {
            this._super();
            this.config = config;
            this.defaultContact();
            if(window.checkoutConfig.branchpickupEnabled === true){
                contactBp.defaultContact(this.config);
            }
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
            this.value(selected);
           return this;

        },
        chooseContact: function () {


            if(window.checkoutConfig.branchpickupEnabled === true){
                contactBp.chooseContact(this.config);
            }
            contact.defaults.errorValidationMessage(false);

            this.rendererComponents = [];
            this.firstname = window.checkoutConfig.customerData.firstname;
            this.lastname = window.checkoutConfig.customerData.lastname;

            var self = this;
            if ($('select[name="salesrep_contact"] option:selected').val()) {
                _.each(addressList(), this.resetAddress, this);
                // $('.shipping-address-item').remove();
                //_.each(addressList(), this.unsetAddress, this);
                var contact_name = $('select[name="salesrep_contact"] option:selected').text();
                contact_name = contact_name.split(' ');
                addressList.removeAll();

                this.rendererComponents._each(function (currentAddress, index, addresses) {
                    if (currentAddress) {
                        if (currentAddress.isDefaultShipping()) {
                            selectShippingAddressAction(currentAddress);
                            checkoutData.setSelectedShippingAddress(currentAddress.getKey());
                            //$('.shipping-address-items .shipping-address-item:first .action-select-shipping-item').trigger('click');
                        }

                        if (currentAddress.isDefaultBilling() && window.checkoutConfig.forceAddressTypes) {
                            currentAddress = self.updateBillingAddress(currentAddress);
                        }


                        if (!(currentAddress.getType() == 'new-customer-address')) {
                            currentAddress.firstname = contact_name[0];
                            currentAddress.lastname = contact_name[1];
                        }
                        // addressList.replace(oldcurrentAddress,currentAddress);
                        addressList.push(currentAddress);
                    }
                });

                addressListcustom.removeAll();
                this.contactaddress = false;
                contactaddress($('select[name="salesrep_contact"] option:selected').val(), 'delivery').done(
                    function () {
                        if (this.contactaddress.delivery) {
                            $.each(this.contactaddress.delivery, function (index, currentAddressdata) {
                                currentAddressdata.id = currentAddressdata.entity_id;

                                var newaddress = new addressModel(currentAddressdata);
                                newaddress.getType('customer-address');
                                newaddress.getAddressInline('currentAddressdata.inline');
                                newaddress.canUseForBilling(true);
                                addressList.push(newaddress);
                            });
                        }

                        if (this.contactaddress.invoice) {
                            var invCounter = 0;
                            $.each(this.contactaddress.invoice, function (index, currentAddressdata) {
                                currentAddressdata.id = currentAddressdata.entity_id;

                                var newaddress = new addressModel(currentAddressdata);
                                newaddress.getType('customer-address');
                                newaddress.getAddressInline('currentAddressdata.inline');
                                newaddress.canUseForBilling(true);
                                addressListcustom.push(newaddress);
                                invCounter++;

                            });
                        }

                    }, this.resetshippingFormData('choose')
                );

                //   addressList.removeAll();
                addressList.valueHasMutated();
                addressListcustom.valueHasMutated();
            } else {
                contactaddress($('select[name="salesrep_contact"] option:selected').val(), 'delivery', true).done(function () {
                    if (this.contactaddress.invoice) {
                         var invCounter = 0;
                         addressListcustom.removeAll();
                         $.each(this.contactaddress.invoice, function (index, currentAddressdata) {
                             currentAddressdata.id = currentAddressdata.entity_id;

                             var newaddress = new addressModel(currentAddressdata);
                             newaddress.getType('customer-address');
                             newaddress.getAddressInline('currentAddressdata.inline');
                             newaddress.canUseForBilling(true);
                             addressListcustom.push(newaddress);
                             invCounter++;

                         });
                     }
                    });
                _.each(addressList(), this.resetAddress, this);
                addressList.removeAll();
                this.rendererComponents._each(function (currentAddress, index, addresses) {
                    if (!(currentAddress.getType() == 'new-customer-address')) {
                        currentAddress.firstname = '';
                        currentAddress.lastname = '';
                    }
                    if (currentAddress.isDefaultBilling() && window.checkoutConfig.forceAddressTypes) {
                       currentAddress = self.updateBillingAddress(currentAddress);
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
            var hasNewAddress = addressList.some(function (address) {
                    return address.getType() == 'new-customer-address';
                });
               if(contact_name && !hasNewAddress){
                    $('#co-shipping-form input[name="firstname"]').val(contact_name[0]);
                    $('#co-shipping-form input[name="lastname"]').val(contact_name[1]);
                }

        },
        defaultContact: function () {

            var selected = this.config.value;
            this.rendererComponents = [];
            addressListcustom.removeAll();
            var contact_name = [];
            _.each(addressList(), this.resetAddress, this);
            if (selected) {
                var contact_name = selected;
                contact_name = contact_name.split(' ');
            } else {
                contact_name[0] = '';
                contact_name[1] = '';
            }
            var self = this;
            addressList.removeAll();
            this.rendererComponents._each(function (currentAddress, index, addresses) {
                if (currentAddress) {
                    if (!(currentAddress.getType() == 'new-customer-address')) {
                        currentAddress.firstname = contact_name[0];
                        currentAddress.lastname = contact_name[1];
                    }
                    // addressList.replace(oldcurrentAddress,currentAddress);

                    if (currentAddress.isDefaultBilling() && window.checkoutConfig.forceAddressTypes) {
                            currentAddress = self.updateBillingAddress(currentAddress);
                        }


                    addressList.push(currentAddress);

                }
            });
            addressListcustom.removeAll();
            this.contactaddress = false;
            // if (selected) {
            contactaddress($('select[name="salesrep_contact"] option:selected').val(), 'delivery').done(
                    function () {
                        if (this.contactaddress.delivery) {
                            $.each(this.contactaddress.delivery, function (index, currentAddressdata) {
                                currentAddressdata.id = currentAddressdata.entity_id;

                                var newaddress = new addressModel(currentAddressdata);
                                ko.observableArray([newaddress]);
                                addressList.push(newaddress);
                            });
                        }

                        if (this.contactaddress.invoice) {
                            addressListcustom.removeAll();
                            $.each(this.contactaddress.invoice, function (index, currentAddressdata) {
                                currentAddressdata.id = currentAddressdata.entity_id;

                                var newaddress = new addressModel(currentAddressdata);
                                newaddress.getType('customer-address');
                                newaddress.getAddressInline('currentAddressdata.inline');
                                newaddress.canUseForBilling(true);
                                addressListcustom.push(newaddress);
                            });
                        } else {
                        }
                        addressList.valueHasMutated();
                        addressListcustom.valueHasMutated();

                    }, this.resetshippingFormData(false)
                    );
            //  }
            addressList.valueHasMutated();
            addressListcustom.valueHasMutated();

            return this;
        },
        /**
         * Create new component that will render given address in the address list
         *
         * @param address
         * @param index
         */
        updateBillingAddress: function (currentAddress) {
            var invcurrent_key = currentAddress.getKey();
            if (invcurrent_key.indexOf("erpaddress_") != -1) {
                var billingcustomerAddressId = currentAddress.customerAddressId;
                currentAddress.customerAddressId = null;
                $.cookie('erp_billing_customer_addressId', billingcustomerAddressId, {expires: 365, path: '/'});
            }
            selectBillingAddress(currentAddress);
            checkoutData.setSelectedBillingAddress(currentAddress.getKey());
            setBillingAddressAction(globalMessageList);
            quote.billingAddress(currentAddress);
            currentAddress.customerAddressId = billingcustomerAddressId;
            return currentAddress;
        },
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
    });
});
