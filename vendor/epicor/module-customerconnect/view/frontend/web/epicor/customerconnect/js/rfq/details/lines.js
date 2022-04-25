/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

if (typeof Epicor_Lines == 'undefined') {
    var Epicor_Lines = {};
}

require([
    'jquery',
    'searchForm',
    'Magento_Catalog/js/price-utils',
    'mage/validation',
    'prototype',
    'mage/translate',
    "mage/calendar"
], function (jQuery, searchForm, priceUtils, validation) {
    Epicor_Lines.lines = Class.create();
    Epicor_Lines.lines.prototype = {
        rowProcessors: [],
        cloneProcessors: [],
        pricePrecision: window.checkout.priceFormat.requiredPrecision,
        initialize: function () {
            if(typeof(window.common) == 'undefined'){
                common = new Epicor_Common.common();
                window.common = common;
            }
        },

        /***********************************************************
         * Quick Line Add
         */

        addLineAddRow: function (el) {
            var newRow = el.up('.la_row').clone(true);
            //var lineadd_count;
            var laSearchForm = [];
            lineadd_count += 1;

            newRow.writeAttribute('id', 'la_row_' + lineadd_count);
            newRow.down('.lineadd-autocomplete').writeAttribute('id', 'lineadd_autocomplete_' + lineadd_count).checked = false;
            if (newRow.down('.la_custompart')) {
                newRow.down('.la_custompart').writeAttribute('id', 'la_custompart_' + lineadd_count).checked = false;
            }
            newRow.down('.la_sku').writeAttribute('id', 'la_sku_' + lineadd_count).value = '';
            newRow.down('.la_product_id').writeAttribute('id', 'la_product_id_' + lineadd_count).value = '';
            newRow.down('.la_uom').writeAttribute('id', 'la_uom_' + lineadd_count).value = '';
            newRow.down('.la_sku_box').show();
            newRow.down('.la_name_box').hide();
            newRow.down('.la_name').writeAttribute('id', 'la_name_' + lineadd_count).value = '';
            newRow.down('.la_quantity').writeAttribute('id', 'la_quantity_' + lineadd_count).value = '';
            newRow.down('.la_packsize').writeAttribute('id', 'la_pack_' + lineadd_count).hide();
            if (newRow.down('.validation-advice') != undefined) {
                newRow.down('.validation-advice').remove();
            }
            $('la_rows').insert({bottom: newRow});
            newRow.down('.la_sku').focus();

            tempForm = new Epicor.searchForm('la_row_' + lineadd_count, 'la_sku_' + lineadd_count, '', '', 'la_uom_' + lineadd_count, 'la_pack_' + lineadd_count, 'la_product_id_' + lineadd_count, '', 'la_quantity_' + lineadd_count);
            tempForm = tempForm.initAutocomplete($('la_submit_url').value, 'lineadd_autocomplete_' + lineadd_count);

            laSearchForm[laSearchForm.length] = tempForm;

            $$('.la_delete').each(function (e) {
                e.tabIndex = -1;
                e.show();
            });
        },

        processLinesAdd: function (event) {

            hideButtons();

            var skudata = [];
            var customdata = [];
            var showConfiguratorMessage = false;
            var errors = [];

            $$('.la_row').each(function (el) {
                if (el.visible()) {
                    var custom = false;
                    var customtick = el.select('.la_custompart').shift();
                    if (customtick !== undefined) {
                        custom = customtick.checked;
                    }
                    var sku = el.select('.la_sku').shift().value;
                    var productid = el.select('.la_product_id').shift().value;
                    var name = el.select('.la_name').shift().value;
                    var uom = el.select('.la_uom').shift().value;
                    var qty = el.select('.la_quantity').shift().value;

                    if (lines.isLineEmpty(el)) {
                        return;
                    }

                    var lineErrors = lines.isLineValid(el);
                    if (lineErrors.length != 0) {
                        errors = lineErrors.concat(errors);
                        return;
                    }

                    if (custom == false) {
                        var sendSku = sku;
                        if (uom != '') {
                            sendSku = sku + $('la_separator').value + uom;
                        }

                        skudata[skudata.length] = {'sku': sku, 'sendSku': sendSku, 'uom': uom, 'qty': qty, 'productid': productid}
                    } else {
                        customdata[customdata.length] = {'sku': name, 'uom': uom, 'qty': qty}
                    }
                }
            });


            if (errors.length != 0) {
                lines.displayLineAddInputErrors(errors)
                event.stop();
            }

            if (skudata.length == 0) {
                lines.resetLineAdd();
                if (customdata.length > 0) {
                    for (index = 0; index < customdata.length; index++) {
                        lines.addLineRow(true, customdata[index].sku, customdata[index].qty, {});
                    }
                    lines.recalcLineTotals();
                }
                event.stop();
                return;
            }

            var url = $('la_msq_link').value;

            skuArr = [];
            qtyArr = [];
            idArr = [];

            for (index = 0; index < skudata.length; index++) {
                skuArr[skuArr.length] = skudata[index].sendSku;
                qtyArr[qtyArr.length] = skudata[index].qty;
                idArr[idArr.length] = skudata[index].productid;
            }

            var postData = {'from': 'rfq', 'sku[]': skuArr, 'qty[]': qtyArr, 'id[]': idArr, 'currency_code': $('quote_currency_code').value, 'use_index': 'row_id'}

            //  $('loading-mask').show();
            jQuery('body').loader('show');
            common.performAjax(url, 'post', postData, function (data) {
                var msqData = data.responseText.evalJSON();

                if (msqData['has_errors']) {
                    message = jQuery.mage.__('One or more lines had errors:') + '\n\n';
                    for (index = 0; index < skudata.length; index++) {
                        sku = skudata[index].sendSku;
                        qty = skudata[index].qty;
                        pData = msqData[index];//msqData[sku];

                        pData.sku = lines.getNiceSku(pData, skudata[index].sendSku);
                        if (pData.error == 1) {
                            message += jQuery.mage.__('SKU') + ' ' + pData.sku + ' ';
                            if ($$('.la_custompart').length > 0) {
                                message += jQuery.mage.__('Does not exist - Select Custom Part') + '\n';
                            } else {
                                message += jQuery.mage.__('Does not exist') + '\n';
                            }
                        }
                        if (pData.status_error == 1) {
                            message += jQuery.mage.__('SKU') + ' ' + pData.sku + ' ' + jQuery.mage.__('Not currently available');
                        }

                        jQuery('body').loader('hide');
                        if (jQuery('.loading-mask')) {
                            jQuery('.loading-mask').hide();
                        }
                    }
                    alert(message);
                    return;
                }

                for (index = 0; index < skudata.length; index++) {
                    sku = skudata[index].sendSku;
                    pData = msqData[index];//msqData[sku];
                    pData.sku = lines.getNiceSku(pData, skudata[index].sendSku);

                    lines.addLineRow(false, pData.sku, skudata[index].qty, pData);
                    if (lines.isProductConfigurable(pData)) {
                        showConfiguratorMessage = true;
                    }
                }

                if (customdata.length > 0) {
                    for (indexc = 0; indexc < customdata.length; indexc++) {
                        lines.addLineRow(true, customdata[indexc].sku, customdata[indexc].qty, {});
                    }
                }

                lines.resetLineAdd();
                lines.recalcLineTotals();

                message = jQuery.mage.__('Lines added successfully');

                if (showConfiguratorMessage)
                {
                    message += '\n\n' + jQuery.mage.__('One or more products require configuration. Please click on each "Configure" link in the lines list');
                    //alert(message);
                }


                jQuery('body').loader('hide');
                //   $('loading-mask').hide();
                rfqHasChanged();
            });

            event.stop();
        },

        validateQty: function (el) {
            $(el).select('.validation-advice').each(function (errorOccurance) {
                errorOccurance.remove();
            });
            var error = 0;
            $(el).select('.la_quantity').each(function (qtyOccurance) {
                value = qtyOccurance.value.trim();
                dataValidate = qtyOccurance.getAttribute('data-validate');
                if (dataValidate == null) {
                    return true;
                }
                objValidate =  jQuery.parseJSON(dataValidate);
                decimalPlaces = objValidate.validatedecimalplace;
                msg = "Decimal Places not Permitted";
                if (decimalPlaces > 0) {
                    zero = '';
                    for (j = 0; j < decimalPlaces; j++) {
                        zero = zero + 'x';
                    }
                    msg = "Qty must be in the form of xxx." + zero;
                }
                if (decimalPlaces !== '') {
                    if (value != '') {
                        var numNum = +value;
                        if (!isNaN(numNum)) {
                            if (value > 0) {
                                var isdecimal = (value.match(/\./g) || []).length;
                                var decimal = 0;
                                if (isdecimal > 0) {
                                    decimal = parseInt(value.toString().split(".")[1].length || 0);
                                }
                                if ((decimalPlaces == 0 && isdecimal > 0) || (decimalPlaces > 0 && isdecimal > 0 && decimal == 0) || (decimalPlaces > 0 && decimal > 0 && decimal > decimalPlaces) || (decimalPlaces == 0 && decimal > 0)) {
                                    qtyOccurance.insert({after: new Element('div').addClassName('validation-advice').update(msg)});
                                    error = error + 1;

                                }
                            }
                        } else {
                            qtyOccurance.insert({after: new Element('div').addClassName('validation-advice').update("Enter a Valid Qty")});
                            error = error + 1;
                        }
                    }
                }
            });

            if (error == 0)
            {
                return true;
            } else
            {
                return false;
            }
        },

        isLineEmpty: function (line) {
            var custom = false;
            var customtick = line.select('.la_custompart').shift();
            if (customtick !== undefined) {
                custom = customtick.checked;
            }
            var sku = line.select('.la_sku').shift().value;
            var name = line.select('.la_name').shift().value;
            var qty = line.select('.la_quantity').shift().value;

            var lineEmpty = false;

            var qtyEmpty = (qty == '' || !(!isNaN(parseNumber(qty)) && /^\s*-?\d*(\.\d*)?\s*$/.test(qty)))

            if (custom && name == '' && qtyEmpty) {
                lineEmpty = true;
            } else if (!custom && sku == '' && qtyEmpty) {
                lineEmpty = true;
            }

            return lineEmpty;
        },

        isLineValid: function (line) {
            var custom = false;
            var customtick = line.select('.la_custompart').shift();
            if (customtick !== undefined) {
                custom = customtick.checked;
            }
            var sku = line.select('.la_sku').shift().value;
            var name = line.select('.la_name').shift().value;
            var qty = line.select('.la_quantity').shift().value;

            var lineErrors = [];

            var skuValid = (sku != '');
            var nameValid = (name != '');
            var qtyValid = !(qty == '' || !(!isNaN(parseNumber(qty)) && /^\s*-?\d*(\.\d*)?\s*$/.test(qty)))

            if (!custom && !skuValid) {
                lineErrors['sku'] = 1;
            }

            if (custom && !nameValid) {
                lineErrors['name'] = 1;
            }

            if (!qtyValid) {
                lineErrors['qty'] = 1;
            }

            return lineErrors;
        },

        displayLineAddInputErrors: function (errors) {
            errorMessage = '';

            if (errors['sku'] !== undefined) {
                errorMessage += jQuery.mage.__('You must provide an SKU for all non-custom parts') + '\n';
            }

            if (errors['name'] !== undefined) {
                errorMessage += jQuery.mage.__('You must provide a name for all custom parts') + '\n';
            }

            if (errors['qty'] !== undefined) {
                errorMessage += jQuery.mage.__('All quantities must be valid') + '\n';
            }

            alert(errorMessage);
        },

        resetLineAdd: function () {
            //remove all lines other than the first and reset the values
            $$('#la_rows .la_row').each(function (e, index) {
                if (index == 0) {
                    if (e.down('.la_custompart')) {
                        e.down('.la_custompart').checked = false;
                    }
                    e.down('.la_sku').value = '';
                    e.down('.la_uom').value = '';
                    e.down('.la_product_id').value = '';
                    e.down('.la_sku_box').show();
                    e.down('.la_name_box').hide();
                    e.down('.la_name').value = '';
                    e.down('.la_quantity').value = '';
                    e.down('.packsize').update('');
                    e.down('.la_packsize').hide();
                } else {
                    e.remove();
                }
            });

            $('line-add').hide();
        },

        /***********************************************************
         * Line Addition
         */

        addLineRow: function (custom, sku, qty, product, sendMsq) {
            $$('#rfq_lines_table tbody tr:not(.lines_row)').each(function (e) {
                if (typeof e.up('.lines_row') === 'undefined') {
                    e.remove();
                }
            });

            var row = $('lines_row_template').clone(true);

            var type = custom ? 'N' : 'S';
            var is_kit = 'N';
            var configure_html = '';
            var requires_configuration = false;
            var show_configuration = false;
            var updateRequired = true;
            lines.setupLineInputs(row);

            product.is_custom = custom;

            row.down('.description_display').update(product.name);
            row.down('.lines_product_code').writeAttribute('value', sku);
            if (product.decimal_place !== "" && product.decimal_place != undefined) {
                var decimalPlace = '{"validatedecimalplace":' + product.decimal_place + '}';
                row.down('.lines_quantity').writeAttribute('data-validate', decimalPlace);
            }
            row.down('.lines_quantity').writeAttribute('value', qty);
            row.down('.lines_type').writeAttribute('value', type);
            row.down('.lines_product_json').writeAttribute('value', JSON.stringify(product));
            row.down('.lines_product_id').writeAttribute('value', product.entity_id);
            row.down('.lines_product_type').writeAttribute('value', product.ecc_product_type);

            if (custom) {
                row.down('.lines_description').addClassName('required-entry');
                if (!valueEmpty(product.custom_description)) {
                    row.down('.lines_description').writeAttribute('value', product.custom_description);
                    row.down('.lines_price').writeAttribute('value', parseFloat(product.use_price).toFixed(this.pricePrecision));
                    row.down('.lines_price_display').update(product.formatted_price);
                    row.down('.lines_line_value').writeAttribute('value', parseFloat(product.use_price).toFixed(this.pricePrecision) * qty);
                    row.down('.lines_line_value_display').update(product.formatted_total);
                } else {
                    row.down('.lines_price_display').update(jQuery.mage.__('TBA'));
                    row.down('.lines_line_value_display').update(jQuery.mage.__('TBA'));
                }
            } else {
                row.down('.uom_display').update(product.uom);
                row.down('.lines_unit_of_measure_code').writeAttribute('value', product.uom);

                row.down('.lines_description').writeAttribute('value', product.name);
                row.down('.lines_description').writeAttribute('type', 'hidden');

                if (product.ewa_attributes && product.ewa_attributes != undefined && product.ewa_attributes != 'value') {
                    row.down('.lines_attributes').writeAttribute('value', product.ewa_attributes);
                }
                if (window.checkout.dealerPortal === 0){
                    row.down('.lines_price').writeAttribute('value', parseFloat(product.use_price).toFixed(this.pricePrecision));
                    row.down('.lines_price_display').update(product.formatted_price);
                }

                row.down('.lines_line_value').writeAttribute('value', parseFloat(product.use_price).toFixed(this.pricePrecision) * qty);
                row.down('.lines_line_value_display').update(product.formatted_total);

                row.down('.lines_ewa_code').writeAttribute('value', '');
                if (product.configurator == 1) {
                    configureFunction = 'lines.configureEwaProduct';
                    show_configuration = true;
                    if (valueEmpty(product.ewa_code)) {
                        requires_configuration = true;
                    } else {
                        lines.updateLineEwaInfo(row, product, true);
                        updateRequired = false;
                    }
                } else if (product.type_id == 'configurable' || !valueEmpty(product.has_options) || product.type_id == 'grouped') {
                    configureFunction = 'lines.configureProduct';
                    show_configuration = true;
                    requires_configuration = true;
                }

                if(product.ewa_visible_description) {
                    row.down('.description_display').update(product.ewa_visible_description);
                }

                if (show_configuration) {
                    if (requires_configuration) {
                        row.down('.lines_configured').writeAttribute('value', 'TBC');
                        row.down('.lines_price').writeAttribute('value', '');
                        row.down('.lines_price_display').update(jQuery.mage.__('TBA'));
                        row.down('.lines_line_value').writeAttribute('value', '');
                        row.down('.lines_line_value_display').update(jQuery.mage.__('TBA'));
                        var configure_html = '<br /><a href="javascript:' + configureFunction + '(\'' + line_count + '\')">' + jQuery.mage.__('Configure') + '</a>';
                    } else {
                        var configure_html = '<br /><a href="javascript:' + configureFunction + '(\'' + line_count + '\')">' + jQuery.mage.__('Edit Configuration') + '</a>';
                    }
                }

                if (product.stk_type == 'E') {
                    is_kit = 'Y';
                }
            }


            if (!valueEmpty(product.lines_orig_quantity)) {
                row.down('.lines_orig_quantity').writeAttribute('value', product.lines_orig_quantity);
            }

            if (!valueEmpty(product.request_date)) {
                row.down('.lines_request_date').writeAttribute('value', product.request_date);
            }

            if (typeof(row.down('.ecc_return_type_field')) != 'undefined' && typeof(row.down('.ecc_return_type_select')) != 'undefined') {
                if (!valueEmpty(product.ecc_return_type)) {
                    row.down('.lines_line_ecc_return_type_field').writeAttribute('value', product.ecc_return_type);
                    row.down('.lines_line_ecc_return_type_display').update(product.ecc_return_type_display);
                    row.down('.ecc_return_type_select').remove();
                } else {
                    row.down('.ecc_return_type_field').remove();
                }
            }
            row.down('.lines_is_kit').writeAttribute('value', is_kit);
            row.down('.is_kit_display').update(is_kit);

            if (updateRequired === true) {
                row.down('.product_code_display').update(sku + configure_html);
            }

            if (sendMsq === true) {
                lines.sendMsqForLine(row);
            } else {
                lines.processRowExtra(line_count, row, product, requires_configuration);
            }

            $('rfq_lines').down('tbody').insert({bottom: row});

            // ATTACHMENTS....

            var row = $('line_attachments_row_template').clone(true);

            row.addClassName('new');
            row.setAttribute('id', 'row-attachments-' + line_count);
            row.down('.rfq_line_attachment_add').writeAttribute('id', 'add_line_attachment_' + line_count);
            row.down('#rfq_line_attachments_').writeAttribute('id', 'rfq_line_attachments_' + line_count);
            row.down('#rfq_line_attachments__table').writeAttribute('id', 'rfq_line_attachments_' + line_count + '_table');
            row.down('#line_attachment_row_template_').writeAttribute('id', 'line_attachment_row_template_' + line_count);
            $('rfq_lines').down('tbody').insert({bottom: row});

            /*Calendar.setup({
             inputField: 'line_' + line_count + '_request_date',
             ifFormat: $('date_input_format').value,
             button: 'date_from_trig',
             align: 'Bl',
             singleClick: true
             });*/
            var dateFormats="";
            if(jQuery("#date_input_format").length) {
                var dateFormats = jQuery("#date_input_format").val();
            }
            jQuery('#line_' + line_count + '_request_date').calendar({dateFormat:dateFormats,showOn:"both"});
            common.colorRows('rfq_lines_table', ':not(.attachment)');
            line_count += 1;
            rfqHasChanged();
        },

        addLinesByJson: function (jsonData) {
            ewaProduct.closepopup("ewaWrapper");
            $('loading-mask').show();

            jsonData = jsonData.evalJSON();

            errors = false;

            if (jsonData.errors) {
                errors = true;
            }

            linesAdded = false;
            showConfiguratorMessage = false;

            if (jsonData.products) {
                linesAdded = true;
                for (index = 0; index < jsonData.products.length; index++) {
                    product = jsonData.products[index];

                    if (product.sku.search(escapeRegExp($('la_separator').value)) != -1) {
                        product.sku = product.sku.replace($('la_separator').value + product.uom, '');
                    }

                    lines.addLineRow(false, product.sku, product.qty, product);

                    if (product.configurator == 1 || product.type_id == 'configurable' || (product.type_id == 'grouped' && !product.stk_type)) {
                        showConfiguratorMessage = true;
                    }
                }
            }

            if (!linesAdded) {
                message = jQuery.mage.__('No lines added');
            } else {
                message = jQuery.mage.__('Line(s) added successfully');

                if (showConfiguratorMessage)
                {
                    message += '\n\n' + jQuery.mage.__('One or more products require configuration. Please click on each "Configure" link in the lines list');
                }
                rfqHasChanged();
            }

            lines.recalcLineTotals();

            alert(message);

            if($('linesearch-iframe').readAttribute('dealerpage') == 1){
                $('linesearch-iframe').writeAttribute('src', '/dealerconnect/quotes/linesearch/');
            }else {
                $('linesearch-iframe').writeAttribute('src', '/customerconnect/rfqs/linesearch/');
            }

        },

        setupLineInputs: function (row) {

            row.addClassName('new');
            row.setAttribute('id', 'lines_' + line_count);
            common.resetInputs(row);

            row.down('.plus-minus').writeAttribute('id', 'attachments-' + line_count);
            row.down('.lines_orig_quantity').writeAttribute('name', 'lines[new][' + line_count + '][lines_orig_quantity]');
            row.down('.lines_delete').writeAttribute('name', 'lines[new][' + line_count + '][delete]');
            row.down('.lines_product_code').writeAttribute('name', 'lines[new][' + line_count + '][product_code]');
            row.down('.lines_description').writeAttribute('name', 'lines[new][' + line_count + '][description]');
            row.down('.lines_attributes').writeAttribute('name', 'lines[new][' + line_count + '][attributes]');
            row.down('.lines_group_sequence').writeAttribute('name', 'lines[new][' + line_count + '][group_sequence]');
            row.down('.lines_group_sequence').writeAttribute('id', 'group_sequence_' + line_count);
            row.down('.lines_configured').writeAttribute('name', 'lines[new][' + line_count + '][configured]');
            row.down('.lines_ewa_code').writeAttribute('name', 'lines[new][' + line_count + '][ewa_code]');
            row.down('.lines_ewa_code').writeAttribute('id', 'ewa_code_' + line_count);
            row.down('.lines_quantity').writeAttribute('name', 'lines[new][' + line_count + '][quantity]');
            row.down('.lines_type').writeAttribute('name', 'lines[new][' + line_count + '][type]');
            row.down('.lines_is_kit').writeAttribute('name', 'lines[new][' + line_count + '][is_kit]');
            row.down('.lines_price').writeAttribute('name', 'lines[new][' + line_count + '][price]');
            row.down('.lines_additional_text').writeAttribute('name', 'lines[new][' + line_count + '][additional_text]');
            row.down('.lines_miscellaneous_charges_total').writeAttribute('name', 'lines[new][' + line_count + '][miscellaneous_charges_total]');
            row.down('.lines_line_value').writeAttribute('name', 'lines[new][' + line_count + '][line_value]');
            row.down('.lines_unit_of_measure_code').writeAttribute('name', 'lines[new][' + line_count + '][unit_of_measure_code]');
            row.down('.lines_request_date').writeAttribute('name', 'lines[new][' + line_count + '][request_date]');
            row.down('.lines_request_date').writeAttribute('id', 'line_' + line_count + '_request_date');
            if (typeof(row.down('.lines_line_ecc_return_type_field')) != 'undefined') {
                row.down('.lines_line_ecc_return_type_field').writeAttribute('name', 'lines[new][' + line_count + '][ecc_return_type]');
            }
            if (typeof(row.down('.lines_line_ecc_return_type_select')) != 'undefined') {
                row.down('.lines_line_ecc_return_type_select').writeAttribute('name', 'lines[new][' + line_count + '][ecc_return_type]');
            }

            row.down('.lines_product_json').writeAttribute('name', 'lines[new][' + line_count + '][lines_product_json]');
            row.down('.lines_orig_quantity').value = 0;
        },

        /*************************************
         * Line Editing
         */

        sendMsqForLine: function (line) {
            jQuery('body').loader('show');
            var pSku = line.down('.lines_product_code').value;

            if (line.down('.lines_unit_of_measure_code').value && valueEmpty(line.down('.lines_ewa_code').value)) {
                pSku = pSku + $('la_separator').value + line.down('.lines_unit_of_measure_code').value;
            }

            var skuData = pSku;
            var qty = parseFloat(line.down('.lines_quantity').value);
            var att = line.down('.lines_attributes').value;
            var ewa = '';

            if (!valueEmpty(line.down('.lines_ewa_code').value)) {
                ewa = line.down('.lines_ewa_code').value;
            }

            var postData = {'from': 'rfq', 'sku[]': [skuData], 'qty[]': [qty], 'att[]': [att], 'ewa[]': [ewa], 'currency_code': $('quote_currency_code').value}
            var url = $('la_msq_link').value;

            common.performAjax(url, 'post', postData, function (data) {
                var msqData = data.responseText.evalJSON();
                var lineMsqData = msqData[skuData];
                if( msqData['has_errors'] && lineMsqData['status_error']){
                    message = jQuery.mage.__('One or more lines had errors:') + '\n\n';
                    message += jQuery.mage.__('SKU') + ' ' + lineMsqData['sku'] + ' ' + jQuery.mage.__('Not currently available');
                    jQuery('body').loader('hide');
                    alert(message);
                    line.down('.lines_quantity').value = line.down('.lines_quantity').readAttribute('value');
                    return;
                }

                if (!msqData[pSku]) {
                    $('loading-mask').hide();
                    return;
                }
                pData = msqData[pSku];
                lines.updateLinePrice(line, pData);
                line = lines.processRowExtra(line.readAttribute('id').replace('lines_', ''), line, pData, false);
                lines.recalcLineTotals();
                jQuery('body').loader('hide');
            });
        },

        /*****************************************
         * EWA functions
         */

        configureEwaProduct: function (line_id) {
            var returnurl = $('rfq_ewa_returnurl').value;

            var row = $('lines_' + line_id);
            var webReference = $('web_reference').value;
            var webReferencePrefix = $('web_reference_prefix').value;
            var data = {
                productId: row.down('.lines_product_id').value,
                groupSequence: row.down('.lines_group_sequence').value,
                ewaCode: row.down('.lines_ewa_code').value,
                sku: row.down('.lines_product_code').value,
                qty: row.down('.lines_quantity').value,
                type: row.down('.lines_product_type').value,
                quoteId: webReference.replace(webReferencePrefix,''),
                lineNumber: lines.getLineNumberById(line_id)
            };

            configurator_line = line_id;
            if (valueEmpty(data.ewaCode) && valueEmpty(data.groupSequence)) {
                ewaProduct.submit(data, returnurl);
            } else {
                ewaProduct.edit(data, returnurl);
            }
        },

        updateConfiguratorProductsByJson: function (lineNo, jsonData) {
            ewaProduct.closepopup("ewaWrapper");
            $('loading-mask').show();
            jsonData = jsonData.evalJSON();
            if (jsonData.errors.length > 0) {
                $('loading-mask').hide();
                return;
            }

            if (jsonData.products) {
                for (index = 0; index < jsonData.products.length; index++) {
                    var product = jsonData.products[index];
                    break
                }

                product.sku = lines.getNiceSku(product, product.sku);
                var row = $('lines_' + configurator_line);
                var qty = row.down('.lines_quantity').value;
                lines.updateLineEwaInfo(row, product, false, jsonData.ewasortorder);
                $('loading-mask').hide();
                lines.updateLinePrice(row, product);
                row = lines.processRowExtra(configurator_line, row, product, false);
                lines.recalcLineTotals();

                if (row.down('.lines_quantity').value > 1) {
                    lines.sendMsqForLine(row);
                }
            }
        },

        updateLineEwaInfo: function (row, product, newline, ewasortorder) {
            var br = '<br />';
            var ewa_text = '';
            var ewa_html = '';

            var rowId = row.readAttribute('id').replace('lines_', '');

            if (newline !== true || !valueEmpty(product.ewa_code)) {
                ewa_text = jQuery.mage.__('Edit Configuration');
            } else {
                ewa_text = jQuery.mage.__('Configure');
            }

            var ewa_html = br + '<a href="javascript:lines.configureEwaProduct(\'' + rowId + '\')">' + ewa_text + '</a>';
            row.down('.product_code_display').update(product.ewa_sku + ewa_html);

            var ewa_desc = [];
            if(ewasortorder){
                // loop through ewa array to display values in correct order 
                var ewaArray = {base_description: 'rfq_ewa_show_basedesc', ewa_description: 'rfq_ewa_show_desc', ewa_short_description: 'rfq_ewa_show_shortdesc', ewa_title: 'rfq_ewa_show_title', ewa_sku: 'rfq_ewa_show_sku'};
                var base64 = {base_description: 'no', ewa_description: 'yes', ewa_short_description: 'yes', ewa_title: 'yes', ewa_sku: 'no'};
                product.base_description = product.name;
                for(var index in ewasortorder) {
                    if ($(ewaArray[ewasortorder[index]]).value == 'Y') {
                        if(base64[index] == 'yes'){
                            //unscramble if required
                            ewa_desc.push(atob(product[index]));
                        }else{
                            ewa_desc.push(product[index]);
                        }
                    }
                }
            }


            row.down('.description_display').update(ewa_desc.join(br + br));
            row.down('.lines_ewa_title').value = product.ewa_title;
            row.down('.lines_ewa_sku').value = product.ewa_sku;
            row.down('.lines_ewa_short_description').value = product.ewa_short_description;
            row.down('.lines_ewa_description').value = product.ewa_description;
            row.down('.lines_ewa_code').value = product.ewa_code;
            row.down('.lines_attributes').value = product.ewa_attributes;
            /* E10 has an issue and always returns qty as 1 -- Keep this line until the issue is fixed */
            if (product.qty > 1) {
                row.down('.lines_quantity').value = product.qty;
            }
            row.down('.lines_configured').value = '';

            common.formatNumber(row.down('.lines_quantity'));
        },

        updateLinePrice: function (row, product) {
            if (!row.down('.lines_price').hasClassName('no_update')) {
                row.down('.lines_price').value = parseFloat(product.use_price).toFixed(this.pricePrecision);
                row.down('.lines_line_value').value = (parseFloat(product.use_price).toFixed(this.pricePrecision) * product.qty);
                row.down('.lines_price_display').update(product.formatted_price);
                row.down('.lines_line_value_display').update(product.formatted_total);
                rfqHasChanged();
            }
        },

        /*****************************************
         * Configurable product functions
         */

        configureProduct: function (line_id) {
            var url = $('rfq_configurable_url').value;
            var row = $('lines_' + line_id);
            var product_id = row.down('.lines_product_id').value;
            var qty = row.down('.lines_quantity').value;
            var lines_attributes = row.down('.lines_attributes').value;
            var child_id = '';

            if (row.down('.lines_child_id')) {
                child_id = row.down('.lines_child_id').value;
            }

            $('loading-mask').show();

            var form_data = {'productid': product_id, 'child': child_id, 'currency_code': $('quote_currency_code').value, 'qty': qty, 'options': lines_attributes};
            configurator_line = line_id;
            configure_id = product_id;
            //Added by Tani Ray
            this.ajaxRequest = new Ajax.Request(url, {
                method: 'post',
                parameters: form_data,
                onComplete: function (request) {
                    this.ajaxRequest = false;
                }.bind(this),
                onSuccess: function (data) {

                    var json = data.responseText.evalJSON();
                    if (!json.error || json.error == '') {
                        //optionsPrice = new Product.OptionsPrice(json.jsonconfig);
                        $('loading-mask').hide();
                        lines.showConfigureOverlay(json.html);
                    } else {
                        alert(json.error);
                        if (json.error) {
                            showMessage(json.error, 'error');
                        }
                    }
                }.bind(this),
                onFailure: function (request) {
                    alert('Error occured in Ajax Call');
                }.bind(this),
                onException: function (request, e) {
                    alert(e);
                }.bind(this)
            });
            //Addition ends here
//        common.performAjax(url, 'post', form_data, function (data) {
//            var json = data.responseText.evalJSON();
//            if (!json.error || json.error == '') {
//                optionsPrice = new Product.OptionsPrice(json.jsonconfig);
//                $('loading-mask').hide();
//                showConfigureOverlay(json.html);
//            } else {
//                alert(json.error);
//                if (json.error) {
//                    showMessage(json.error, 'error');
//                }
//            }
//
//            $('loading-mask').hide();
//        });
        },

        showConfigureOverlay: function (htmlToShow) {
            var body = document.body, html = document.documentElement;
            var width = Math.max(body.scrollWidth, body.offsetWidth, html.clientWidth, html.scrollWidth, html.offsetWidth);
            var height = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight);
            //    if(!$('window-overlay').down(id_to_show)){
            jQuery('#window-overlay-content').html(htmlToShow).trigger('contentUpdated');
            $('window-overlay').setStyle({width: width + 'px', height: height + 'px'});
            $('window-overlay').show();

            var elementHeight = $('window-overlay-content').getHeight();
            var elementWidth = $('window-overlay-content').getWidth();
            var formwidth = ((html.clientWidth - elementWidth) / 2);
            var viewport = document.viewport.getDimensions();   // Gets the viewport as an object literal
            var visibleheight = viewport.height;                       // Usable window height
            var topoffset = (visibleheight - elementHeight - 50) / 2;
            $('window-overlay-content').setStyle({'top': topoffset + 'px', 'left': formwidth + 'px'});
            $('window-overlay-content').show();
        },

        submitConfigurableProduct: function () {
            $('window-overlay').hide();
            $('loading-mask').show();
            var url = $('configure_product_form').readAttribute('action');
            var form_data = $('configure_product_form').serialize(true);

            common.performAjax(url, 'post', form_data, function (data) {
                var json = data.responseText.evalJSON();
                if (json.grouped) {
                    var row = $('lines_' + configurator_line);
                    row.remove();

                    for (index = 0; index < json.grouped.length; index++) {
                        product = json.grouped[index];
                        product.sku = lines.getNiceSku(product, product.sku);
                        lines.addLineRow(false, product.sku, product.qty, product);
                    }

                    lines.recalcLineTotals();
                    $('window-overlay-content').update('');
                } else if (json[configure_id]) {

                    var product = json[configure_id];
                    var row = $('lines_' + configurator_line);

                    row.down('.lines_product_code').writeAttribute('value', product.sku);

                    lines.updateLinePrice(row, product);

                    row.down('.uom_display').update(product.uom);
                    row.down('.lines_unit_of_measure_code').writeAttribute('value', product.uom);
                    row.down('.lines_child_id').writeAttribute('value', product.entity_id);
                    ewa_html = '<br /><a href="javascript:lines.configureProduct(\'' + configurator_line + '\')">' + jQuery.mage.__('Edit Configuration') + '</a>';
                    row.down('.description_display').update(product.name);
                    row.down('.product_code_display').update(product.sku + ewa_html);
                    row.down('.lines_configured').value = '';
                    //row.down('.lines_attributes').writeAttribute('value', product.configured_options);

                    if (product.option_values) {
                        var optdesc = '<br /><br />';
                        var options = product.option_values;
                        for (index = 0; index < options.length; index++) {
                            optdesc += '<strong>' + options[index].description + '</strong>: ' + options[index].value + '<br />';
                        }
                        row.down('.description_display').update(product.name + optdesc);
                        row.down('.lines_attributes').writeAttribute('value', product.option_values_encoded);
                    }

                    row = lines.processRowExtra(configurator_line, row, product);

                    lines.recalcLineTotals();
                    $('window-overlay-content').update('');
                    rfqHasChanged();
                } else {
                    if (json.error) {
                        alert(json.error);
                        $('window-overlay').show();
                    }
                }
                $('loading-mask').hide();
            });

        },

        /***********************************************************
         * Line Deletion
         */

        confirmLinesDelete: function (el) {
            var allowDelete = true;
            if (confirm(jQuery.mage.__('Are you sure you want to delete selected lines?')) === false) {
                allowDelete = false;
            }
            return allowDelete;
        },

        deleteLines: function () {

            $$('.lines_select:checked').each(function (e) {
                if (e.checked) {
                    var row = e.parentNode.parentNode;
                    var attachmentsRow = $('row-' + row.down('.plus-minus').readAttribute('id'));
                    if (row.hasClassName('new')) {
                        row.remove();
                        attachmentsRow.remove();
                    } else {
                        row.hide();
                        row.down('.lines_delete').value = 1;
                        row.down('.lines_select').checked = false;
                        attachmentsRow.hide();
                    }
                }
            });
        },

        /***********************************************************
         * Line Cloning
         */

        confirmLinesClone: function (el) {
            var allowClone = true;
            if (confirm(jQuery.mage.__('Are you sure you want to clone selected lines?')) === false) {
                allowClone = false;
            }
            return allowClone;
        },

        cloneLines: function () {
            $$('.lines_select:checked').each(function (e) {
                var row = e.parentNode.parentNode;
                var rowQty = row.down('.lines_quantity').value;
                var rowProduct = row.down('.lines_product_json').value.evalJSON();
                var configured = row.down('.lines_configured').value;

                rowProduct.sku = lines.getNiceSku(rowProduct, rowProduct.sku);
                rowProduct.qty = rowQty;

                var oldId = row.readAttribute('id');
                oldId = oldId.replace('lines_', '');

                rowProduct.lines_orig_quantity = rowQty;

                lines.processRowCloneExtra(row, rowProduct);

                rowProduct.lines_additional_text = row.down('.lines_additional_text').value;
                if (rowProduct.configurator == 1 && configured == '') {
                    jQuery('body').loader('show');
                    var ewa_code = row.down('.lines_ewa_code').value;
                    var gs = row.down('.lines_group_sequence').value;
                    var webReference = $('web_reference').value;
                    var webReferencePrefix = $('web_reference_prefix').value;
                    var postData = {
                        ewaCode: ewa_code,
                        groupSequence: gs,
                        productId: rowProduct.entity_id,
                        action: 'C',
                        quoteId: webReference.replace(webReferencePrefix,''),
                        lineNumber: $$('.lines_row:not(.attachment)').length
                    }
                    var url = $('rfq_ewa_cimurl').value;

                    common.performAjax(url, 'post', postData, function (data) {
                        var cimData = data.responseText.evalJSON();

                        if (!valueEmpty(cimData.error)) {
                            return;
                        }

                        rowProduct.ewa_title = row.down('.lines_ewa_title').value;
                        rowProduct.ewa_sku = row.down('.lines_ewa_sku').value;
                        rowProduct.ewa_short_description = row.down('.lines_ewa_short_description').value;
                        rowProduct.ewa_description = row.down('.lines_ewa_description').value;
                        rowProduct.ewa_visible_description = row.down('.description_display').innerHTML;

                        rowProduct.ewa_code = cimData.ewa_code;
                        rowProduct.ewa_attributes = cimData.ewa_attributes;

                        lines.addLineRow(rowProduct.is_custom, rowProduct.sku, rowQty, rowProduct, true);
                        lines.recalcLineTotals();
                        jQuery('body').loader('hide');
                        e.checked = false;
                    });
                } else {
                    if (rowProduct.is_custom == 1) {
                        rowProduct.custom_description = row.down('.lines_description').value;
                    }
                    lines.addLineRow(rowProduct.is_custom, rowProduct.sku, rowQty, rowProduct, true);
                    e.checked = false;
                }

            });
            lines.recalcLineTotals();
        },

        /***********************************************************
         * Line Attachments
         */

        addLineAttachment: function (element) {

            var rowId = element.readAttribute('id').replace('add_line_attachment_', '');
            var row = $('line_attachment_row_template_' + rowId).clone(true);

            $$('#rfq_line_attachments_' + rowId + '_table tbody tr:not(.line_attachment_row)').each(function (e) {
                e.remove();
            });

            row.addClassName('new');
            row.setAttribute('id', 'line_attachments_' + line_attachment_count);
            row = common.resetInputs(row);

            row.down('.line_attachments_delete').writeAttribute('name', 'lineattachments[new][' + rowId + '][' + line_attachment_count + '][delete]');
            row.down('.line_attachments_description').writeAttribute('name', 'lineattachments[new][' + rowId + '][' + line_attachment_count + '][description]');
            row.down('.line_attachments_filename').writeAttribute('name', 'lineattachments[new][' + rowId + '][' + line_attachment_count + '][filename]');

            $('rfq_line_attachments_' + rowId + '_table').down('tbody').insert({bottom: row});
            common.colorRows('rfq_line_attachments_' + rowId + '_table', '.line_attachment_row');
            line_attachment_count += 1;

        },

        /***********************************************************
         * UTILITY
         */

        isProductConfigurable: function (product) {
            return (product.configurator == 1 || product.type_id == 'configurable' || (product.type_id == 'grouped' && !product.stk_type) || product.has_options)
        },

        getNiceSku: function (pData, defaultValue) {
            if (pData.sku) {
                if (pData.sku.search(escapeRegExp($('la_separator').value)) != -1) {
                    pData.sku = pData.sku.replace($('la_separator').value + pData.uom, '');
                }
            } else {
                pData.sku = defaultValue;
            }

            return pData.sku;

        },

        recalcLineTotals: function (def) {
            if(typeof def === "undefined"){
                def = 'c';
            }
            var subtotal = 0;
            var dealerSubTot = 0;
            var subtotalP = 0;
            var dealerSubTotP = 0;
            var currentMode = $('dealer-price-toggle') ? $('dealer-price-toggle').readAttribute('dealer') : window.checkout.dealerPriceMode;
            var canShowPrice = window.checkout.dealerCanShowCusPrice;
            var dealerPrice = 0;
            var parent = jQuery('.totals');
            var price = 'TBA';
            var dealerTotal = 0;

            $$('#rfq_lines_table tbody tr.lines_row:not(.attachment)').each(function (row) {
                var price = '';
                if (row.down('.lines_delete').value != 1) {
                    oqty = parseFloat(row.down('.lines_orig_quantity').value);
                    qty = parseFloat(row.down('.lines_quantity').value);
                    var parentNode = ''; // row.down('.lines_price').parentNode.attributes.class.value;
                    if (typeof row.down('.lines_price') !== "undefined") {
                        parentNode = row.down('.lines_price').parentNode.attributes.class.value;
                    }
                    if (window.checkout.dealerPortal === 1) {
                        if (typeof row.down('.dealer-container') !== "undefined") {
                            var dealerParent =  row.down('.dealer-container').parentNode.attributes.class.value;
                            if (dealerParent && typeof row.down('.dealer-container').down('.lines_price') !== "undefined") {
                                dealerPrice = parseFloat(row.down('.dealer-container').down('.lines_price').value);
                            }
                            if (dealerParent.indexOf("no-display") === -1 && currentMode == "shopper" && typeof row.down('.dealer-container') !== "undefined" && typeof row.down('.dealer-container').down('.lines_price') !== "undefined") {
                                price = parseFloat(row.down('.dealer-container').down('.lines_price').value);
                            } else if (typeof row.down('.lines_base_price') !== "undefined") {
                                price = parseFloat(row.down('.lines_base_price').value);
                            }else {
                                price = 'TBA';
                            }
                        } else if (typeof row.down('.lines_price') !== "undefined") {
                            price = parseFloat(row.down('.lines_price').value);
                        }
                    } else {
                        if (parentNode.indexOf("no-display") === -1) {
                            price = parseFloat(row.down('.lines_price').value);
                        } else {
                            price = 'TBA';
                        }
                    }
                    total = parseFloat(row.down('.lines_line_value').value);

                    if (row.down('.lines_type').value == 'S') {
                        total = qty * price;
                        dealerTotal = qty * dealerPrice;
                        var totalP = total;
                        var dealerTotalP = dealerTotal;
                        if(typeof(row.down('.lines_miscellaneous_charges_total')) !== 'undefined' && row.down('.lines_miscellaneous_charges_total').value !== ''){
                            total += parseFloat(row.down('.lines_miscellaneous_charges_total').value);
                            dealerTotal += parseFloat(row.down('.lines_miscellaneous_charges_total').value);
                        }
                        row.down('.lines_line_value').value = total;
                        row.down('.lines_line_value_display').update(priceUtils.formatPrice(total, window.checkoutConfig.priceFormat));
                        row.down('.lines_orig_quantity').value = qty;
                    } else {
                        if (oqty != qty || price == '') {
                            //row.down('.lines_line_value').value = '';
                            row.down('.lines_line_value_display').update(jQuery.mage.__('TBA'));
                            subtotal = jQuery.mage.__('TBA');
                        } else {
                            row.down('.lines_line_value_display').update(priceUtils.formatPrice(total, window.checkoutConfig.priceFormat));
                        }
                    }

                    if (subtotal != jQuery.mage.__('TBA')) {
                        subtotal += total;
                        dealerSubTot += dealerTotal;
                        subtotalP += totalP;
                        dealerSubTotP += dealerTotalP;
                    }
                }
            });

            if (subtotal != jQuery.mage.__('TBA')) {
                var rawSubtotal = subtotal.toFixed(2);
                var rawSubtotalP = subtotalP.toFixed(2);
                subtotal = priceUtils.formatPrice(subtotal);
                var rawDealerSubtot = dealerSubTot.toFixed(2);
                var rawDealerSubtotP = dealerSubTotP.toFixed(2);
                dealerSubTot = priceUtils.formatPrice(dealerSubTot);
            }
            $('rfq_lines_table').down('.subtotal .price').update(subtotal);
            $('rfq_lines_table').down('.subtotal .post-price').value = rawSubtotalP;

            if (typeof $('rfq_lines_table').down('.dealer-subtotal .price') !== "undefined") {
                if (subtotal == jQuery.mage.__('TBA')) {
                    $('rfq_lines_table').down('.dealer-subtotal .price').update(subtotal);
                } else {
                    $('rfq_lines_table').down('.dealer-subtotal .price').update(dealerSubTot);
                }
                $('rfq_lines_table').down('.dealer-subtotal .post-price').value = rawDealerSubtotP;
            }

            if (def === 'c') {
                if(typeof $('rfq_lines_table').down('.dealer-misc .price') !== "undefined"){
                    $('rfq_lines_table').down('.dealer-misc .price').update('TBA');
                }
                if(typeof $('rfq_lines_table').down('.misc .price') !== "undefined"){
                    $('rfq_lines_table').down('.misc .price').update('TBA');
                }
                if(typeof $('rfq_lines_table').down('.shipping') !== 'undefined'){
                    $('rfq_lines_table').down('.shipping .price').update(jQuery.mage.__('TBA'));
                    $('rfq_lines_table').down('.shipping .post-price').value = 0;
                }

                if (typeof $('rfq_lines_table').down('.dealer-shipping .price') !== "undefined") {
                    $('rfq_lines_table').down('.dealer-shipping .price').update(jQuery.mage.__('TBA'));
                    $('rfq_lines_table').down('.dealer-shipping .post-price').value = 0;
                }
                if ($('rfq_lines_table').down('.tax .price')) {
                    $('rfq_lines_table').down('.tax .price').value = jQuery.mage.__('TBA');
                }
                $('rfq_lines_table').down('.grand_total .price').update(jQuery.mage.__('TBA'));
                $('rfq_lines_table').down('.grand_total .post-price').value = rawSubtotal;

                if (typeof $('rfq_lines_table').down('.dealer-grand_total .price') !== "undefined") {
                    $('rfq_lines_table').down('.dealer-grand_total .price').update(jQuery.mage.__('TBA'));
                    $('rfq_lines_table').down('.dealer-grand_total .post-price').value = rawDealerSubtot;
                }
            }
            if (window.checkout.dealerPortal === 1){
                lines.totalsDisplay(parent, currentMode);
            }

        },

        addRowProcessor: function (rowfunction) {

            this.rowProcessors[this.rowProcessors.length] = rowfunction;
        },

        processRowExtra: function (rowid, row, product, requires_configuration) {

            for (rowcount = 0; rowcount < this.rowProcessors.length; rowcount++) {
                extrafunction = this.rowProcessors[rowcount];
                row = extrafunction(rowid, row, product, requires_configuration);
            }

            return row;
        },

        addRowCloneProcessor: function (rowfunction) {
            this.cloneProcessors[this.cloneProcessors.length] = rowfunction;
        },

        processRowCloneExtra: function (row, product) {

            for (clonecount = 0; clonecount < this.cloneProcessors.length; clonecount++) {
                extrafunction = this.cloneProcessors[clonecount];
                row = extrafunction(row, product);
            }

            return row;
        },

        getLineNumberById: function (line_id) {
            var lineNumber = 1;
            $$('.lines_row').each(function (e) {
                var currentLineId = e.identify();
                if (currentLineId == 'lines_' + line_id) {
                    throw $break;
                } else if (currentLineId.substr(0, 6) == 'lines_') {
                    lineNumber++;
                }
            });
            return lineNumber;
        },

        totalsDisplay: function (parent, currentMode) {
            if(typeof currentMode === "undefined"){
                currentMode = 'dealer';
            }
            if (currentMode == "shopper") {

                parent.prepend($('rfq_lines_table').down('.dealer-subtotal'));
                if ($('rfq_lines_table').down('.dealer-subtotal').hasClassName('no-display')) {
                    $('rfq_lines_table').down('.dealer-subtotal').removeClassName('no-display');
                }
                if (!$('rfq_lines_table').down('.subtotal').hasClassName('no-display')) {
                    $('rfq_lines_table').down('.subtotal').addClassName('no-display');
                }
                if (typeof $('rfq_lines_table').down('.dealer-shipping') !== 'undefined' &&  $('rfq_lines_table').down('.dealer-shipping').hasClassName('no-display')) {
                    $('rfq_lines_table').down('.dealer-shipping').removeClassName('no-display');
                }
                if (typeof $('rfq_lines_table').down('.shipping') !== 'undefined' &&  !$('rfq_lines_table').down('.shipping').hasClassName('no-display')) {
                    $('rfq_lines_table').down('.shipping').addClassName('no-display');
                }
                if ($('rfq_lines_table').down('.dealer-grand_total').hasClassName('no-display')) {
                    $('rfq_lines_table').down('.dealer-grand_total').removeClassName('no-display');
                }
                if (!$('rfq_lines_table').down('.grand_total').hasClassName('no-display')) {
                    $('rfq_lines_table').down('.grand_total').addClassName('no-display');
                }

            } else {

                parent.prepend($('rfq_lines_table').down('.subtotal'));
                if ($('rfq_lines_table').down('.subtotal').hasClassName('no-display')) {
                    $('rfq_lines_table').down('.subtotal').removeClassName('no-display');
                }
                if (!$('rfq_lines_table').down('.dealer-subtotal').hasClassName('no-display')) {
                    $('rfq_lines_table').down('.dealer-subtotal').addClassName('no-display');
                }
                if ( typeof $('rfq_lines_table').down('.shipping') !== 'undefined' && $('rfq_lines_table').down('.shipping').hasClassName('no-display')) {
                    $('rfq_lines_table').down('.shipping').removeClassName('no-display');
                }
                if (typeof $('rfq_lines_table').down('.dealer-shipping') !== 'undefined' && !$('rfq_lines_table').down('.dealer-shipping').hasClassName('no-display')) {
                    $('rfq_lines_table').down('.dealer-shipping').addClassName('no-display');
                }
                if ($('rfq_lines_table').down('.grand_total').hasClassName('no-display')) {
                    $('rfq_lines_table').down('.grand_total').removeClassName('no-display');
                }
                if (!$('rfq_lines_table').down('.dealer-grand_total').hasClassName('no-display')) {
                    $('rfq_lines_table').down('.dealer-grand_total').addClassName('no-display');
                }
            }
        },
    };


    lines = new Epicor_Lines.lines();
    window.lines = lines;

    line_count = $$('#rfq_lines_table tbody tr.line_row').length;
    Event.live('#add_line', 'click', function (el, event) {
        $('line-add').show();
        $$("[id^=la_row_]").each(function (e) {
            e.show();
        });

        $('line-search').hide();
        var laSearchForm = [];
        tempForm = new Epicor.searchForm('la_row_1', 'la_sku_1', '', '', 'la_uom_1', 'la_pack_1', 'la_product_id_1', '', 'la_qty_1');
        tempForm = tempForm.initAutocomplete($('la_submit_url').value, 'lineadd_autocomplete_1');
        laSearchForm[laSearchForm.length] = tempForm;
        event.stop();
    });


    Event.live('.la_sku', 'keydown', function (a, b) {
        if (b.key == 'Tab') {
            if (!b.shiftKey) {
                a.up('.la_row').down('.la_quantity').focus();
                b.stop();
            } else {
                var currentRowDataArray = a.id.split('_');
                var currentRowNumber = Number(currentRowDataArray[2]);
                if (currentRowNumber > 1) {
                    $('lineadd_autocomplete_' + currentRowNumber).hide();
                } else {
                    b.stop();
                }
            }
        }
    });

    Event.live('.la_quantity', 'keydown', function (a, b) {
        if (b.key == 'Tab') {
            if (!b.shiftKey) {
                var focusOnNextRow = false;
                var rows = jQuery('.la_quantity');
                rows.each(function (index, value) {
                    var size = rows.size();
                    if (focusOnNextRow){
                        focusOnNextRow = false;
                        nextRowArray = this.id.split('_');
                        nextRowNumber =  nextRowArray[2];
                        jQuery('#la_sku_' + nextRowNumber).focus();
                        return false;
                    }
                    //if on last row add new row
                    if(size == parseInt(index + 1)){
                        lines.addLineAddRow(a);
                        b.stop();
                        return false;
                    }else {
                        //else focus on line after current one
                        if (a.id == this.id) {
                            focusOnNextRow = true;
                        }
                    }
                });
                b.stop();
            }
        }
    });
    Event.live('.la_quantity', 'keyup', function (el, e) {
//--SF        formatNumber(el, false, true);
//--Removing for WSO-6132 common.formatNumber(el, false, false);
    });

    Event.live('.la_custompart', 'click', function (el) {
        var rowId = el.readAttribute('id').replace('la_custompart_', '');
        var skubox = $('la_row_' + rowId).down('.la_sku_box');
        var namebox = $('la_row_' + rowId).down('.la_name_box');

        if (el.checked) {
            namebox.down('.input-text').value = skubox.down('.input-text').value;
            skubox.down('.input-text').value = '';
            skubox.hide();
            namebox.show();
        } else {
            skubox.down('.input-text').value = namebox.down('.input-text').value;
            namebox.down('.input-text').value = '';
            namebox.hide();
            skubox.show();
        }
    });

    Event.live('#lineadd-add', 'click', function (el, event) {
        lines.addLineAddRow($$('#line-add .la_row .la_quantity').shift());
        event.stop();
    });


    Event.live('#line-add-close', 'click', function (el, event) {
        $$("#line-add [id^=la_row_]").each(function (e) {
            e.show();
        });

        lines.resetLineAdd();
        event.stop();
    });

    Event.live('.la_delete', 'click', function (el, event) {
        if (lineadd_count > 1) {
            el.up('.la_row').remove();
        }
        //if only one row left, don't display the delete button
        if ( jQuery('.la_delete').length == 1 ) {
            var lastRow = jQuery('.la_delete');
            lastRow.tabIndex = 0;
            lastRow.hide();
        }

        event.stop();
    });

    Event.live('#lineadd-submit', 'click', function (el, event) {
        var valid = lines.validateQty('la_rows');
        if (!valid) {
            return false;
        }
        lines.processLinesAdd(event);
    });

    /***********************************************************
     * Line Add By Search
     */

    Event.live('#add_search', 'click', function (el, event) {
        //     $('loading-mask').show();
        jQuery('body').loader('show');
        if($('linesearch-iframe').readAttribute('dealerpage') == 1) {
            $('linesearch-iframe').writeAttribute('src', '/dealerconnect/quotes/linesearch/');
        }else {
            $('linesearch-iframe').writeAttribute('src', '/customerconnect/rfqs/linesearch/');
        }
        $('line-search').show();
        $('line-add').hide();
        event.stop();
    });

    Event.live('#line-search-close', 'click', function (el, event) {
        $('line-search').hide();
        $('linesearch-iframe').writeAttribute('src', '');
        event.stop();
    });

    /***********************************************************
     * Line Selections
     */

    Event.live('#clone_selected', 'click', function (el, event) {

        if ($$('.lines_select:checked').length > 0) {
            if (lines.confirmLinesClone()) {
                lines.cloneLines();
                rfqHasChanged();
            }
        } else {
            alert(jQuery.mage.__('Please select one or more lines'));
        }
        event.stop();
    });

    Event.live('#delete_selected', 'click', function (el, event) {
        if ($$('.lines_select:checked').length > 0) {
            if (lines.confirmLinesDelete()) {
                lines.deleteLines();
                common.checkCount('rfq_lines', 'lines_row', 3);
                lines.recalcLineTotals();
                rfqHasChanged();
            }
        } else {
            alert(jQuery.mage.__('Please select one or more lines'));
        }
        event.stop();
    });

    /***********************************************************
     * Line Editing
     */

    Event.live('.lines_quantity', 'change', function (el) {
        hideButtons();
// Commenting for WSO-6132 common.formatNumber(el, false, false);
//--SF        formatNumber(el, false, true);
        parentRow = el.up('tr');
        if (parentRow.down('.lines_type').value == 'S') {
            lines.sendMsqForLine(parentRow);
        } else {
            lines.recalcLineTotals();
        }
    });

    // jQuery('.lines_line_ecc_return_type_select').live('change',function(){
    jQuery('.lines_line_ecc_return_type_select').on('change',function(){
        jQuery('.loading-mask').show();
        var returnType = jQuery(this).val();
        jQuery(this).find("option[selected]").attr('selected', false);
        jQuery(this).find("option[value=" + returnType +"]").attr('selected', true);
        jQuery('.loading-mask').hide();
    });

    /***********************************************************
     * Product Configuration
     */

    Event.live('#configure_product', 'click', function (el, event) {
        $('loading-mask').show();
        lines.submitConfigurableProduct();
        event.stop();
    });

    Event.live('#cancel_configure_product', 'click', function (el, event) {
        $('window-overlay').hide();
        event.stop();
    });


    /***********************************************************
     * Line Attachments
     */

    Event.live('.line_attachments_delete', 'click', function (el) {

        if (deleteWarning(el)) {
            hideButtons();
            var tableId = el.up('table').readAttribute('id');
            common.deleteElement(el, tableId);
            common.checkCount(tableId.replace('_table', ''), 'line_attachment_row', 4);
        }
    });

    Event.live('.rfq_line_attachment_add', 'click', function (el) {
        hideButtons();
        lines.addLineAttachment(el);
    });
});