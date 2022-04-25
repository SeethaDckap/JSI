/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'ko',
        'Magento_Checkout/js/action/select-shipping-method',
        'Magento_Checkout/js/model/checkout-data-resolver',
        'Magento_Checkout/js/model/step-navigator'
    ],
    function (ko,selectShippingMethodAction,checkoutDataResolver) {
        "use strict";
        var shippingRates = ko.observableArray([]),
            radioChecked = jQuery('#branchpickupshipping').prop('checked');
        return {
            isLoading: ko.observable(false),
            /**
             * Set shipping rates
             *
             * @param ratesData
             */
            setShippingRates: function(ratesData) {
                for(var i=0; i<ratesData.length; i++){
                    if(ratesData[i].carrier_code == window.checkoutConfig.branchPickupCode){
                        if(radioChecked){
                            selectShippingMethodAction(ratesData[i])
                        }
                        ratesData.splice(i, 1);  //removes 1 element at position i 
                        break;
                    }
                }                  
                shippingRates(ratesData);
                shippingRates.valueHasMutated();
                checkoutDataResolver.resolveShippingRates(ratesData);
            },

            /**
             * Get shipping rates
             *
             * @returns {*}
             */
            getShippingRates: function() {
                return shippingRates;
            }
        };
    }
);