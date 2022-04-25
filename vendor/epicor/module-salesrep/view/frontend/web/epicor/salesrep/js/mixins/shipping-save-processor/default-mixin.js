/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define,alert*/
define(
    [
        'ko',
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/action/select-billing-address',
        'Epicor_Comm/epicor/comm/js/model/noticeList',
    ],
    function (
        ko,
        quote,
        selectBillingAddressAction,
        messageContainer
    ) {
        'use strict';
       return function(targetModule){
              var saveShippingInformation = targetModule.saveShippingInformation;
                targetModule.saveShippingInformation = function(){
                   if(window.checkoutConfig.isSalesRep){
                        var  erp_address_shipping =  quote.shippingAddress();
                        var  erp_address_billing =  quote.billingAddress();
                        var temp_ship_custAddressId = erp_address_shipping.customerAddressId;

                         if (temp_ship_custAddressId && temp_ship_custAddressId.indexOf("erpaddress_") != -1){
                              quote.shippingAddress().customerAddressId=null;
                         }
                         if(erp_address_billing){
                             var temp_bill_custAddressId = erp_address_billing.customerAddressId;
                              if (temp_bill_custAddressId && temp_bill_custAddressId.indexOf("erpaddress_") != -1){
                                  quote.billingAddress().customerAddressId=null;
                               }
                         }
                        var result = saveShippingInformation.apply(this, arguments);
                        
                         if(erp_address_billing){ 
                              if (temp_bill_custAddressId && temp_bill_custAddressId.indexOf("erpaddress_") != -1){
                                quote.billingAddress().customerAddressId=temp_bill_custAddressId;
                              }
                          }
                        if (temp_ship_custAddressId && temp_ship_custAddressId.indexOf("erpaddress_") != -1){
                            quote.shippingAddress().customerAddressId=temp_ship_custAddressId;
                        }
                        if (erp_address_billing) {
                                selectBillingAddressAction(quote.billingAddress());
                        }else{
                            if (temp_ship_custAddressId && temp_ship_custAddressId.indexOf("erpaddress_") == 0) {
                                quote.shippingAddress().customerAddressId = null;
                                selectBillingAddressAction(quote.shippingAddress());
                            } else {
                                selectBillingAddressAction(quote.shippingAddress());
                            }                  
                        }
                    }else{
                       if(window.checkoutConfig.isMasqurading) {
                           var  erp_address_shipping =  quote.shippingAddress();
                           var  erp_address_billing =  quote.billingAddress();
                           var temp_ship_custAddressId = erp_address_shipping.customerAddressId;
                           if(erp_address_billing){
                               var temp_bill_custAddressId = erp_address_billing.customerAddressId;
                               if (temp_bill_custAddressId && temp_bill_custAddressId.indexOf("erpaddress_") != -1){
                                   quote.billingAddress().customerAddressId=null;
                               }
                           }

                           if (temp_ship_custAddressId && temp_ship_custAddressId.indexOf("erpaddress_") != -1){
                               quote.shippingAddress().customerAddressId=null;
                           }
                       }
                        var result = saveShippingInformation.apply(this, arguments);
                        selectBillingAddressAction(quote.billingAddress());
                    }
                    result.then(function handlePayMsg(response) {
                        if(typeof response.extension_attributes !== "undefined" &&  response.extension_attributes.ecc_pay_error){
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
                    });
                    return result;
                }
                return targetModule;
            };
    }         
);
