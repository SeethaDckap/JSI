define(
    [
        'jquery',
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/resource-url-manager',
        'mage/storage',
        'Magento_Checkout/js/model/payment-service',
        'Magento_Checkout/js/model/payment/method-converter',
        'Magento_Checkout/js/model/error-processor',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/select-billing-address',
        'Magento_Ui/js/modal/modal',
        'mage/url',
        'Magento_Checkout/js/action/set-shipping-information',
        'Magento_Checkout/js/model/shipping-rate-processor/new-address',
        'Magento_Checkout/js/action/create-shipping-address',
        'Magento_Checkout/js/action/select-shipping-address',
        'Magento_Checkout/js/checkout-data',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Epicor_SalesRep/epicor/salesrep/js/model/contact',
        'Epicor_Comm/epicor/comm/js/model/noticeList',
    ],
    function(
        $,
        ko,
        quote,
        resourceUrlManager,
        storage,
        paymentService,
        methodConverter,
        errorProcessor,
        fullScreenLoader,
        selectBillingAddressAction,
        modal,
        url,
        setShippingAction,
        defaultProcessor,
        createShippingAddress,
        selectShippingAddress,
        checkoutData,
        selectShippingMethodAction,
        rateRegistry,
        checkoutDataResolver,
        contact,
        messageContainer
    ) {
        'use strict';

        return {
            saveShippingInformation: function(locationCode, jsonData) {
                var payload;
                payload = {};
                payload['locationcode'] = locationCode;
                this.setBranchShippingInfo();
                var address = quote.shippingAddress();
                if(address) {
                    rateRegistry.set(address.getKey(), null);
                    rateRegistry.set(address.getCacheKey(), null);                
                }
                var isCustomerLoggedin = window.isCustomerLoggedIn;
                if (!isCustomerLoggedin) {
                    payload['email'] = $('#bcustomer-email').val();
                    payload['firstname'] = $('input[name="firstname"]').val();
                    payload['lastname'] = $('input[name="lastname"]').val();
                    jsonData.email = $('#bcustomer-email').val();
                    jsonData.lastname = $('input[name="lastname"]').val();
                    jsonData.firstname = $('input[name="firstname"]').val();
                    $('#customer-email').val(payload['email']);
                }
                payload['ecc_customer_order_ref'] = $('input[name="ecc_customer_order_ref"]').val();
                payload['ecc_tax_exempt_reference'] = $('input[name="ecc_tax_exempt_reference"]').val();
                if($('[name="ecc_ship_status_erpcode"]')) {
                    payload['ecc_ship_status_erpcode'] = $('[name="ecc_ship_status_erpcode"]').val();
                }

                if(window.checkoutConfig.isSalesRepContactenabled){
                    payload['salesrep_contact'] = jQuery('[name="salesrep_contact"]').val();
                }else{
                    payload['salesrep_contact'] = '';
                    
                }
                
                
                    // you can extract value of extension attribute from any place (in this example I use customAttributes approach)
                if($('#branch_shippingdates [name="ecc_required_date"]').attr('type')=="radio"){
                    payload['ecc_required_date'] = $('#branch_shippingdates [name="ecc_required_date"]:checked').val();                
                }else{
                    payload['ecc_required_date'] = $('#branch_shippingdates [name="ecc_required_date"]').val();
                }

                if(jQuery('.becc_required_date [name="ecc_required_date"]')) {
                    payload['ecc_required_date'] = $('.becc_required_date [name="ecc_required_date"]').val();
                }
            
                fullScreenLoader.startLoader();
                var self = this;
                this.saveNewAddress(jsonData);
                return storage.post(
                    url.build('branchpickup/pickup/SaveBranchInformation'),
                    payload,
                    false,
                    'application/x-www-form-urlencoded; charset=UTF-8'

                ).done(
                    function(response) {
                        var address = quote.shippingAddress();
                        var shippingCode = window.checkoutConfig.branchPickupCode;
                        //address.trigger_reload = new Date().getTime();
                        quote.setTotals(response.totals);
                        self.setBranchShippingInfo();
                        rateRegistry.set(address.getKey(), null);
                        rateRegistry.set(address.getCacheKey(), null);                        
                        checkoutData.setSelectedShippingRate(shippingCode + '_' + shippingCode);
                        paymentService.setPaymentMethods(methodConverter(response.payment_methods));
                        fullScreenLoader.stopLoader();
                        if(typeof response.extension_attributes !== "undefined" && response.extension_attributes.ecc_pay_error){
                            messageContainer.addNoticeMessage({
                                message: response.extension_attributes.ecc_pay_error
                            });
                        }

                        //OrderApproval message
                        if(typeof response.extension_attributes !== "undefined" &&  response.extension_attributes.is_approval_require){
                            messageContainer.addNoticeMessage({
                                message: response.extension_attributes.is_approval_require
                            });
                        }
                    }
                ).fail(
                    function(response) {
                        errorProcessor.process(response);
                        fullScreenLoader.stopLoader();
                    }
                );
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
            },
            getLocationData: function(locationCode) {

            },
            saveNewAddress: function(jsonData) {
                var addressData,
                    newShippingAddress;
                
                var shippingCode = window.checkoutConfig.branchPickupCode;
                
//                if(window.checkoutConfig.isSalesRep){                    
//                        jsonData.firstname = '';
//                        jsonData.lastname = '';
//                }
//                if(window.checkoutConfig.isSalesRepContactenabled){
//                    if ($('select[name="salesrep_contact"] option:selected').val()) {
//                        var contact_name = $('select[name="salesrep_contact"] option:selected').text();
//                        contact_name = contact_name.split(' ');
//                        jsonData.firstname = contact_name[0];
//                        jsonData.lastname = contact_name[1];                
//                    }else{
//                        jsonData.firstname = '';
//                        jsonData.lastname = '';
//                    }
//                }
                addressData = this.formatAddressBranch(jsonData);
                addressData.save_in_address_book = 0;
                newShippingAddress = createShippingAddress(addressData);
                selectShippingAddress(newShippingAddress);
                //selectBillingAddressAction(newShippingAddress);
                checkoutData.setSelectedShippingAddress(newShippingAddress.getKey());
                checkoutData.setNewCustomerShippingAddress(addressData);
                checkoutData.setSelectedShippingRate(shippingCode + '_' + shippingCode);
                //quote.shippingMethod('epicor_branchpickup_epicor_branchpickup');
                return true;
            },
            resetAddress: function(){
             //  checkoutData.setSelectedBillingAddress(null);
               checkoutData.setNewCustomerShippingAddress(null);
               //quote.billingAddress(null);
               selectShippingMethodAction(null);
               checkoutDataResolver.resolveShippingAddress();
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
            }
        };
    }
);