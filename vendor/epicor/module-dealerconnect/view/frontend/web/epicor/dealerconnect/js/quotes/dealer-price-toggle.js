/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

require(
        [
            'jquery',
            'mage/storage',
            'mage/url',
            'Magento_Catalog/js/price-utils',
            'prototype',
            'domReady'
        ],
        function (
                jQuery,
                storage,
                urlBuilder,
                priceUtils
                ) {
            'use strict';
            jQuery(function () {
                var isCtrl;
                var isAlt;
                var counter = 0;
                var altct;
                var ctrct;

                if ($('dealer-price-toggle')) {
                    jQuery(document).keydown(function (e) {
                        if (e.which === 18) {
                            isAlt = true;
                            altct = counter;
                        }
                        if (e.which === 17) {
                            isCtrl = true;
                            ctrct = counter;
                        }
                        if (e.which === 68 && isAlt && isCtrl && counter === ctrct + 1 && ctrct === altct + 1) {
                            togglePrice();
                            isAlt = false;
                            isCtrl = false;
                        }
                        counter++;

                    });

                    jQuery(document).keyup(function (e) {
                        counter = 0;
                    });
                    setTimeout(function(){
                        jQuery("#dealer-price-toggle").click(function(){
                            togglePrice();
                        });
                    }, 3000);
                }
            });

            function togglePrice() {
                storage.post(
                        urlBuilder.build('/dealerconnect/toggle'),
                        JSON.stringify({
                            check: true
                        }),
                        true
                        ).done(
                        function (responseText) {
                            if (responseText.mode == 'dealer') {
                                jQuery('#dealer-price-toggle img').attr('src', window.cusImgUrl);
                                jQuery('#dealer-price-toggle').attr('dealer', 'dealer');
                            } else if (responseText.mode == 'shopper') {
                                jQuery('#dealer-price-toggle img').attr('src', window.dealerImgUrl);
                                jQuery('#dealer-price-toggle').attr('dealer', 'shopper');
                            }
                            var url = window.location.href;
                            var canShowCusPrice = window.checkout.dealerCanShowCusPrice;
                            var canShowMargin = window.checkout.dealerCanShowMargin;
                            var showMiscCharge = window.checkout.showMiscCharge;
                            var isListEnabled = window.checkout.isListsEnabled;
                            if (window.checkout.dealerPortal === 1) {
                                var readAtt = responseText.mode == 'shopper' ? 'dealerprice' : 'cusprice';
                                if (responseText.mode == 'dealer') {
                                    jQuery('.col-original_value').removeClass("no-display");
                                    jQuery('.col-dealer_grand_total_inc').addClass("no-display");
                                    jQuery('.toggle_price').trigger('click');
                                    if (url.indexOf("order") !== -1) {
                                        jQuery('.col-line_value').removeClass("no-display");
                                        jQuery('.col-dealer_line_value').addClass("no-display");
                                        $('customerconnect_order_parts_table').down('.subtotal .price').update($('customerconnect_order_parts_table').down('.subtotal .price').readAttribute('orig'));
                                        $('customerconnect_order_parts_table').down('.grand_total .price').update($('customerconnect_order_parts_table').down('.grand_total .price').readAttribute('orig'));
                                        if(typeof $('customerconnect_order_parts_table').down('.shipping .price') !== 'undefined'){
                                            $('customerconnect_order_parts_table').down('.shipping .price').update($('customerconnect_order_parts_table').down('.shipping .price').readAttribute('orig'));
                                        }
                                    }
                                    if (url.indexOf("order") !== -1 || window.checkout.rfqEditable === 0) {
                                        jQuery('.col-price').removeClass("no-display");
                                        if (canShowCusPrice == "disable") {
                                            jQuery('.col-dealer_price').addClass("no-display");
                                        }
                                        jQuery('.col-dealer_price .dealerDisc').css("display", "block");
                                        if (canShowMargin != "disable") {
                                            jQuery('.col-price > span').html("Price<br>Margin");
                                            jQuery('.col-price .baseMargin').css("display", "block");
                                        } else {
                                            jQuery('.col-price > span').html("Price");
                                            jQuery('.col-price .baseMargin').css("display", "none");
                                        }
                                        jQuery('.col-dealer_price > span').html("Customer Price<br>Discount");
                                        //$('customerconnect_order_parts_table').down('.tax .price').update($('customerconnect_order_parts_table').down('.tax .price').readAttribute('orig'));
                                    }
                                } else if (responseText.mode == 'shopper') {
                                    jQuery('.col-original_value').addClass("no-display");
                                    jQuery('.col-dealer_grand_total_inc').removeClass("no-display");
                                    jQuery('.toggle_price').trigger('click');
                                    if (url.indexOf("order") !== -1) {
                                        $('customerconnect_order_parts_table').down('.subtotal .price').update($('customerconnect_order_parts_table').down('.dealer_subtotal .price').innerHTML);
                                        $('customerconnect_order_parts_table').down('.grand_total .price').update($('customerconnect_order_parts_table').down('.dealer_grand_total .price').innerHTML);
                                        if(typeof $('customerconnect_order_parts_table').down('.shipping .price') !== 'undefined'){
                                            $('customerconnect_order_parts_table').down('.shipping .price').update($('customerconnect_order_parts_table').down('.dealer_shipping .price').innerHTML);
                                        }
                                        jQuery('.col-line_value').addClass("no-display");
                                        jQuery('.col-dealer_line_value').removeClass("no-display");
                                    }
                                    if (url.indexOf("order") !== -1 || window.checkout.rfqEditable === 0) {
                                        jQuery('.col-dealer_price > span').html("Price");
                                        jQuery('.col-dealer_price .dealerDisc').css("display", "none");
                                        jQuery('.col-price .baseMargin').css("display", "none");
                                        jQuery('.col-price').addClass("no-display");
                                        jQuery('.col-price').addClass("no-display");
                                        jQuery('.col-dealer_price').removeClass("no-display");
                                        //$('customerconnect_order_parts_table').down('.tax .price').update(priceUtils.formatPrice(0, window.checkout.priceFormat));

                                    }

                                }
                                if(!showMiscCharge){
                                    if (url.indexOf("order") !== -1) {
                                        var colspan = isListEnabled ? 10 : 9;

                                        $$('#customerconnect_order_parts_table tfoot tr').each(function (row) {
                                            if (canShowCusPrice != "disable" && responseText.mode == "dealer") {
                                                row.down('td:first-child').writeAttribute('colspan', colspan);
                                            } else {
                                                row.down('td:first-child').writeAttribute('colspan', colspan - 1);
                                            }

                                        });
                                    } else {
                                        $$('#rfq_lines_table tfoot tr').each(function (row) {
                                            var dcolspan = window.checkout.rfqEditable === 0 ? 10 : 11;
                                            var ecolspan = window.checkout.rfqEditable === 0 ? 9 : 10;
                                            if(typeof row.down('td:first-child') !== "undefined"){
                                                if (canShowCusPrice != "disable" && responseText.mode == "dealer") {
                                                    row.down('td:first-child').writeAttribute('colspan', dcolspan);
                                                } else {
                                                    row.down('td:first-child').writeAttribute('colspan', ecolspan);
                                                }
                                            }
                                        });
                                    }
                                }

                                if(typeof lines !== "undefined"){
                                    if (typeof lines.recalcLineTotals === "function" && window.checkout.rfqEditable === 1) {
                                        lines.recalcLineTotals('0');
                                    } else {
                                        lines.totalsDisplay(jQuery('.totals'), responseText.mode);
                                    }
                                }

                                jQuery('#linesearch-iframe').contents().find('#qop-list tbody tr').each(function (){
                                    this.down('span.price').update((this.down('span.price').readAttribute(readAtt)),window.checkout.priceFormat);
                                });
                                
                                if (window.checkout.rfqEditable === 0) {
                                    $$('#rfq_lines_table tbody tr.lines_row:not(.attachment)').each(function (row) {
                                        row.down('.lines_line_value_display').update(priceUtils.formatPrice(parseFloat(row.down('.lines_line_value').readAttribute(readAtt)).toFixed(2), window.checkout.priceFormat));
                                    });
                                }

                            }
                        }
                ).fail(
                        function (response) {
                            console.log(response);
                        }
                );
            }

        });

