/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

if (typeof Epicor_DealerPricing == 'undefined') {
    var Epicor_DealerPricing = {};
}
var resetDiscountCheck = false;
var dealerPricing = 'dealerPricing';

require([
    'jquery',
    'mage/translate',
    'prototype',
    'domReady!'
], function (jQuery, $t) {
    Epicor_DealerPricing.dealerPricing = Class.create();
    Epicor_DealerPricing.dealerPricing.prototype = {

        initialize: function () {
        },
        extraFunction: function () {
        },
        inputPrice: "",
        inputDiscount: "",
        invalidPrice: "The price entered was too low",
        invalidDiscount: "The discount entered was too high",
        changeStack: [],
        stackCtr: 0,
        initialize: function () {
            var arr = $$('.dealer-container input');
            for (var i = 0, len = arr.length; i < len; i++) {
                arr[i].observe('focus', this.processFocus.bind(this));
                arr[i].observe('blur', this.processBlur.bind(this));
            };


            var resetdis = $$('.dealer-discount-container div .reset_discount_item');

            for (var i = 0, len = resetdis.length; i < len; i++) {
                resetdis[i].observe('click', this.resetdata.bind(this));
            }
            ;

        },

        initializeElement: function (element) {
            var arr = $(element).select('input');
            for (var i = 0, len = arr.length; i < len; i++) {
                arr[i].observe('focus', this.processFocus.bind(this));
                arr[i].observe('blur', this.processBlur.bind(this));
            }
        },

        processFocus: function (event) {
            var element = Event.element(event);
            var cartItemId = element.readAttribute('dealer-cartid');
            var priceElement = $$('#cart-item-' + cartItemId + ' .price')[0];
            var discountElement = $$('#cart-item-' + cartItemId + ' .dealer-discount')[0];
            if (!priceElement.hasAttribute('readonly')) {
                this.inputPrice = priceElement.value;
                this.inputDiscount = discountElement.value;
                var getType = {};
            }
        },
        processBlur: function (event) {
            var pricePrecision = window.checkout.priceFormat.requiredPrecision;
            var def = 'c';
            var element = Event.element(event);
            var cartItemId = element.readAttribute('dealer-cartid');
            var priceElement = $$('#cart-item-' + cartItemId + ' .price')[0];
            var discountElement = $$('#cart-item-' + cartItemId + ' .dealer-discount')[0];
            var marginElement = $$('#cus-cart-item-' + cartItemId + ' .customer-margin')[0];
            var cusPrice = priceElement.readAttribute('cus-price');
            priceElement.value = priceElement.value.replace(/[^0-9\.\-]/g, '');
            discountElement.value = discountElement.value.replace(/[^0-9\.\-]/g, '');
            if (!priceElement.hasAttribute('readonly')) {
                var startPrice = priceElement.readAttribute('orig-value');
                var currentPrice = priceElement.value;
                var origDiscount = discountElement.readAttribute('orig-value');
                var currentDiscount = discountElement.value;
                if (parseFloat(startPrice) == parseFloat(currentPrice) && parseFloat(origDiscount) == parseFloat(currentDiscount)) {
                    return;
                }
                switch (element.readAttribute('dealer-type')) {
                    case 'price':
                        var discount = parseFloat(startPrice - currentPrice).toFixed(pricePrecision);
                        if (discount != parseFloat(currentDiscount)) {
                            this.changeStack[this.stackCtr] = "change";
                            this.stackCtr++;
                            discountElement.value = discount;
                        }

                        break;
                    case 'discount':
                    default:
                        var price = parseFloat(startPrice - currentDiscount).toFixed(pricePrecision);
                        if (price != parseFloat(currentPrice)) {
                            this.changeStack[this.stackCtr] = "change";
                            this.stackCtr++;
                            priceElement.value = price;
                        }
                        break;
                }


                if (parseFloat(startPrice) !== parseFloat(priceElement.value)) {
                    if ($('reset_discount_' + cartItemId)) {
                        $('reset_discount_' + cartItemId).show();
                    }
                } else {
                    if ($('reset_discount_' + cartItemId)) {
                        $('reset_discount_' + cartItemId).hide();
                    }
                }

                if (typeof lines !== "undefined" && typeof lines.recalcLineTotals === "function") {
                    if(jQuery.inArray("change", this.changeStack) !== -1){
                        def = 'c';
                        marginElement.value = parseFloat(((priceElement.value - cusPrice)/priceElement.value)*100).toFixed(pricePrecision);
                    }else{
                        def = 'dc';

                    }
                    lines.recalcLineTotals(def);
                }
            }
        },
        resetDiscount: function (id) {
            if ($('cart-item-' + id)) {
                var pricePrecision = window.checkout.priceFormat.requiredPrecision;
                var web_price;
                var disc_price;
                var price_input = $('cart-item-' + id).select('[dealer-type="price"]').first();
                var discount_input = $('cart-item-' + id).select('[dealer-type="discount"]').first();
                if (price_input !== null && discount_input !== null) {
                    resetDiscountCheck = true;
                    web_price = price_input.readAttribute('orig-value');
                    disc_price = discount_input.readAttribute('orig-value');
                    price_input.focus();
                    price_input.value = parseFloat(web_price).toFixed(pricePrecision);
                    price_input.blur();
                    discount_input.focus();
                    discount_input.value = parseFloat(0).toFixed(pricePrecision);
                    discount_input.blur();
                    resetDiscountCheck = false;
                }
            }
        },
        togglePrice: function (id) {
            var pricePrecision = window.checkout.priceFormat.requiredPrecision;
            this.changeStack[this.stackCtr] = "toggle";
            this.stackCtr++;
            var currentMode = $('dealer-price-toggle') ? $('dealer-price-toggle').readAttribute('dealer') : window.checkout.dealerPriceMode;
            var canShowCusPrice = window.checkout.dealerCanShowCusPrice;
            var canShowMargin = window.checkout.dealerCanShowMargin;
            
            if (currentMode == "shopper") {
                if (jQuery('.dealer-price').hasClass('no-display')) {
                    jQuery('.dealer-price').removeClass('no-display');
                }
                if (!jQuery('.cus-price').hasClass('no-display')) {
                    jQuery('.cus-price').addClass('no-display');
                }
                if (jQuery('.col-dealer_price').hasClass('no-display')) {
                    jQuery('.col-dealer_price').removeClass('no-display');
                }
                if (!jQuery('.col-price').hasClass('no-display')) {
                    jQuery('.col-price').addClass('no-display');
                }
                jQuery('th.col-dealer_price > span').html('Price');

            }else{  
                if (jQuery('.cus-price').hasClass('no-display')) {
                    jQuery('.cus-price').removeClass('no-display');
                }
                if (jQuery('.dealer-price') && jQuery('.dealer-price').hasClass('no-display')) {
                    if(canShowCusPrice != "disable"){
                        jQuery('.dealer-price').removeClass('no-display');
                    }else{
                        jQuery('.dealer-price').addClass('no-display');
                    }
                }else {
                    if(canShowCusPrice == "disable"){
                        jQuery('.dealer-price').addClass('no-display');
                    }
                }
                if (jQuery('.col-price').hasClass('no-display')) {
                    jQuery('.col-price').removeClass('no-display');
                }
                if (jQuery('.col-dealer_price').hasClass('no-display')) {
                    if(canShowCusPrice != "disable"){
                        jQuery('.col-dealer_price').removeClass('no-display');
                    }
                }else{
                    if(canShowCusPrice == "disable"){
                        jQuery('.col-dealer_price').addClass('no-display');
                    }
                }
                jQuery('th.col-dealer_price > span').html('Customer Price');
            }

            if ($('cart-item-' + id)) {
                var web_price;
                var price_input = $('cart-item-' + id).select('[dealer-type="price"]').first();
                var discount_input = $('cart-item-' + id).select('[dealer-type="discount"]').first();
                var base_price = $('cart-item-' + id).select('.dp_base_price').first();
                if (price_input !== null) {

                    if (price_input.readAttribute('dealer') == 1) {
                        price_input.attributes['dealer'].nodeValue = 0;
                        web_price = price_input.readAttribute('cus-price');
                    } else {
                        price_input.attributes['dealer'].nodeValue = 1;
                        web_price = price_input.readAttribute('dealer-price');
                    }
                     if(jQuery('#line-search').css('display') == 'none')
                     {
                         price_input.focus();
                     }
                    base_price.value = parseFloat(price_input.readAttribute('orig-value')).toFixed(pricePrecision);
                    price_input.blur();
                }
            }
        },
        
        resetDealerPricing: function () {
            dealerPricing = new Epicor_DealerPricing.dealerPricing();
        }
    };
    dealerPricing = new Epicor_DealerPricing.dealerPricing();
    window.dealerPricing = dealerPricing;
});