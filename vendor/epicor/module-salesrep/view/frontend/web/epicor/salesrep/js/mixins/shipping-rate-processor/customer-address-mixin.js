/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'Magento_Checkout/js/model/resource-url-manager',
        'Magento_Checkout/js/model/quote',
        'mage/storage',
        'Magento_Checkout/js/model/shipping-service',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/model/error-processor',
        'mage/cookies'
    ],
    function ($,resourceUrlManager, quote, storage, shippingService, rateRegistry, errorProcessor) {
        "use strict";
          return function(targetModule){
              var getRates = targetModule.getRates;
                targetModule.getRates = function(address){
                    
                    var checkArQuote = window.checkoutConfig.arPaymentCheckout;
                    if (typeof checkArQuote != 'undefined') {
                        return targetModule;
                    }                     
                
                     var current_key = address.getKey();
               /*         if (current_key.indexOf("erpaddress_") != -1){
                             var values = current_key.split("_");
                              if(values[1] !== void 0){ 
                                    $.cookie('erp_shipping_customer_addressId', "erpaddress_"+values[1], {expires: 365, path: '/' });
                                }
                        }             
                  */      
                      var call_overwrite = false;
                      var use_alternate_key = false;
                      if(address.customerAddressId ==null){
                          if(address.getErpCustomerAddressId().indexOf("erpaddress_") != -1){
                              current_key = address.getErpCustomerAddressId();
                                  call_overwrite = true;  
                                  use_alternate_key = true;
                          }
                      }else{
                            if (address && address.customerAddressId.indexOf("erpaddress_") != -1) 
                            {
                                call_overwrite = true;  
                            }
                    }  
                      if(!call_overwrite){ 
                                var result = getRates.apply(this, arguments);
                                return result;
                                
                      }else{
                                  if (current_key.indexOf("erpaddress_") != -1){
                                        var values = current_key.split("_");
                                         if(values[1] !== void 0){ 
                                               $.cookie('erp_shipping_customer_addressId', "erpaddress_"+values[1], {expires: 365, path: '/' });
                                           }
                                   }
                                        
                                shippingService.isLoading(true);
                                    var cache = rateRegistry.get(address.getKey());
                                    if (cache) {
                                        shippingService.setShippingRates(cache);
                                        shippingService.isLoading(false);
                                    } else { 
                                        var url = resourceUrlManager.getUrlForErpEstimationShippingMethodsByAddressId();
                                        var custom_id = address.customerAddressId;
                                        if(use_alternate_key){
                                            custom_id =  address.getErpCustomerAddressId();
                                        }
                                        
                                        storage.post(
                                            url,
                                            JSON.stringify({
                                                addressId: custom_id
                                            }),
                                            false
                                        ).done(
                                            function(result) {
                                                rateRegistry.set(address.getKey(), result);
                                                shippingService.setShippingRates(result);
                                            }

                                        ).fail(
                                            function(response) {
                                                shippingService.setShippingRates([]);
                                                errorProcessor.process(response);
                                            }
                                        ).always(
                                            function () {
                                                shippingService.isLoading(false);
                                            }
                                        );
                                    }
                                
                                return true; 
                      }  
                }
                return targetModule;
            };
    }
);
