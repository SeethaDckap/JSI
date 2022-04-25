/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
  'jquery',
  'mage/utils/wrapper',
  'Magento_Checkout/js/model/quote',
], function ($, wrapper, quote) {
  'use strict';

  return function (placeOrderAction) {

    /** Override default place order action and add agreement_ids to request */
    return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {

      var customerAddressId =  quote.billingAddress().customerAddressId;
      var checkArQuote = window.checkoutConfig.arPaymentCheckout;
      if (typeof checkArQuote != 'undefined') {
        quote.billingAddress().customerAddressId = 0;
        return originalAction(paymentData, messageContainer);
      }

      if (customerAddressId!= undefined && customerAddressId!==null && customerAddressId.indexOf("erpaddress_") !== -1)
      {
        $.cookie('erp_billing_customer_addressId', customerAddressId, {expires: 365, path: '/' });
        quote.billingAddress().customerAddressId = null;
      }else if($.cookie('erp_billing_customer_addressId') == 'new-address'){

      }
      return originalAction(paymentData, messageContainer);
    });
  };
});
