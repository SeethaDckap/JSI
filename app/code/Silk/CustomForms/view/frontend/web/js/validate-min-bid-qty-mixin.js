define(['jquery'], function($) {
    'use strict';
  
    return function() {
      $.validator.addMethod(
        'validate-min-bid-qty',
        function(value, element) {
            console.log($(".min-amount").text());
            var minAmount = $(".min-amount").text();
            return value >= minAmount;
        },
        $.mage.__('Quantity must be greater than or equal to Min Bid Qty')
      )
    }
});