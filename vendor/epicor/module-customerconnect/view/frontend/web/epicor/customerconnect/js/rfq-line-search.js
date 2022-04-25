/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


require([
    'jquery',
    'prototype',
    'mage/translate',
    'mage/validation'

], function (jQuery) {
    jQuery(document).ready(function(){   

        window.parent.$('loading-mask').hide();
        window.parent.jQuery('body').loader('hide');

        pageHeight = $$('.quickorderpad').shift().getHeight();

        window.parent.$('linesearch-iframe').setStyle({
            height: pageHeight + 55 + 'px'
        });


        Event.live('.addall_qty', 'keyup', function (el, e) {
//  Commented for WSO-6132 formatNumber(el, false, false);
        });

        $$('#qop-list a').invoke('observe', 'click', function (event) {
            alert(jQuery.mage.__('That function is not available'));
            event.stop();
        });

        $$('.pro-line-search').invoke('observe', 'click', function (event) {
            window.parent.jQuery('body').loader('show');
        });

        $$('.btn-qop').invoke('observe', 'click', function (event) {

            prodArr = [];
            qtyArr = [];
            var message = '';

            if (this.readAttribute('id') == 'add_all_to_basket') {
                var valid = checkDecimal(jQuery('.addall_qty'));
                if (!valid) {
                    event.stop();
                    return false;
                }
                $$('.addall_qty').each(function (ele) {
                    if (ele.value > 0) {
                        var product = ele.readAttribute('id').replace('qty_', '');
                        name = ele.readAttribute('name');
                        if (name != 'qty') {
                            product = name.replace('super_group[', '').replace(']', '');
                        }

                        prodArr[prodArr.length] = product;
                        qtyArr[qtyArr.length] = ele.value;
                        ele.value = 0;
                    }
                });

                message = jQuery.mage.__('Lines added successfully');
            } else {
                var searchForm = jQuery(this).parent();
                var valid = searchForm.validation() && searchForm.validation('isValid');
                if (!valid) {
                    event.stop();
                    return false;
                }
                ele = this.up().select('.addall_qty').shift();

                if (ele.value > 0) {
                    var product = ele.readAttribute('id').replace('qty_', '');
                    name = ele.readAttribute('name');
                    if (name != 'qty') {
                        product = name.replace('super_group[', '').replace(']', '');
                    }

                    prodArr[prodArr.length] = product;
                    qtyArr[qtyArr.length] = ele.value;

                    ele.value = 0;
                } else {
                    alert(jQuery.mage.__('Please enter a qty'));
                }

                message = jQuery.mage.__('Line added successfully');
            }

            if (prodArr.length > 0) {

                var url = window.parent.$('la_msq_link').value;
                var formKey = jQuery.cookie("form_key");
                var postData = {'form_key':formKey,'id[]': prodArr, 'qty[]': qtyArr, 'currency_code': window.parent.$('quote_currency_code').value}

                   this.ajaxRequest = new Ajax.Request(url, {
                    method: 'post',
                    parameters: postData,
                    onComplete: function (request) {
                        this.ajaxRequest = false;
                    }.bind(this),
                    onSuccess: function (data) {
                        var msqData = data.responseText.evalJSON();
                        var showConfiguratorMessage = false;

                        for (index = 0; index < prodArr.length; index++) {
                            id = prodArr[index];
                            qty = qtyArr[index];
                            pData = msqData[id];

                            separator = window.parent.$('la_separator').value;

                            if (pData.sku.search(window.parent.escapeRegExp(separator)) != -1) {
                                pData.sku = pData.sku.replace(separator + pData.uom, '');
                            }
                            if (pData.configurator == 1 || pData.type_id == 'configurable' || (pData.type_id == 'grouped' && !pData.stk_type || pData.has_options)) {
                                showConfiguratorMessage = true;
                            }
                            window.parent.lines.addLineRow(false, pData.sku, qty, pData);
                        }

                        window.parent.lines.recalcLineTotals();

                        window.parent.$('line-search').hide();
                        window.parent.$('loading-mask').hide();

                        if (showConfiguratorMessage)
                        {
                            message += '\n\n' + jQuery.mage.__('One or more products require configuration. Please click on each "Configure" link in the lines list');
                            alert(message);
                        }
                    }.bind(this),
                    onFailure: function (request) {
                        window.parent.$('loading-mask').hide();
                        alert(jQuery.mage.__('Error occured in Ajax Call'));
                    }.bind(this),
                    onException: function (request, e) {
                        window.parent.$('loading-mask').hide();
                        console.log(e);
                        alert(e);
                    }.bind(this)
                });
            }

            event.stop();
        });

    });

});


function formatNumber(el, allowNegatives, allowFloats) {
    var value = el.value, firstChar, nextFirst;
    if (value.length == 0)
        return;

    firstChar = value.charAt(0);
    if (allowFloats) {
        value = value.replace(/[^0-9\.]/g, '');
        nextFirst = value.charAt(0);
    } else {
        value = parseInt(value);
        nextFirst = '';
    }

    if (nextFirst == '.') {
        value = '0' + value;
    }

    if (allowNegatives && firstChar == '-') {
        value = firstChar + value;
    }

    el.value = value;
}
