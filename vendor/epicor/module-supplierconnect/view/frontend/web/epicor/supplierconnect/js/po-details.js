/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
require([
    'jquery',
    'Magento_Customer/js/customer-data',
    'Epicor_Supplierconnect/js/supplierconnect/supplierconnect',
    'prototype',
    'mage/validation'
], function (
    jQuery,
    customerData,
    supplierconnect
) {
    jQuery(function () {

        var line_attachment_count = 0;
        var header_attachment_count = 0;

        jQuery("#purchase_order_save").on('click', function(event) {
            event.preventDefault();
            if (!validatePoForm()) {
                jQuery('body').loader('hide');
            } else {
                jQuery('body').trigger('processStart');
                var url = jQuery('#purchase_order_update').attr('action');
                url = url + (url.match(new RegExp('\\?')) ? '&isAjax=true' : '?isAjax=true');
                var form =jQuery('#purchase_order_update');
                var formParams = form.serializeArray();
                var data = new FormData();
                jQuery.each(formParams, function(key, value) {
                    data.append(value.name, value.value);
                });
                $$('#purchase_order_update input[type="file"]').each(function (elem) {
                    if (elem.name != '')
                    {
                        if(typeof( elem.files[0]) !== 'undefined')
                            data.append(elem.name, elem.files[0], elem.files[0].filename);
                    }
                });
                jQuery.ajax({
                    url: url, data: data,
                    type: 'post',
                    async: false,
                    contentType: false,
                    cache: false,
                    processData: false,
                    global: false,
                    // showLoader: true,
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
        });

        if (jQuery('#add_attachment').length > 0) {
            jQuery('#add_attachment').click(function(event) {
                jQuery('#supplier_orders_attachments_table tbody tr:not(.attachments_row)').each(function(e) {
                    jQuery(this).remove();
                });
                var elem = jQuery('#attachments_row_template').clone();
                var row = addAttachment(elem, header_attachment_count, 'attachments', '');
                jQuery('#supplier_orders_attachments').find('tbody').append(row);
                colorRows('supplier_orders_attachments');
                header_attachment_count += 1;
            });
        }

        if (jQuery('.order_line_attachment_add').length > 0) {
            jQuery('.order_line_attachment_add').click(function () {
                var rowId = jQuery(this).attr('id').replace('add_line_attachment_', '');
                var lineElem = jQuery('#line_attachment_row_template_'+rowId).clone();
                var lineRow = addAttachment(lineElem, line_attachment_count, 'line_attachments',rowId);
                jQuery('#order_line_attachments_' + rowId + '_table').find('tbody').append(lineRow);
                colorRows('order_line_attachments_' + rowId + '_table');
                line_attachment_count += 1;
            });
        }

        jQuery(document).click(function(e) {
            var target = e.target.className;
            if (target.indexOf('attachments_delete') != -1) {
                var el = jQuery(e.target);
                if(deleteWarning(el)){
                    deleteElement(el, 'supplier_orders_attachments');
                    checkCount('supplier_orders_attachments', 'attachments_row', 6);
                }
            }

        });

        function addAttachment(row, attchment_count, attch_class, rowId) {
            row.attr('id', attch_class + '_' + attchment_count);
            row.addClass('new');
            row = resetInputs(row);

            var inputName = attch_class + '[new][' + attchment_count + ']';
            if (rowId != '') {
                var attchclass = attch_class.replace("_", "");
                var inputName = attchclass + '[new][' + rowId + '][' + attchment_count + ']';
            }
            row.find('.' + attch_class + '_delete').attr('name', inputName + '[delete]');
            row.find('.' + attch_class + '_description').attr('name', inputName + '[description]');
            row.find('.' + attch_class + '_filename').attr('name', inputName + '[filename]');
            return row;
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

        function checkCount(table, rowclass, colspan) {
            var rowCount = jQuery('#' + table + '_table tbody tr.' + rowclass).length;
            if (rowCount == 0) {
                row = '<tr class="even" style="">'
                    + '<td colspan="' + colspan + '" class="empty-text a-center">No records found.</td>'
                    + '</tr>';

                jQuery('#' + table + '_table').find('tbody').append(row);

            }
        }

        function deleteWarning(el) {
            let allowDelete = true;
            if (confirm(jQuery.mage.__('Are you sure you want to delete selected line?')) === false) {
                allowDelete = false;
            }
            return allowDelete;
        }

        function showMessage(txt, type) {
            customerData.set('messages', {
                messages: [{
                    type: type,
                    text: txt
                }]
            });
        }

        function validatePoForm() {
            var poform = jQuery('#purchase_order_update');
            return validateSupplierForm(poform, "orders", true);
        }
    });
});
