/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
require([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Magento_Customer/js/customer-data',
    'domReady!'
], function ($, confirmation, customerData) {
    'use strict';
    $(document).ready(function () {
        if (window.checkout) {
            recentPurchasesCheck();

            if (window.checkout.cartReorderOption == 'prompt') {
                $('.link-reorder').on('click', function (e) {
                    //don't display the prompt if there are no items in the cart
                    var items = $('.minicart-wrapper span.counter-number').text();
                    if (items == "" || items == "0" ) {
                        return;
                    }
                    //get href from current target
                    var currentTarget = $(e.currentTarget);
                    var url = currentTarget.attr('href');
                    var datapost;
                    //if the href url = #, check if a data-post value is set, if so use that
                    if (url == '#') {
                        datapost = currentTarget.attr('data-post');
                        if (datapost) {
                            url = JSON.parse(datapost).action;
                        }
                    }
                    // if url still undefined, look to parent for form action
                    if (url == undefined) {
                        url = $(e.currentTarget).parent().attr('action');
                    }
                    e.preventDefault();
                    e.stopPropagation();
                    confirmation({
                        title: $.mage.__('Reorder Confirmation'),
                        content: 'Do you want to clear existing items from your cart?',
                        buttons: [{
                            text: $.mage.__('Yes'),
                            class: 'action-primary action-accept',
                            click: function (event) {
                                this.closeModal(event, true);
                                url = url + "?cartClearConfirm=1";
                                if (Object.keys(urldata).length > 0) {
                                    url = url + "&recentpurchasesorderqty="+btoa(JSON.stringify(urldata));
                                }
                                window.location.href = url;
                            }
                        }, {
                            text: $.mage.__('Merge Cart'),
                            class: 'action primary action-no',
                            click: function (event) {
                                this.closeModal(event, true);
                                url = url + "?cartClearConfirm=0";
                                if (Object.keys(urldata).length > 0) {
                                    url = url + "&recentpurchasesorderqty="+btoa(JSON.stringify(urldata));
                                }
                                window.location.href = url;
                            }
                        }, {
                            text: $.mage.__('Cancel'),
                            class: 'action-secondary action-dismiss',
                            click: function (event) {
                                this.closeModal(event);
                            }
                        }
                        ]
                    });
                })
            }else{
                if (window.location.pathname.indexOf('/customerconnect/recentpurchases/') > -1) {
                    $('.link-reorder').on('click', function (e) {
                        //only perform if recentpurchases and urldata has elements
                        if (Object.keys(urldata).length > 0) {
                            e.preventDefault();
                            e.stopPropagation();
                            var url = $(e.currentTarget).attr('href');
                            url = url + "?recentpurchasesorderqty="+btoa(JSON.stringify(urldata)) ;
                            window.location.href = url;
                        }
                    })
                }
            }

        }

        jQuery('#customerconnect_rph_massaction-form button').on('click', function(el){
            el.preventDefault();
            el.stopPropagation();
            var massActionData = getSavedMassActionData();
            //if nothing selected, use default massaction process to display message

            if (massActionData.length == 0 || jQuery('#customerconnect_rph_massaction-select').val() == "" ){
                customerconnect_rph_massactionJsObject.apply();
                return;
            }
            //if nothing in cart, don't show popup
            var items = $('.minicart-wrapper span.counter-number').text();
            if (items == "" || items == "0" ) {
                items = 0;
            }else{
                items = parseInt(items);
            }
            if (window.checkout.cartReorderOption == 'prompt' && items > 0) {
                confirmation({
                    title: $.mage.__('Reorder Confirmation'),
                    content: 'Do you want to clear existing items from your cart?',
                    buttons: [{
                        text: $.mage.__('Yes'),
                        class: 'action-primary action-accept',
                        click: function (event) {
                            this.closeModal(event, true);
                            //apply massaction data to massaction form
                            addMassActionRows(massActionData);
                            //clear existing cart
                            jQuery('#customerconnect_rph_massaction-form').append('<input type="hidden" name="cartClearConfirm" id="cartClearConfirm" value="1" >');

                            //clear localstorage massactiondata
                            localStorage.removeItem("massactiondata");
                            //continue with original massaction processing
                            customerconnect_rph_massactionJsObject.apply();

                        }
                    }, {
                        text: $.mage.__('No'),
                        class: 'action primary action-no',
                        click: function (event) {
                            this.closeModal(event, true);
                            //apply massaction data to massaction form
                            addMassActionRows(massActionData);
                            //don't clear existing cart
                            jQuery('#customerconnect_rph_massaction-form').append('<input type="hidden" name="cartClearConfirm" id="cartClearConfirm" value="0" >');
                            //clear localstorage massactiondata
                            localStorage.removeItem("massactiondata");

                            //continue with original massaction processing
                            customerconnect_rph_massactionJsObject.apply();
                        }
                    }, {
                        text: $.mage.__('Cancel'),
                        class: 'action-secondary action-dismiss',
                        click: function (event) {
                            this.closeModal(event);
                        }
                    }
                    ]
                });
            }else{
                //apply massaction data to massaction form
                addMassActionRows(massActionData);
                //continue with original massaction processing
                customerconnect_rph_massactionJsObject.apply();
            }
        })
    });
    return function () {
        // if checkout cart is displayed ensure that the minicart is up to date (doesn't always update if reorder is from My Orders)
        if (window.location.pathname == '/checkout/cart/') {
            var sections = ['cart'];
            customerData.invalidate(sections);
            customerData.reload(sections, true);
        }
    }
})
var urldata = {};
function recentPurchasesCheck() {
    if (window.location.pathname.indexOf('/customerconnect/recentpurchases/') > -1) {

        //change massaction to accommodate the reorder popup
        jQuery('#customerconnect_rph_massaction-form button').removeAttr('onclick', null).unbind();

        jQuery('[class ^="col-total_qty_ordered"]').on('mouseleave', function (e) {
            saveOrderQty(jQuery(this).parents('tr'));
        })
        //this added in case client doesn't use the mouse, but moves cursor by tabbing
        jQuery('[class ^="col-total_qty_ordered"]').on('focusout', function (e) {
            saveOrderQty(jQuery(this).parents('tr'));
        })
        jQuery('#customerconnect_rph_table input[type=checkbox]').on('change', function (e) {
            var row = jQuery(this).parents('tr');
            var productCode = row.find('.col-product_code').text().trim();
            var lastorder = row.find('.col-last_order_number').text().trim();
            var uom = row.find('.col-unit_of_measure_code').text().trim();
            var nameString = 'massactionXYYX' + productCode + 'XYYX' + lastorder + 'XYYX' + uom;

            var qty = getTotalQty(row);
            var value = {
                'product_code': productCode,
                'last_order_number': lastorder,
                'unit_of_measure': uom,
                'total_qty_ordered': qty
            };
            var massActionData = getSavedMassActionData();

            if (jQuery(this).prop("checked") == true) {
                massActionData[nameString] = value;
            } else {
                delete massActionData[nameString];
            }
            //save new massactiondata
            localStorage.setItem("massactiondata", JSON.stringify(massActionData));
        })
    }else{
        //remove massactiondata from local storage if not on recentpurchases page
        localStorage.removeItem("massactiondata");
    }
}

function saveOrderQty(row) {
    var productCode = row.find('.col-product_code').text().trim();
    var lastorder = row.find('.col-last_order_number').text().trim();
    var uom = row.find('.col-unit_of_measure_code').text().trim();
    var nameString = productCode + lastorder + uom;
    var qty = getTotalQty(row);

    urldata[nameString] = qty;

    // check if massselect was selected before value changed
    var massActionData = getSavedMassActionData();
    var massActionName = 'massactionXYYX' + productCode + 'XYYX' + lastorder + 'XYYX' + uom;
    if (massActionData[massActionName]) {
        var value = {
            'product_code': productCode,
            'last_order_number': lastorder,
            'unit_of_measure': uom,
            'total_qty_ordered': qty
        };
        massActionData[massActionName] = value;
        //save new massactiondata
        localStorage.setItem("massactiondata", JSON.stringify(massActionData));
    }

    return;
}

function addMassActionRows(massActionData) {

    jQuery.each(massActionData, function (index, value) {
        jQuery('#customerconnect_rph_massaction-form').append('<input type="hidden" name="' + index + '" id="' + index + '" value="' + btoa(JSON.stringify(value)) + '" >');
    });
}

function getSavedMassActionData() {
    //get saved massaction values
    var massActionData = JSON.parse(localStorage.getItem("massactiondata"));
    //if nothing saved, create fresh
    if (!massActionData) {
        massActionData = {};
    }
    return massActionData;
}

function getTotalQty(row){
    // row value location changes depending on access right. this covers all options
    var qty = row.find('.col-total_qty_ordered').val();
    if(!qty){
        qty = row.find('.col-total_qty_ordered').text().trim();
    }
    if(!qty){
        qty = row.find('[class *="total_qty_ordered_"]').val();
    }
    if(!qty){
        qty = row.find('[class *="total_qty_ordered_"]').text().trim();
    }
    return qty;
}

