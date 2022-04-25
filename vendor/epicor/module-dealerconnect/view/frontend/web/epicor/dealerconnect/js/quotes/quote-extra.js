/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (typeof Epicor_dealerExtra == 'undefined') {
    var Epicor_dealerExtra = {};
}
require([
    'jquery',
    'prototype'
], function (jQuery) {

    Epicor_dealerExtra.dealerExtra = Class.create();
    Epicor_dealerExtra.dealerExtra.prototype = {

        initialize: function () {
        },
        dealerCloneLineRow: function (row, product) {
            if (row.down('.dealer-discount')) {
                var discountField = row.down('.dealer-discount');
                var priceField = row.down('.lines_price');
                product.dealer_price_title = priceField.readAttribute('title');
                product.dealer_discount_title = discountField.readAttribute('title');
                
            }

            return row;

        },
        dealerAddLineRow: function (rowid, row, product, requires_configuration) {
            var rowDealerClass = row.down('.dealer-container').parentNode.className;
            var currentMode = $('dealer-price-toggle') ? $('dealer-price-toggle').readAttribute('dealer') : window.checkout.dealerPriceMode;
            var canShowCusPrice = window.checkout.dealerCanShowCusPrice;
            var canShowMargin = window.checkout.dealerCanShowMargin;
            if (typeof row.down('.customer-container') !== 'undefined') {
                basePriceparent = row.down('.customer-container').parentNode;
                var rowCustomerClass = row.down('.customer-container').parentNode.className;
            } else {
                basePriceparent = row.down('.lines_price').parentNode;
                var rowCustomerClass = row.down('.lines_price').parentNode.className;
            }
            dealerPriceparent = row.down('.dealer-container').parentNode;
            if (!requires_configuration && !product.is_custom && !valueEmpty(product.dealer_price_title)) {

                var is_dealer = currentMode == "shopper" ? 1 : 0;
                if (row.hasClassName('new')) {
                    rowname = 'new';
                } else {
                    rowname = 'existing';
                }

                replacerow = true;

                var pricePrecision = window.checkout.priceFormat.requiredPrecision;
                var discountValue = parseFloat(0).toFixed(pricePrecision);
                var base_price;
                var use_price;
                var cus_price = parseFloat(product.use_price).toFixed(pricePrecision);
                var dealer_price = parseFloat(product.dealer_price).toFixed(pricePrecision);
                var marginValue = (dealer_price == cus_price && dealer_price == 0.00)? parseFloat(0).toFixed(pricePrecision) : parseFloat(((dealer_price - cus_price) / dealer_price) * 100).toFixed(pricePrecision);
                use_price = parseFloat(product.dealer_price).toFixed(pricePrecision);
                base_price = use_price;

                if (replacerow) {
                    dealerPriceparent.innerHTML = '';
                    basePriceparent.innerHTML = '';
                    var resetStyle = (parseFloat(base_price) == parseFloat(use_price)) ? 'style="display:none"' : '';
                    var resetMargin = (canShowMargin == 'disable') ? 'style="display:none"' : '';

                    dealerHtml = '<div class="dealer-container" id="cart-item-' + rowid + '">';
                    dealerHtml += '<span class="discount-currency left">' + $('quote_currency_symbol').value + '</span><input type="text" dealer-cartid="' + rowid + '" dealer-type="price" name="lines[' + rowname + '][' + rowid + '][dealer_price_inc]" orig-value="' + use_price + '"cus-price="' + cus_price + '" dealer-price="' + dealer_price + '" dealer="' + is_dealer + '" value="' + use_price + '" size="12" title="' + product.dealer_price_title + '" class="input-text price lines_price no_update disabled" maxlength="20" />';
                    dealerHtml += '<div>' + '<span class="left">' + product.dealer_discount_title + '</span><input type="text" dealer-cartid="' + rowid + '" dealer-type="discount" name="lines[' + rowname + '][' + rowid + '][dealer-discount]" orig-value="0" size="4" title="' + product.dealer_discount_title + '" class="input-text dealer-discount disabled" maxlength="12" style="width:86px !important;float:left;" value="' + discountValue + '"/></div>';
                    dealerHtml += '<input type="hidden" class="dp_base_price" value="' + base_price + '" name="lines[' + rowname + '][' + rowid + '][dp_base_price]"/>';
                    dealerHtml += '<div id="reset_discount_' + rowid + '" ' + resetStyle + '><a class="reset_discount_line_level" href="javascript:dealerPricing.resetDiscount(\'' + rowid + '\')">' + jQuery.mage.__('Revert to Web Price') + '</a></div>';
                    dealerHtml += '<div  id="toggle_price_' + rowid + '" style="display:none" class = "toggle_price" onclick="javascript:dealerPricing.togglePrice(\'' + rowid + '\')"></div>';
                    dealerHtml += '<span class="lines_price_display" style="display:none"></span>';
                    dealerHtml += '</div>';
                    dealerPriceparent.innerHTML = dealerHtml;

                    baseHtml = '<div class="customer-container" id="cus-cart-item-' + rowid + '">';
                    baseHtml += '<span class="discount-currency left">' + $('quote_currency_symbol').value + '</span><input readonly type="text" customer-cartid="' + rowid + '" customer-type="price" name="lines[' + rowname + '][' + rowid + '][price]"  value="' + cus_price + '" size="12" title="' + product.dealer_price_title + '" class="input-text price lines_base_price no_update disabled" maxlength="20" />';
                    baseHtml += '<div ' + resetMargin + '>' + '<span class="left">Margin <span>%</span></span><input readonly type="text" customer-cartid="' + rowid + '" customer-type="margin" name="lines[' + rowname + '][' + rowid + '][customer-discount]" orig-value="0" size="4" title="' + product.dealer_discount_title + '" class="input-text customer-margin disabled" maxlength="12" style="width:86px !important;float:left;" value="' + marginValue + '"/></div>';
                    baseHtml += '<span class="lines_price_display" style="display:none"></span>';
                    baseHtml += '</div>';
                    basePriceparent.innerHTML = baseHtml;

                    row.down('.lines_price').writeAttribute('value', parseFloat(product.dealer_price).toFixed(pricePrecision));
                    row.down('.lines_price_display').update(product.formatted_price);
                }

            } else if (!valueEmpty(product.dealer_price_title) || product.is_custom) {

                dealerPriceparent.innerHTML = '';
                basePriceparent.innerHTML = '';

                dealerHtml = '<div class="dealer-container">TBA</div>';
                dealerHtml += '<div  id="toggle_price_' + rowid + '" style="display:none" class = "toggle_price" onclick="javascript:dealerPricing.togglePrice(\'' + rowid + '\')"></div>';
                baseHtml = '<div class="customer-container">TBA</div><input type="hidden" class="lines_price no_update" value="" name="" />';

                dealerPriceparent.innerHTML = dealerHtml;
                basePriceparent.innerHTML = baseHtml;
            }

            dealerPricing.initializeElement(dealerPriceparent);
            if (currentMode == "shopper") {
                if (row.down('.customer-container')) {
                    row.down('.customer-container').parentNode.className = rowCustomerClass + " no-display";
                }
                row.down('.dealer-container').parentNode.className = rowDealerClass.replace("no-display", "");
            } else {
                if (canShowCusPrice != "disable") {
                    row.down('.dealer-container').parentNode.className = rowDealerClass.replace("no-display", "");
                }
            }
            return row;
        }
    };
    var dealerExtra = 'rfqExtra';
    var lines = 'lines';
    dealerExtra = new Epicor_dealerExtra.dealerExtra();
    window.dealerExtra = dealerExtra;
    if ($('rfq_save')) {
        window.lines.addRowProcessor(dealerExtra.dealerAddLineRow);
        window.lines.addRowCloneProcessor(dealerExtra.dealerCloneLineRow);
    }

});
