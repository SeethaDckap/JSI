define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Customer/js/model/customer',
        'uiLayout',
        'mageUtils',
        'Epicor_BranchPickup/js/epicor/model/address-list',
        'Epicor_BranchPickup/js/epicor/model/save-branch-information',
        'Magento_Ui/js/modal/alert',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/select-shipping-address',
        'Epicor_BranchPickup/js/epicor/model/branch-common-utils',
        'mage/url',
        'Epicor_SalesRep/epicor/salesrep/js/model/contact',
        'mage/validation'
    ],
    function(
        $,
        ko,
        Component,
        _,
        stepNavigator,
        customer,
        layout,
        utils,
        addressList,
        saveBranchInformation,
        alertbox,
        fullScreenLoader,
        selectShippingAddressAction,
        Branchutils,
        url,
        contact
    ) {

        'use strict';
        return Component.extend({
            defaults: {
                template: 'Epicor_BranchPickup/shipping-address/footer',
            },
            /**
             * Set shipping information handler
             */
            setShippingInformation: function() {
                // Validate Required date Before Next
                var requireDateElement = this.containers[0].getChild("after-branch-pickup-address").getChild("becc_required_date");
                if(requireDateElement){
                    var isValid = requireDateElement.validate();
                    if (!isValid.valid) {
                        return false;
                    }
                }

                if(window.checkoutConfig.isSalesRep && window.checkoutConfig.isSalesRepContactReq){
                    if (!contact.selectedcontact()) {
                        alertbox({
                            title: 'Error',
                            content: 'Please Choose a Contact.'
                        });
                        return false;
                    }
                }
                //if (this.validateShippingInformation()) {
                var locationCode = this.branchPickupValidation();
                var isCustomerLoggedin = window.isCustomerLoggedIn;
                if (!isCustomerLoggedin) {
                    var validateFields = this.validateFields();
                    if (!validateFields) {
                        return false;
                    }
                }
                //check if customer orer reference is compulsory and the correct length
                if(!this.validateCheckoutPurchaseOrder()){
                    $('input[name="ecc_customer_order_ref"]').filter(":visible").focus();
                    alertbox({
                            title: 'Error',
                            content: 'Please enter a Valid Customer Order Reference'
                        });
                    return false;
                };  
                if (locationCode) {
                    var self = this;
                    fullScreenLoader.startLoader();
                    $.ajax({
                        showLoader: false,
                        data: {
                            locationcode: locationCode
                        },
                        url: url.build('branchpickup/pickup/changepickuplocation'),
                        type: "POST",
                    }).done(function(data) {
                        fullScreenLoader.stopLoader();
                        var jsonData = data.details;
                        saveBranchInformation.saveShippingInformation(locationCode, jsonData).done(
                            function() {
                                stepNavigator.next();
                                Branchutils.proceedtoNextStep();
                            }
                        );                                                
                    });

                } else {
                    alertbox({
                        title: 'Error',
                        content: 'Please select a location'
                    });
                }
                //}
            },
            validateFields: function() {
                var checkEmail = $('#bcustomer-email').val();
                var getEmail = this.validateEmail(checkEmail);
                var getFname = $('input[name="firstname"]').val();
                var getLname = $('input[name="lastname"]').val();
                if (!getEmail) {
                    $('#bcustomer-email').focus();
                } else if (!getFname) {
                    $('input[name="firstname').focus();
                } else if (!getLname) {
                    $('input[name="lastname').focus();
                }
                if (getEmail) {
                    $('#bcustomer-email-error').hide();
                }
                if (!getEmail || !getFname || !getLname) {
                    alertbox({
                        title: 'Error',
                        content: 'Please Fill the Required Fields'
                    });
                    return false;
                } else {
                    return true;
                }               
            },
            validateEmail: function(sEmail) {
                var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
                if (filter.test(sEmail)) {
                    return true;
                } else {
                    return false;
                }
            },            
            branchPickupValidation: function() {
                var locationCode = '';
                var containter = $("#branchpickup-addresses").find("div.selected-branchpickup-item");
                if (containter) {
                    locationCode = $('.selected-branchpickup-item').attr('data-custom');
                }
                return locationCode;
            },
            checkBranchPickupSelected: function() {
                var shippingAddress = window.checkoutConfig.selectedBranch;
                if(!shippingAddress) {
                    shippingAddress =false;
                }                
                return shippingAddress;
            },
            showShippingAddress: function() {
                //var address = window.checkoutConfig.defaultShippingAddress;
                //alert(console.log(address))
                //selectShippingAddressAction(address);
                saveBranchInformation.resetAddress();
                //$('#checkout-step-shipping').find('div[class="shipping-address-item not-selected-item"]').trigger('click');
                //$('[class="action action-select-shipping-item"]:first-child').trigger('click');
                Branchutils.showShippingAddress();
                return true;
            },

            showBranchPickupAddress: function() {
                Branchutils.showBranchPickupAddress();
                return true;
            },
            validateCheckoutPurchaseOrder : function () {
                //check admin values if field is compulsory and/or has min/max length
                var checkoutPurchaseOrder = $('input[name="ecc_customer_order_ref"]').filter(":visible");
                var minTextLength = window.checkoutConfig.eccCustomerOrderRefValidation['min_text_length'];
                var maxTextLength = window.checkoutConfig.eccCustomerOrderRefValidation['max_text_length'];
                var requiredEntry = window.checkoutConfig.eccCustomerOrderRefValidation['required-entry'];
                if(checkoutPurchaseOrder.val().length > maxTextLength || requiredEntry && checkoutPurchaseOrder.val().length < minTextLength){  
                   return false;
                }
                return true;
            }    
        });
    }
);