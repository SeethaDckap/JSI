/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
require([
    'jquery',
    'domReady',
    'Magento_Customer/js/customer-data'
], function (
        jQuery,
        domReady,
        customerData
) {
    domReady(function() {

        if (jQuery("#rfq_save").length > 0) {
            jQuery(document).on('click', '#rfq_save', function(event) {
                if (!validateRfqForm()) {
                    return false;
                    event.preventDefault();
                } else {
                    event.preventDefault();
                    var url = jQuery('#rfq_update').attr('action');
                    url = url + (url.match(new RegExp('\\?')) ? '&isAjax=true' : '?isAjax=true');
                    var form =jQuery('#rfq_update');
                    var formParams = form.serializeArray();
                    var data = new FormData();
                    jQuery.each(formParams, function(key, value) {
                        data.append(value.name, value.value);
                    });
                    $$('#rfq_update input[type="file"]').each(function (elem) {
                        if (elem.name != '')
                        {
                            if(typeof( elem.files[0]) !== 'undefined')
                                data.append(elem.name, elem.files[0], elem.files[0].filename);
                        }
                    });
                    jQuery('body').trigger('processStart');
                    jQuery.ajax({
                        url: url, data: data,
                        type: 'post',
                        async: false,
                        contentType: false,
                        cache: false,
                        processData: false,
                        global: false,
                        success: function (response) {
                            if (response.type == 'success') {
                                if (response.redirect) {
                                    window.location.replace(response.redirect);
                                }
                            } else {
                                setTimeout(function() {
                                    jQuery('body').trigger('processStop');
                                }, 1000);
                                if (response.message) {
                                    showMessage(response.message, response.type);
                                }
                            }
                        },
                        error: function () {
                            alert('Error occurred in Ajax Call');
                            jQuery('body').trigger('processStop');
                        }
                    });
                }
                event.preventDefault();
            });
        }

        jQuery(document).change(function(e) {
            var target = e.target.className;
            var targetId = e.target.id;
            if (targetId.indexOf('base_unit_price') != -1) {
                var el = jQuery(e.target);
                formatNumber(el, false, true);
                recalcPriceBreaks();
            } else if (targetId.indexOf('discount_percent') != -1) {
                recalcPriceBreaks();
            } else if (target.indexOf('price_break_modifier') != -1) {
                var el = jQuery(e.target);
                recalcPriceBreakRow(el.parent().parent());
            } else if (targetId.indexOf('price_break_modifier') != -1) {
                recalcPriceBreaks();
            } else if (targetId.indexOf('expires_date') != -1) {
                recalcExpiry('days');
            } else if (targetId.indexOf('days') != -1) {
                recalcExpiry('expires_date');
            } else if (target == "cross_reference_part_manufacturer") {
                var el = jQuery(e.target);
                updateManufacturerProductCodes(el);
            }
        });

        jQuery(document).click(function(e) {
            var target = e.target.className;
            if (target.indexOf('price_break_delete') != -1) {
                var el = jQuery(e.target);
                deleteElement(el, 'rfq_price_breaks');
                checkCount('rfq_price_breaks', 'qpb_row', 4);
            } else if (target.indexOf('suom_delete') != -1) {
                var el = jQuery(e.target);
                deleteElement(el, 'rfq_supplier_unit_of_measures');
                checkCount('rfq_supplier_unit_of_measures', 'suom_row', 6);
            } else if (target.indexOf('cross_reference_part_delete') != -1) {
                var el = jQuery(e.target);
                deleteElement(el, 'rfq_cross_reference_parts');
                checkCount('rfq_cross_reference_parts', 'xref_row', 6);
            } else if (target.indexOf('attachments_delete') != -1){
                var el = jQuery(e.target);
                if(deleteWarning(el)){
                    deleteElement(el, 'supplier_rfq_attachments');
                    checkCount('supplier_rfq_attachments', 'attachments_row', 6);
                }
            }
        });

        jQuery(document).keyup(function(e) {
            var target = e.target.className;
            var targetId = e.target.id;
            if (target.indexOf('price_break_modifier') != -1) {
                var el = jQuery(e.target);
                formatNumber(el, true, true);
            } else if (target.indexOf('price_break_min_quantity') != -1) {
                var el = jQuery(e.target);
                formatNumber(el, false, true);
            } else if (target.indexOf('price_break_days_out') != -1) {
                var el = jQuery(e.target);
                formatNumber(el, false, false);
            } else if (target.indexOf('suom_value') != -1) {
                var el = jQuery(e.target);
                formatNumber(el, false, true);
            } else if (target.indexOf('cross_reference_part_supplier_lead_days') != -1) {
                var el = jQuery(e.target);
                formatNumber(el, false, false);
            }
            /*else if (targetId.indexOf('base_unit_price') != -1) {
                var el = jQuery(e.target);
                formatNumber(el, false, true);
            }*/
            else if (targetId.indexOf('minimum_price') != -1) {
                var el = jQuery(e.target);
                formatNumber(el, false, true);
            } else if (targetId.indexOf('discount_percent') != -1) {
                var el = jQuery(e.target);
                formatNumber(el, true, true);
            } else if (targetId.indexOf('lead_days') != -1) {
                var el = jQuery(e.target);
                formatNumber(el, false, false);
            } else if (targetId.indexOf('quantity_on_hand') != -1) {
                var el = jQuery(e.target);
                formatNumber(el, false, false);
            } else if (targetId.indexOf('days') != -1) {
                var el = jQuery(e.target);
                formatNumber(el, false, false);
            }
        });

        if (jQuery('#add_price_break').length > 0) {
            var price_break_count = jQuery('#rfq_price_breaks tbody tr.qpb_row').length;
            jQuery('#add_price_break').click(function(event) {
                addPriceBreakRow();
            });
        }

        if (jQuery('#add_attachment').length > 0) {
            var attachment_count = jQuery('#supplier_rfq_attachments tbody tr.attachments_row').length;
            jQuery('#add_attachment').click(function(event) {
                addAttachmentRow();
            });
        }

        // Supplier uom processing
        if (jQuery('#add_suom').length > 0) {
            var suom_count = jQuery('#rfq_supplier_unit_of_measures tbody tr.suom_row').length;
            jQuery('#add_suom').click(function(event) {
                addSuomRow();
            });
        }

        // Supplier uom processing
        if (jQuery('#add_cross_reference_part').length > 0) {
            jQuery('#add_cross_reference_part').click(function(event) {
                addCrossReferencePartRow();
            });
        }
    });

    function showMessage(txt, type) {
        customerData.set('messages', {
            messages: [{
                type: type,
                text: txt
            }]
        });
    }

    function validateRfqForm() {

        var valid           = true;
        var rfqForm         = jQuery('#rfq_update');
        var validAttachment = true;
        // validate UOMS
        // - must not be duplicate UOM
        // - must be at least one
        var errorMessage = '';
        suomlist = jQuery('#rfq_supplier_unit_of_measures_table tbody tr.suom_row');
        if (suomlist.length == 0) {
            errorMessage += 'You must supply at least one Supplier Unit Of Measure\n';
        } else {
            var blank = true;
            var duplicates = false;
            var uomArray = new Array();
            suomlist.each(function(e) {
                var uom = jQuery(this).find('.suom_unit_of_measure').val();
                var deleted = false;

                if(jQuery(this).find('.suom_delete').length > 0) {
                    var deleted = jQuery(this).find('.suom_delete').is(":checked");
                }

                if (uom != '' && !deleted) {
                    blank = false;
                    if (uomArray.indexOf(uom) != -1) {
                        duplicates = true;
                    }
                    uomArray[uomArray.length] = uom;
                }
            });

            if (blank) {
                errorMessage += 'You must supply at least one Supplier Unit Of Measure \n';
            } else if (duplicates) {
                errorMessage += 'You must supply only unique Supplier Unit Of Measures \n';
            }
        }

        // - Cross reference parts
        // - no duplicate combo of Manufacturer + Manufacturers part + Supplier part
        xrlist = jQuery('#rfq_cross_reference_parts_table tbody tr.xref_row');
        var duplicates = false;
        var missing = false;
        xrArray = new Array();
        xrlist.each(function(e) {
            var manufacturer = jQuery(this).find('.cross_reference_part_manufacturer').val();
            var manufacturer_product = jQuery(this).find('.cross_reference_part_manufacturers_product_code').val();
            var supplier_product = jQuery(this).find('.cross_reference_part_supplier_product_code').val();
            var combined = manufacturer.trim() + manufacturer_product.trim() + supplier_product.trim();
            var deleted = jQuery(this).find('.cross_reference_part_delete').is(":checked");
            if (combined != '' && !deleted) {
                blank = false;
                if(supplier_product == '') {
                     missing = true;
                }

                if (xrArray.indexOf(combined) != -1) {
                    duplicates = true;
                }
                xrArray[xrArray.length] = combined;
            } else {
                missing = deleted ? false : true;
            }
        });

        if (duplicates) {
            errorMessage += 'Each Cross Reference Part must have a unique combination of Manufacturer, Manufacturer\'s Part and Supplier\'s Part \n';
        }

        if (missing) {
            errorMessage += 'You must supply a Supplier\'s Part for each Cross Reference Part\n';
        }

        // - Price breaks
        // - no duplicate quantity
        pblist = jQuery('#rfq_price_breaks_table tbody tr.qpb_row');
        duplicates = false;
        pbArray = new Array();
        pblist.each(function(e) {
            var quantity = jQuery(this).find('.price_break_min_quantity').val();
            var deleted = jQuery(this).find('.price_break_delete').is(":checked");
            if (quantity != '' && !deleted) {
                blank = false;
                if (pbArray.indexOf(parseInt(quantity)) != -1) {
                    duplicates = true;
                }
                pbArray[pbArray.length] = parseInt(quantity);
            }
        });

        if (duplicates) {
            errorMessage += 'You must supply only unique Quanities in the Quantity Price Breaks \n';
        }

        if (errorMessage != '') {
            valid = false;
            alert(errorMessage);
        }

        validAttachment = validateSupplierForm(rfqForm, "rfq", true);

        return valid && validAttachment;
    }

    function recalcExpiry(fieldToChange) {

        var newValue = '';

        if (fieldToChange == 'days') {
            var effectiveDate = new Date(jQuery('#effective_date').val());
            var expiryDate = new Date(jQuery('#expires_date').val());
            if (expiryDate.getFullYear() < 2000) {
                expiryDate.setFullYear(expiryDate.getFullYear() + 100);
            }
            newValue = Math.round((expiryDate - effectiveDate) / (1000 * 60 * 60 * 24));
        } else if (fieldToChange == 'expires_date') {
            var effectiveDate = new Date(jQuery('#effective_date').val());
            var days = parseInt(jQuery('#days').val());
            var expiryDate = new Date();
            expiryDate.setDate(effectiveDate.getDate() + days)
            newValue = (expiryDate.getMonth() + 1) + '/' + expiryDate.getDate() + '/' + expiryDate.getFullYear();
        }

        jQuery('#' + fieldToChange).val(newValue);

    }

    function recalcPriceBreaks() {
        jQuery('#rfq_price_breaks tbody tr.qpb_row').each(function(e) {
            recalcPriceBreakRow(jQuery(this));
        });
    }

    function recalcPriceBreakRow(el) {

        if (jQuery('#base_unit_price').val().length == 0) {
            jQuery('#base_unit_price').val(0);
        }

        if (jQuery('#discount_percent').val().length == 0) {
            jQuery('discount_percent').val(0);
        }

        if (el.find('.price_break_modifier').val().length == 0) {
            el.find('.price_break_modifier').val(0);
        }

        var basePrice = parseFloat(jQuery('#base_unit_price').val()).toFixed(5);
        var discount = parseFloat(jQuery('#discount_percent').val()).toFixed(5);
        var pbmodifier = jQuery('#price_break_modifier').val();
        var modifier = parseFloat(el.find('.price_break_modifier').val());

        switch (true) {
            case (pbmodifier == '$'):
                basePrice = parseFloat(basePrice) + parseFloat(modifier);
                break;
            default:
                basePrice = basePrice * ((100 - modifier) / 100);
                break;
        }

        if (discount > 0) {
            basePrice = basePrice * ((100 - discount) / 100);
        }

basePrice = basePrice.toFixed(5);
        el.find('.price_break_effective_price').val(basePrice);
        el.find('.price_break_effective_price_label').html(basePrice);
    }

    function updateManufacturerProductCodes(el) {
        var manufacturer = el.val();
        if (jQuery('#manufacturer_product_codes_' + manufacturer).length > 0) {
            var manufacturerHtml = jQuery('#manufacturer_product_codes_' + manufacturer).html();
            el.parent().parent().find(".cross_reference_part_manufacturers_product_code").html(manufacturerHtml);
        } else {
            el.parent().parent().find(".cross_reference_part_manufacturers_product_code").html('<option value=""></option>');
        }

    }

    function deleteElement(el, table_id) {
        var disabled = false;
        if (el.is(":checked")) {
            disabled = true;
        }
        if (el.parent().parent().hasClass('new')) {
            el.parent().parent().remove();
            colorRows(table_id, '');
        } else {
            el.parent().parent().find('input[type=text],input[type=file],select,textarea').each(function() {
                jQuery(this).attr('disabled', disabled);
            });
        }
    }

    function formatNumber(el, allowNegatives, allowFloats) {
        var value = el.val(), firstChar, nextFirst;
        if (value.length == 0)
            return;

        firstChar = value.charAt(0);
        if (allowFloats) {
            value = value.replace(/[^0-9\.]/g, '');
            nextFirst = value.charAt(0);
        } else {
            var repvalue = value.replace(/[^0-9]/g, '');
            nextFirst = repvalue.charAt(0);
            if (nextFirst != '.' && nextFirst != "") {
                value = Math.round(value);
            }
        }

        if (nextFirst == '.') {
            value = '0' + value;
        }

        if (allowNegatives && firstChar == '-') {
            value = firstChar + value;
        }

        el.val(value);
    }

    var price_break_count = 0;
    var suom_count = 0;
    var cross_reference_count = 0;
    var line_attachment_count = 0;

    function addPriceBreakRow() {
        jQuery('#rfq_price_breaks_table tbody tr:not(.qpb_row)').each(function(e) {
            jQuery(this).remove();
        });

        var row = jQuery('#price_break_row_template').clone();

        row.attr('id', 'price_breaks_' + price_break_count);
        row.addClass('new');
        row = resetInputs(row);

        row.find('.price_break_effective_price').attr('name', 'price_breaks[new][' + price_break_count + '][effective_price]');
        row.find('.price_break_min_quantity').attr('name', 'price_breaks[new][' + price_break_count + '][quantity]');
        row.find('.price_break_days_out').attr('name', 'price_breaks[new][' + price_break_count + '][days_out]');
        row.find('.price_break_modifier').attr('name', 'price_breaks[new][' + price_break_count + '][modifier]');
        jQuery('#rfq_price_breaks').find('tbody').append(row);
        row.find('.price_break_min_quantity').focus();
        colorRows('rfq_price_breaks');
        price_break_count += 1;
    }

    function addAttachmentRow() {
        jQuery('#supplier_rfq_attachments_table tbody tr:not(.attachments_row)').each(function(e) {
            jQuery(this).remove();
        });

        var row = jQuery('#attachments_row_template').clone();

        row.attr('id', 'attachments_' + line_attachment_count);
        row.addClass('new');
        row = resetInputs(row);

        row.find('.attachments_delete').attr('name', 'attachments[new][' + line_attachment_count + '][delete]');
        row.find('.attachments_description').attr('name', 'attachments[new][' + line_attachment_count + '][description]');
        row.find('.attachments_filename').attr('name', 'attachments[new][' + line_attachment_count + '][filename]');
        jQuery('#supplier_rfq_attachmentss').find('tbody').append(row);
        colorRows('supplier_rfq_attachments'); jQuery('#supplier_rfq_attachments').find('tbody').append(row);

        line_attachment_count += 1;
    }

    function checkCount(table, rowclass, colspan) {
        var rowCount = jQuery('#' + table + '_table tbody tr.' + rowclass).length;
        if (rowCount == 0) {
            row = '<tr class="even" style="">'
                    + '<td colspan="' + colspan + '" class="empty-text a-center">No records found.</td>'
                    + '</tr>';

            jQuery('#' + table + '_table').find('tbody').append(row);

        }
    }

    function addSuomRow() {

        jQuery('#rfq_supplier_unit_of_measures_table tbody tr:not(.suom_row)').each(function(e) {
            jQuery(this).remove();
        });

        var row = jQuery('#supplier_unit_of_measures_row_template').clone();
        row.addClass('new');
        row.attr('id', 'suom_' + suom_count);
        row = resetInputs(row);

        row.find('.suom_unit_of_measure').attr('name', 'supplier_unit_of_measures[new][' + suom_count + '][unit_of_measure]');
        row.find('.suom_conversion_factor').attr('name', 'supplier_unit_of_measures[new][' + suom_count + '][conversion_factor]');
        row.find('.suom_operator').attr('name', 'supplier_unit_of_measures[new][' + suom_count + '][operator]');
        row.find('.suom_value').attr('name', 'supplier_unit_of_measures[new][' + suom_count + '][value]');
        row.find('.suom_result').attr('name', 'supplier_unit_of_measures[new][' + suom_count + '][result]');
        row.find('.suom_delete').attr('name', 'supplier_unit_of_measures[new][' + suom_count + '][delete]');

        jQuery('#rfq_supplier_unit_of_measures').find('tbody').append(row);
        colorRows('rfq_supplier_unit_of_measures');
        suom_count += 1;
    }

    function addCrossReferencePartRow() {
        var cross_reference_count = jQuery('#rfq_cross_reference_parts tbody tr.xref_row').length;
        jQuery('#rfq_cross_reference_parts_table tbody tr:not(.xref_row)').each(function() {
            jQuery(this).remove();
        });

        var row = jQuery('#cross_reference_parts_row_template').clone();
        row.addClass('new');
        row.attr('id', 'cross_reference_part_' + cross_reference_count);
        row = resetInputs(row);

        row.find('.cross_reference_part_delete').attr('name', 'cross_reference_parts[new][' + cross_reference_count + '][delete]');
        row.find('.cross_reference_part_manufacturer').attr('name', 'cross_reference_parts[new][' + cross_reference_count + '][manufacturer_code]');
        row.find('.cross_reference_part_manufacturers_product_code').attr('name', 'cross_reference_parts[new][' + cross_reference_count + '][manufacturers_product_code]');
        row.find('.cross_reference_part_supplier_product_code').attr('name', 'cross_reference_parts[new][' + cross_reference_count + '][supplier_product_code]');
        row.find('.cross_reference_part_supplier_lead_days').attr('name', 'cross_reference_parts[new][' + cross_reference_count + '][supplier_lead_days]');
        row.find('.cross_reference_part_supplier_reference').attr('name', 'cross_reference_parts[new][' + cross_reference_count + '][supplier_reference]');

        jQuery('#rfq_cross_reference_parts').find('tbody').append(row);
        colorRows('rfq_cross_reference_parts');
        cross_reference_count += 1;
    }

    function resetInputs(row) {
        row.find('input,select').each(function(e) {
            if (jQuery(this).attr('type') == 'text') {
                jQuery(this).attr('value', '');
            } else if (jQuery(this).attr('type') == 'checkbox') {
                jQuery(this).attr('checked', false);
            }
            jQuery(this).attr('disabled', false);
        });
        return row;
    }

    function colorRows(table_id) {
        var cssClass = 'even';
        jQuery('#' + table_id + ' tbody tr').each(function() {
            jQuery(this).removeClass('even');
            jQuery(this).removeClass('odd');
            jQuery(this).addClass(cssClass);
            if (cssClass == 'even') {
                cssClass = 'odd';
            } else {
                cssClass = 'even';
            }
        });
    }

    function deleteWarning(el) {
        let allowDelete = true;
        if (confirm(jQuery.mage.__('Are you sure you want to delete selected line?')) === false) {
            allowDelete = false;
        }
        return allowDelete;
    }
});