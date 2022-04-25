/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

if (typeof Epicor_SalesrepPricing == 'undefined') {
    var Epicor_SalesrepPricing = {};
}
var resetDiscountCheck = false;
var salesrepPricing = 'salesrepPricing';
require([
    'jquery',
    'mage/translate',
    'prototype',
    'domReady!'
], function (jQuery, $t) {
    Epicor_SalesrepPricing.salesrepPricing = Class.create();
    Epicor_SalesrepPricing.salesrepPricing.prototype = {

        initialize: function () {

        },
        extraFunction: function () {
        },
        inputPrice: "",
        inputDiscount: "",
        invalidPrice: "The price entered was too low",
        invalidDiscount: "The discount entered was too high",
        pricePrecision: window.checkout.priceFormat.requiredPrecision,
        initialize: function () {
            var arr = $$('.salesrep-discount-container input');
            for (var i = 0, len = arr.length; i < len; i++) {
                if (arr[i].type == 'checkbox') {
                    arr[i].observe('click', this.processEdit.bind(this));
                } else {
                    arr[i].observe('focus', this.processFocus.bind(this));
                    arr[i].observe('blur', this.processBlur.bind(this));
                }
            }
            ;


            var resetdis = $$('.salesrep-discount-container div .reset_discount_item');

            for (var i = 0, len = resetdis.length; i < len; i++) {
                resetdis[i].observe('click', this.resetdata.bind(this));
            }
            ;

        },
        resetdata: function (event) {
            var element = Event.element(event);
            var id = element.readAttribute('salesrep-cartid');
            if ($('cart-item-' + id)) {
                var price_input = $('cart-item-' + id).select('[salesrep-type="price"]').first();
                if (price_input !== null) {
                    resetDiscountCheck = true;
                    var web_price = price_input.readAttribute('web-price-value');
                    price_input.focus();
                    price_input.value = parseFloat(web_price);
                    price_input.blur();
                    resetDiscountCheck = false;
                    if ($('cart-item-' + id).hasClassName('salesrep-cart') && parseFloat(price_input.value) != parseFloat(price_input.readAttribute('orig-value'))) {
                        if ($('loading-mask')) {
                            $('loading-mask').show();
                        } else {
                            $(document.body).insert('<div id="loading-mask" class="loading-mask"></div>');
                        }

                        if (!$('loading_mask_loader') && $('salesrep_loading_image')) {
                            $('loading-mask').insert('<p class="loader" id="loading_mask_loader"><img src="' + $('salesrep_loading_image').value + '" alt="' + $t('Loading...') + '"><br />' + $t('Please Wait...') + '</p>');
                        }
                        jQuery('[name="update_cart_action"]').click();
                    }
                }
            }
        },
        initializeElement: function (element) {
            var arr = $(element).select('input');
            for (var i = 0, len = arr.length; i < len; i++) {
                if (arr[i].type == 'checkbox') {
                    arr[i].observe('click', this.processEdit.bind(this));
                } else {
                    arr[i].observe('focus', this.processFocus.bind(this));
                    arr[i].observe('blur', this.processBlur.bind(this));
                }
            }
        },
        processEdit: function (event) {
            var element = Event.element(event);
            var cartItemId = element.readAttribute('salesrep-cartid');

            var priceElement = $$('#cart-item-' + cartItemId + ' .price')[0];
            var discountElement = $$('#cart-item-' + cartItemId + ' .discount')[0];
            if (priceElement.hasAttribute('readonly')) {
                priceElement.removeAttribute('readonly');
                discountElement.removeAttribute('readonly');
                priceElement.removeClassName('disabled');
                discountElement.removeClassName('disabled');
                this.processFocus(event);
                this.processBlur(event);
            } else {
                priceElement.setAttribute('readonly', 'readonly');
                discountElement.setAttribute('readonly', 'readonly');
                priceElement.addClassName('disabled');
                discountElement.addClassName('disabled');
                priceElement.value = priceElement.readAttribute('orig-value');
                discountElement.value = discountElement.readAttribute('orig-value');
            }
        },
        processFocus: function (event) {

            var element = Event.element(event);
            var cartItemId = element.readAttribute('salesrep-cartid');
            var priceElement = $$('#cart-item-' + cartItemId + ' .price')[0];
            var discountElement = $$('#cart-item-' + cartItemId + ' .discount')[0];
            if (!priceElement.hasAttribute('readonly')) {
                this.inputPrice = priceElement.value;
                this.inputDiscount = discountElement.value;
                var getType = {};
            }
        },
        processBlur: function (event) {
            var element = Event.element(event);
            var cartItemId = element.readAttribute('salesrep-cartid');
            var priceElement = $$('#cart-item-' + cartItemId + ' .price')[0];
            var discountElement = $$('#cart-item-' + cartItemId + ' .discount')[0];
            priceElement.value = priceElement.value.replace(/[^0-9\.\-]/g, '');
            discountElement.value = discountElement.value.replace(/[^0-9\.\-]/g, '');
            if (!priceElement.hasAttribute('readonly')) {
                var startPrice = priceElement.readAttribute('orig-value');
                var webPrice = priceElement.readAttribute('web-price-value');
                var origPrice = priceElement.readAttribute('base-value');
                var minPrice = priceElement.readAttribute('min-value');
                var currentPrice = priceElement.value;
                var origDiscount = discountElement.readAttribute('orig-value');
                var maxDiscount = discountElement.readAttribute('max-value');
                var currentDiscount = discountElement.value;

                if (parseFloat(startPrice) == parseFloat(currentPrice) && parseFloat(origDiscount) == parseFloat(currentDiscount)) {
                    return;
                }

                switch (element.readAttribute('salesrep-type')) {
                    case 'price':
                        if (resetDiscountCheck == false && currentPrice * 1 < minPrice * 1) {
                            currentPrice = minPrice;
                            priceElement.value = Math.round(currentPrice * 100) / 100;
                            alert($t(this.invalidPrice));
                        }

                        var discount = (1 - ((currentPrice * 100) / (origPrice * 100))) * 100;
                        discount = parseFloat((discount * 100) / 100).toFixed(2);

                        if (discount != parseFloat(currentDiscount)) {
                            discountElement.value = discount;
                        }

                        break;
                    case 'discount':
                    default:
                        if (resetDiscountCheck == false && currentDiscount * 1 > maxDiscount * 1) {
                            currentDiscount = maxDiscount;
                            discountElement.value = Math.round(currentDiscount * 100) / 100;
                            alert($t(this.invalidDiscount));
                        }
                        origPrice = origPrice * 100;
                        currentDiscount = currentDiscount * 100;

                        var price = (origPrice - ((origPrice / 100) * currentDiscount) / 100) / 100;
                        price = parseFloat(price).toFixed(this.pricePrecision);
                        if (price != parseFloat(currentPrice)) {
                            priceElement.value = price;
                        }
                        break;
                }


                if (parseFloat(webPrice) != parseFloat(priceElement.value)) {
                    if ($('reset_discount_' + cartItemId)) {
                        $('reset_discount_' + cartItemId).show();
                    }
                } else {
                    if ($('reset_discount_' + cartItemId)) {
                        $('reset_discount_' + cartItemId).hide();
                    }
                }

                if (typeof lines !== "undefined" && typeof lines.recalcLineTotals === "function") {
                    lines.recalcLineTotals();
                }
            }
        },
        resetDiscount: function (id) {
            if ($('cart-item-' + id)) {
                var price_input = $('cart-item-' + id).select('[salesrep-type="price"]').first();
                if (price_input !== null) {
                    resetDiscountCheck = true;
                    var web_price = price_input.readAttribute('web-price-value');
                    price_input.focus();
                    price_input.value = parseFloat(web_price);
                    this.resetDiscountTotalOnClick(price_input, id);
                    resetDiscountCheck = false;
                    if ($('cart-item-' + id).hasClassName('salesrep-cart') && parseFloat(price_input.value) != parseFloat(price_input.readAttribute('orig-value'))) {
                        if($('window-overlay')){
                            $('window-overlay').show();
                        }
                        if ($('loading-mask')) {
                            $('loading-mask').show();
                        } else {
                            $(document.body).insert('<div id="loading-mask" class="loading-mask"></div>');
                        }

                        if (!$('loading_mask_loader') && $('salesrep_loading_image')) {
                            $('loading-mask').insert('<p class="loader" id="loading_mask_loader"><img src="' + $('salesrep_loading_image').value + '" alt="' + $t('Loading...') + '"><br />' + $t('Please Wait...') + '</p>');
                        }
                        $$('[name="update_cart_action"]').first().click();
                    }
                }
            }
        },
        resetDiscountTotalOnClick: function (element, id) {
            var cartItemId = element.readAttribute('salesrep-cartid');
            var priceElement = $$('#cart-item-' + cartItemId + ' .price')[0];
            var discountElement = $$('#cart-item-' + cartItemId + ' .discount')[0];
            priceElement.value = priceElement.value.replace(/[^0-9\.\-]/g, '');
            discountElement.value = discountElement.value.replace(/[^0-9\.\-]/g, '');
            if (!priceElement.hasAttribute('readonly')) {
                var startPrice = priceElement.readAttribute('orig-value');
                var webPrice = priceElement.readAttribute('web-price-value');
                var origPrice = priceElement.readAttribute('base-value');
                var minPrice = priceElement.readAttribute('min-value');
                var currentPrice = priceElement.value;
                var origDiscount = discountElement.readAttribute('orig-value');
                var maxDiscount = discountElement.readAttribute('max-value');
                var currentDiscount = discountElement.value;

                if (parseFloat(startPrice) == parseFloat(currentPrice) && parseFloat(origDiscount) == parseFloat(currentDiscount)) {
                    return;
                }

                switch (element.readAttribute('salesrep-type')) {
                    case 'price':
                        if (resetDiscountCheck == false && currentPrice * 1 < minPrice * 1) {
                            currentPrice = minPrice;
                            priceElement.value = Math.round(currentPrice * 100) / 100;
                            alert($t(this.invalidPrice));
                        }

                        var discount = (1 - ((currentPrice * 100) / (origPrice * 100))) * 100;
                        discount = parseFloat((discount * 100) / 100).toFixed(2);

                        if (discount != parseFloat(currentDiscount)) {
                            discountElement.value = discount;
                            if (!$('cart-item-' + id).hasClassName('salesrep-cart')){
                                    priceElement.setAttribute("orig-value", currentPrice); 
                                    discountElement.setAttribute("orig-value", discount); 
                            }
                        }

                        break;
                    case 'discount':
                    default:
                        if (resetDiscountCheck == false && currentDiscount * 1 > maxDiscount * 1) {
                            currentDiscount = maxDiscount;
                            discountElement.value = Math.round(currentDiscount * 100) / 100;
                            alert($t(this.invalidDiscount));
                        }
                        origPrice = origPrice * 100;
                        currentDiscount = currentDiscount * 100;

                        var price = (origPrice - ((origPrice / 100) * currentDiscount) / 100) / 100;
                        price = parseFloat(price).toFixed(this.pricePrecision);
                        if (price != parseFloat(currentPrice)) {
                            priceElement.value = price;
                        }
                        break;
                }


                if (parseFloat(webPrice) != parseFloat(priceElement.value)) {
                    if ($('reset_discount_' + cartItemId)) {
                        $('reset_discount_' + cartItemId).show();
                    }
                } else {
                    if ($('reset_discount_' + cartItemId)) {
                        $('reset_discount_' + cartItemId).hide();
                    }
                }

                if (typeof lines !== "undefined" && typeof lines.recalcLineTotals === "function") {
                    lines.recalcLineTotals();
                }
            }
        },        
        
        resetSalesRepPricing: function () {
            salesrepPricing = new Epicor_SalesrepPricing.salesrepPricing();
        }
    };
    
    //document.observe('dom:loaded', function () {
        salesrepPricing = new Epicor_SalesrepPricing.salesrepPricing();
        window.salesrepPricing = salesrepPricing;
    //});
});