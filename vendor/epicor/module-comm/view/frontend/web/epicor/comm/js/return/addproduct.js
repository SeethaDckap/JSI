/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery',
    'Epicor_Comm/epicor/comm/js/return/tabmaster',
    'prototype'
], function (jQuery, TabMaster) {
    
var AddProduct = Class.create();
AddProduct.prototype = new TabMaster();
AddProduct.prototype.tab = 'products';

AddProduct.prototype.addBeforeSaveFunction('addproduct', function () {
    if ($('add-product-submit')) {
        $('add-product-submit').hide();
    }
    if ($('find-product-submit')) {
        $('find-product-submit').hide();
    }
});

AddProduct.prototype.addBeforeNextStepFunction('addproduct', function () {
    if ($('add-product-submit')) {
        $('add-product-submit').show();
    }
    if ($('find-product-submit')) {
        $('find-product-submit').show();
    }
});

AddProduct.prototype.nextStep = function (transport) {

    if (transport && transport.responseText) {
        try {
            response = eval('(' + transport.responseText + ')');
        }
        catch (e) {
            response = {};
        }
    }

    this.beforeNextStep(response);

    /*
     * if there is an error in payment, need to show error message
     */

    if (response.errors) {
        errorMessage = '';
        join = '';
        for (var i = 0; i < response.errors.length; i++) {
            errorMessage += join + response.errors[i];
            join = '\n';
        }

        alert(errorMessage);
        return;
    }

    if (!response.lines) {
        returns.setStepResponse(response);
    } else {
        for (index = 0; index < response.lines.length; index++) {
            product = response.lines[index];
            addLineRow(product);
        }

        $('sku').value = '';
        $('sku_super_group').value = '';
        $('sku_uom').value = '';
        $('qty').value = '';
        $('packsize_field').hide();

        if (response.hide_add_sku) {
            if ($('add-product-form-holder')) {
                $('add-product-form-holder').hide();
            }

            if ($('lines-adder')) {
                $('lines-adder').removeClassName('col2-set');
            }
        }

        if (response.hide_find_by) {
            if ($('find-product-form-holder')) {
                $('find-product-form-holder').hide();
            }

            if ($('lines-adder')) {
                $('lines-adder').removeClassName('col2-set');
            }
        }

        if (response.restrict_type) {
            if ($('search_type')) {
                $('search_type').select('option').each(function (i) {
                    if (i.value != response.restrict_type) {
                        i.remove();
                    }
                });
                $('search_type').hide();
                $('search_type_label').hide();
                $('search_value_label_text').innerHTML = 'Search By ' + response.restrict_type.capitalizeFirstLetter() + ' Number';
            }
        }
    }
}

line_count = 0;

function addLineRow(product) {

    $$('#return_lines_table tbody tr:not(.lines_row)').each(function (e) {
        if (typeof e.up('.lines_row') === 'undefined') {
            e.remove();
        }
    });

    var row = $('return_lines_row_template').clone(true);
    row.addClassName('new');

    row.setAttribute('id', 'lines_' + line_count);

    row = resetInputs(row);

    if (row.down('.plus-minus')) {
        row.down('.plus-minus').writeAttribute('id', 'return_line_attachments_' + line_count);
    }

    row.down('.return_line_number').update(next_line_number);
    next_line_number++;

    var sku_display = product.sku;

    if (product.type_id == 'configurable' || product.type_id == 'grouped') {
        sku_display = sku_display + '<br /><a href="javascript:fireConfigurableProduct(\'' + product.type_id + '\',\'' + line_count + '\',\'' + product.entity_id + '\')">' + 'Configure' + '</a>';
        row.down('.return_line_configured').writeAttribute('value', 'TBC');
        alert('This line requires configuration, please click the "Configure" link\n');
    }

    if (!product.uom) {
        product.uom = '';
    }
    row.down('.return_sku').update(sku_display);
    row.down('.return_uom').update(product.uom);
    row.down('.return_line_sku').writeAttribute('name', 'lines[' + line_count + '][sku]').writeAttribute('value', product.sku);
    row.down('.return_line_uom').writeAttribute('name', 'lines[' + line_count + '][uom]').writeAttribute('value', product.uom);
    row.down('.return_line_returncode').writeAttribute('name', 'lines[' + line_count + '][return_code]').addClassName('validate-select');
    if($$('.return_line_notes').first() != null && $$('.return_line_notes').first() !== 'undefined'){        
        row.down('.return_line_notes').writeAttribute('name', 'lines[' + line_count + '][note_text]');
    }
    row.down('.return_line_source_type').writeAttribute('name', 'lines[' + line_count + '][source]').writeAttribute('value', product.source);
    source_type = product.source.capitalizeFirstLetter();
    source_value = product.source_label.replace(source_type + ' #', '');
    row.down('.return_line_source_value').writeAttribute('name', 'lines[' + line_count + '][source]').writeAttribute('value', source_value);
    row.down('.return_line_source').writeAttribute('name', 'lines[' + line_count + '][source]').writeAttribute('value', product.source);
    row.down('.return_line_source_data').writeAttribute('name', 'lines[' + line_count + '][source_data]').writeAttribute('value', product.source_data);
    row.down('.return_line_delete').writeAttribute('name', 'lines[' + line_count + '][delete]');
    row.down('.return_line_quantity_returned').writeAttribute('name', 'lines[' + line_count + '][quantity_returned]').writeAttribute('value', product.qty_returned);
    if (product.decimal_place != "") {
    var decimalPlace = '{"validatedecimalplace":' + product.decimal_place + '}';
        row.down('.return_line_quantity_returned').writeAttribute('name', 'lines[' + line_count + '][quantity_returned]').writeAttribute('data-validate', decimalPlace);
    }
    row.down('.return_line_quantity_ordered').writeAttribute('name', 'lines[' + line_count + '][quantity_ordered]').writeAttribute('value', product.qty_ordered);

    if (product.qty_ordered != undefined) {
        row.down('.return_line_quantity_ordered_label').update(' / ' + product.qty_ordered);
    }

    row.down('.return_line_source_label').update(product.source_label);
    $('return_lines_table').down('tbody').insert({bottom: row});

    var row = $('return_line_attachments_row_template').clone(true);

    row.addClassName('new');
    row.setAttribute('id', 'row_return_line_attachments_' + line_count);
    row.down('.return_line_attachment_add').writeAttribute('id', 'add_return_line_attachments_' + line_count);
    row.down('#return_line_attachments_').writeAttribute('id', 'return_line_attachments_' + line_count);
    row.down('#return_line_attachments__table').writeAttribute('id', 'return_line_attachments_' + line_count + '_table');
    row.down('#return_line_attachments__attachment_row_template').writeAttribute('id', 'return_line_attachments_' + line_count + '_attachment_row_template');

    $('return_lines_table').down('tbody').insert({bottom: row});

    colorRows('return_lines_table', ':not(.attachment)');
    resetLineNumbers();
    line_count += 1;
}
String.prototype.capitalizeFirstLetter = function () {
    return this.charAt(0).toUpperCase() + this.slice(1);
}
next_line_number = 1;

function resetLineNumbers() {
    next_line_number = 1;
    $$('#return_lines_table .return_line_number').each(function (el) {
        el.update(next_line_number);
        next_line_number += 1;
    });
}

function colorRows(table_id, table_extra) {

    var cssClass = 'even';
    $$('#' + table_id + ' tbody tr' + table_extra).findAll(function (el) {
        return el.visible();
    }).each(function (e) {
        if (e.visible()) {
            e.removeClassName('even');
            e.removeClassName('odd');
            e.addClassName(cssClass);

            if (cssClass == 'even') {
                cssClass = 'odd';
            } else {
                cssClass = 'even';
            }
        }
    });
}

function resetInputs(row) {
    row.select('input,select,textarea').each(function (e) {
        if (e.readAttribute('type') == 'text' || e.tagName == 'textarea') {
            e.writeAttribute('value', '');
        } else if (e.readAttribute('type') == 'checkbox') {
            e.writeAttribute('checked', false);
        }

        e.writeAttribute('disabled', false);
    });

    return row;
}
  return AddProduct;
  
});