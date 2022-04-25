/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define(
   ['jquery', 'Magento_Ui/js/modal/alert', 'Magento_Checkout/js/model/full-screen-loader'],
   function($, alert, fullScreenLoader) {
      var error_alert_params = {
         title: 'Payment Error',
         content: 'An error occurred while attempting to verify payment information. '
      };
      return {
         method_code: 'esdm',
         sendRequest: function(url, data_to_post, submit_order) {
            var self = this;
            $.ajax(url, {
               type: 'POST',
               dataType: 'json',
               data: data_to_post,
               success: function(response) {
                  var obj = response;
                  var errorStep = obj.errorStep;
                  var skipError = obj.skipError;
                  var errorMsg = obj.errorMsg;
                  if (errorStep != undefined && skipError == undefined) {
                     fullScreenLoader.stopLoader();
                     var error_message = errorStep + " failed" + errorMsg + ' Please try again or choose another payment method';
                     alert({
                        title: error_alert_params.title,
                        content: error_alert_params.content + $.mage.__(error_message)
                     });
                  } else {
                     fullScreenLoader.stopLoader();
                     $('#' + self.method_code + '_cc_number').add('#' + self.method_code + '_expiration').add('#' + self.method_code + '_expiration_yr').removeAttr('name');
                     submit_order();
                  }
               },
               error: function(jqXHR) {
                  fullScreenLoader.stopLoader();
                  var error_message;
                  error_message = 'Please try again or choose another payment method';
                  alert({
                     title: error_alert_params.title,
                     content: error_alert_params.content + $.mage.__(error_message)
                  });
               }
            });
         },
      };
   });