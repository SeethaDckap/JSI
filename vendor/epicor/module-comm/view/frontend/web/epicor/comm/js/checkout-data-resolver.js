/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*jshint browser:true*/
/*global alert*/
/**
 * Checkout adapter for customer data storage
 */
define(
    [
        'Magento_Customer/js/model/address-list',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/action/select-payment-method',
        'Magento_Checkout/js/model/address-converter',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Checkout/js/action/create-billing-address',
        'underscore'
    ],
    function (
        addressList,
        quote,
        checkoutData,
        createShippingAddress,
        selectShippingAddress,
        selectShippingMethodAction,
        paymentService,
        selectPaymentMethodAction,
        addressConverter,
        selectBillingAddress,
        createBillingAddress,
        _
    ) {
        'use strict';

        return {

            /**
             * Resolve estimation address. Used local storage
             */
            resolveEstimationAddress: function () {
                var address;

                if (checkoutData.getShippingAddressFromData() && checkoutData.getSelectedShippingAddress() == 'new-customer-address') {
                    address = addressConverter.formAddressDataToQuoteAddress(checkoutData.getShippingAddressFromData());
                    selectShippingAddress(address);
                } else {
                    this.resolveShippingAddress();
                }

                if (quote.isVirtual()) {
                    if (checkoutData.getBillingAddressFromData()) {
                        address = addressConverter.formAddressDataToQuoteAddress(
                            checkoutData.getBillingAddressFromData()
                        );
                        selectBillingAddress(address);
                    } else {
                        this.resolveBillingAddress();
                    }
                }

            },

            /**
             * Resolve shipping address. Used local storage
             */
            resolveShippingAddress: function () {
                var newCustomerShippingAddress = checkoutData.getNewCustomerShippingAddress();

                if (newCustomerShippingAddress) {
                    createShippingAddress(newCustomerShippingAddress);
                }

                var radioChecked = window.checkoutConfig.selectedBranch;
                var checkArQuote = window.checkoutConfig.arPaymentCheckout;

                if (!radioChecked || typeof checkArQuote != 'undefined') {
                    this.applyShippingAddress();
                }else {
                    var customerData = window.checkoutConfig.customerBranchPickupData;
                    if (Object.keys(customerData).length) {
                        var itemData = customerData.addresses.filter(function(item){
                            return item.code == window.checkoutConfig.selectedBranch;

                        });
                        itemData = itemData[0];
                        if (itemData) {
                            var addressData = this.formatAddressBranch(itemData);
                            addressData.save_in_address_book = 0;
                            var newShippingAddress = createShippingAddress(addressData);
                            selectShippingAddress(newShippingAddress);
                        }
                    }
                }
            },

            formatAddressBranch: function(jsonData) {
                var addressData = jsonData;
                var returnObjs = {
                    locationcode: addressData.code,
                    email: addressData.email,
                    country_id: addressData.country_id,
                    region_id: addressData.region_id,
                    //regionCode: addressData.region.region_code,
                    region: addressData.region,
                    customer_id: addressData.customer_id,
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
                    vat_id: addressData.vat_id,
                    same_as_billing: addressData.same_as_billing,
                    save_in_address_book: 0,
                    custom_attributes: addressData.custom_attributes,
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
                        return this.getType() + this.locationcode;
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
             * Apply resolved estimated address to quote
             *
             * @param {Object} isEstimatedAddress
             */
            applyShippingAddress: function (isEstimatedAddress) {
                var address,
                    shippingAddress,
                    isConvertAddress,
                    addressData,
                    isShippingAddressInitialized;

                if (addressList().length == 0) {
                    address = addressConverter.formAddressDataToQuoteAddress(
                        checkoutData.getShippingAddressFromData()
                    );
                    selectShippingAddress(address);
                }
                shippingAddress = quote.shippingAddress();
                isConvertAddress = isEstimatedAddress || false;

                var checkArQuote = window.checkoutConfig.arPaymentCheckout;
                if (typeof checkArQuote != 'undefined') {
                      var formatArpaymentCode = this.formatArAddress(window.checkoutConfig.arPaymentQuote);
                      quote.billingAddress(formatArpaymentCode);
                      quote.shippingAddress(formatArpaymentCode);
                      shippingAddress = true;
                }

                if (!shippingAddress) {
                    isShippingAddressInitialized = addressList.some(function (addressFromList) {
                        if (addressFromList.customerAddressId === window.checkoutConfig.selectShippingAddress) {
                            addressData = isConvertAddress ?
                            addressConverter.addressToEstimationAddress(addressFromList)
                            : addressFromList;
                            selectShippingAddress(addressData);

                            return true;
                        } else if (checkoutData.getSelectedShippingAddress() == addressFromList.getKey()) {
                            addressData = isConvertAddress ?
                            addressConverter.addressToEstimationAddress(addressFromList)
                            : addressFromList;
                            selectShippingAddress(addressData);

                            return true;
                        }

                        return false;
                    });

                    if (!isShippingAddressInitialized) {
                        isShippingAddressInitialized = addressList.some(function (address) {
                            if (address.isDefaultShipping()) {
                                addressData = isConvertAddress ?
                                    addressConverter.addressToEstimationAddress(address)
                                    : address;
                                var shippingIds = window.checkoutConfig.isShippingIds;
                                if(!window.checkoutConfig.isContractEnabled){
                                    selectShippingAddress(addressData);
                                }else if(jQuery.inArray(addressData.customerAddressId, shippingIds) != -1 ) {
                                    selectShippingAddress(addressData);
                                }else if(jQuery.inArray(addressData.customerAddressId, shippingIds) == -1 &&
                                        shippingIds.length == 1
                                        ) {
                                        addressList.some(function (address) {
                                            if(jQuery.inArray(address.customerAddressId, shippingIds) > -1){
                                                 selectShippingAddress(address);
                                            }
                                        });
                                }

                                return true;
                            }

                            return false;
                        });
                    }

                    if (!isShippingAddressInitialized && addressList().length == 1) {
                        addressData = isConvertAddress ?
                            addressConverter.addressToEstimationAddress(addressList()[0])
                            : addressList()[0];
                        selectShippingAddress(addressData);
                    }
                }
            },

            formatArAddress: function(jsonData) {
                var addressData = jsonData;
                var returnObjs = {
                    locationcode: addressData.code,
                    email: addressData.email,
                    country_id: addressData.country_id,
                    countryId: addressData.country_id,
                    region_id: addressData.region_id,
                    //regionCode: addressData.region.region_code,
                    region: addressData.region,
                    regionCode: addressData.region,
                    customer_id: addressData.customer_id,
                    street: [addressData.street],
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
                    vat_id: addressData.vat_id,
                    same_as_billing: addressData.same_as_billing,
                    save_in_address_book: 0,
                    custom_attributes: addressData.custom_attributes,
                    customerAddressId: Number(),
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
                        return this.getType() ;
                    },
                    getCacheKey: function() {
                        return this.getKey();
                    },
                    isEditable: function() {
                        return false;
                    },
                    canUseForBilling: function() {
                        return true;
                    },
                    getErpCustomerAddressId: function() {
                        return true;
                    }
                }

                return returnObjs;
            },

            /**
             * @param {Object} ratesData
             */
            resolveShippingRates: function (ratesData) {
                var selectedShippingRate = checkoutData.getSelectedShippingRate(),
                    availableRate = false,
                    radioChecked = jQuery('#branchpickupshipping').prop('checked');

                if (!radioChecked) {
                    if (ratesData.length == 1) {
                        //set shipping rate if we have only one available shipping rate
                        selectShippingMethodAction(ratesData[0]);
                        return;
                    }
                }else{
                    return;
                }

                if (quote.shippingMethod()) {
                    availableRate = _.find(ratesData, function (rate) {
                        return rate.carrier_code == quote.shippingMethod().carrier_code &&
                            rate.method_code == quote.shippingMethod().method_code;
                    });
                }

                if (!availableRate && selectedShippingRate) {
                    availableRate = _.find(ratesData, function (rate) {
                        return rate.carrier_code + '_' + rate.method_code === selectedShippingRate;
                    });
                }

                if (!availableRate && window.checkoutConfig.selectedShippingMethod) {
                    availableRate = window.checkoutConfig.selectedShippingMethod;
                    selectShippingMethodAction(window.checkoutConfig.selectedShippingMethod);
                }

                //Unset selected shipping method if not available
                if (!availableRate) {
                    selectShippingMethodAction(null);
                } else {
                    selectShippingMethodAction(availableRate);
                }
            },

            /**
             * Resolve payment method. Used local storage
             */
            resolvePaymentMethod: function () {
                var availablePaymentMethods = paymentService.getAvailablePaymentMethods(),
                    selectedPaymentMethod = checkoutData.getSelectedPaymentMethod();

                if (selectedPaymentMethod) {
                    availablePaymentMethods.some(function (payment) {
                        if (payment.method == selectedPaymentMethod) {
                            selectPaymentMethodAction(payment);
                        }
                    });
                }
            },

            /**
             * Resolve billing address. Used local storage
             */
            resolveBillingAddress: function () {
                var selectedBillingAddress = checkoutData.getSelectedBillingAddress(),
                    newCustomerBillingAddressData = checkoutData.getNewCustomerBillingAddress(),
                    isDefaultBilling = 0,
                    selectBilling;

                var shippingCode = window.checkoutConfig.branchPickupCode;

                if (selectedBillingAddress) {
                    if (selectedBillingAddress == 'new-customer-address' && newCustomerBillingAddressData) {
                        selectBillingAddress(createBillingAddress(newCustomerBillingAddressData));
                    } else {
                        addressList.some(function (address) {
                            if (selectedBillingAddress == address.getKey()) {
                                selectBillingAddress(address);
                            }
                        });
                    }
                } else {
                    if(window.checkoutConfig.forceAddressTypes || (quote.shippingMethod() && quote.shippingMethod().method_code === shippingCode)){
                         addressList.some(function (address) {
                            if (address.isDefaultBilling()) {
                                isDefaultBilling = 1;
                                selectBilling = address;
                            }
                        });
                        if(isDefaultBilling){
                            selectBillingAddress(selectBilling);
                        }else{
                            if(addressList()[0] && typeof addressList()[0].customerAddressId !== 'undefined'){
                                selectBillingAddress(addressList()[0]);
                            }
                        }
                    }else{
                        this.applyBillingAddress();
                    }
                }
            },

            /**
             * Apply resolved billing address to quote
             */
            applyBillingAddress: function () {
                var shippingAddress;

                if (quote.billingAddress()) {
                    selectBillingAddress(quote.billingAddress());

                    return;
                }
                shippingAddress = quote.shippingAddress();
                if (!window.checkoutConfig.forceAddressTypes) {

                    if (shippingAddress &&
                                shippingAddress.canUseForBilling() &&
                                (shippingAddress.isDefaultShipping() || !quote.isVirtual())
                    ) {
                        //set billing address same as shipping by default if it is not empty
                        selectBillingAddress(quote.shippingAddress());
                    }
                }


            }
        };
    }
);
