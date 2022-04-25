/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery',
    'Epicor_Comm/epicor/comm/js/return/tabmaster',
    'mage/translate',
    'prototype',
    'mage/validation',
    'Epicor_Common/epicor/common/js/attachment-validations'
], function (jQuery, TabMaster, $t) {

    var Attachments = Class.create();
    Attachments.prototype = new TabMaster();
    Attachments.prototype.tab = 'attachments';
    Attachments.prototype.addBeforeSaveFunction('attachments', function () {
        if ($('attachments-submit')) {
            $('attachments-submit').hide();
        }
    });
    Attachments.prototype.addBeforeNextStepFunction('attachments', function () {
        if ($('attachments-submit')) {
            $('attachments-submit').show();
        }
    });
    Attachments.addMethods({
        attachments_count: 0,
        add: function (table_id, fieldName) {

            // customer_returns_attachment_lines_table
            $$('#' + table_id + '_table tbody tr:not(.attachments_row)').each(function (e) {
                e.remove();
            });
            this.attachment_count = $$('#' + table_id + '_table tbody tr.attachments_row').length;

            var row = $(table_id + '_attachment_row_template').clone(true);
            row.addClassName('new');
            row.setAttribute('id', table_id + 'attachments_' + this.attachments_count);

            resetInputs(row);

            row.down('.attachments_delete').writeAttribute('name', fieldName + '[' + this.attachments_count + '][delete]');
            row.down('.attachments_description').writeAttribute('name', fieldName + '[' + this.attachments_count + '][description]');
            row.down('.attachments_filename').writeAttribute('name', fieldName + '[' + this.attachments_count + '][filename]');

            $(table_id + '_table').down('tbody').insert({bottom: row});
            colorRows(table_id, '');
            this.attachments_count += 1;
        }
    });


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
    function deleteElement(el, table_id) {
        var disabled = false;
        if (el.checked) {
            disabled = true;
        }
        if (el.parentNode.parentNode.hasClassName('new')) {
            el.parentNode.parentNode.remove();
            colorRows(table_id, '');
        } else {
            el.parentNode.parentNode.select('input[type=text],input[type=file],select,textarea').each(function (input) {
                input.disabled = disabled;
                ignoreValidationDisabled(input, 'validate-select', 'return_line_returncode', 'SELECT', disabled);
            });
        }
    }

    function ignoreValidationDisabled(input, className1, className2, typename, disabled) {
        if (input.hasClassName(className1) && disabled && input.tagName == typename && input.hasClassName(className2)) {
            input.removeClassName(className1);
            var elements = 'advice-validate-select-' + input.name;
            if ($(elements) != undefined) {
                $(elements).hide();
            }

        } else {
            if (input.hasClassName(className2) && input.tagName == typename && !disabled && !input.hasClassName(className1)) {
                input.addClassName(className1);
            }
        }
    }

    function checkCount(table, rowclass, colspan) {
        var rowCount = $$('#' + table + '_table tbody tr.' + rowclass).findAll(function (el) {
            return el.visible();
        }).length;
        if (rowCount == 0) {
            row = '<tr class="even" style="">'
                + '<td colspan="' + colspan + '" class="empty-text a-center">' + $t('No records found.') + '</td>'
                + '</tr>';

            $(table + '_table').down('tbody').insert({bottom: row});

        }
    }
    jQuery(document).ready(function (element) {

        Event.live('.return_line_delete', 'click', function (el, event) {
            var attRowId = el.up('tr').readAttribute('id').replace('lines_', 'row_return_line_attachments_');
            deleteElement(el, 'return_lines');
            if (el.parentNode.parentNode.hasClassName('new')) {
                if ($(attRowId)) {
                    $(attRowId).remove();
                }
            }
            checkCount('return_lines', 'lines_row', 10);
            colorRows('return_lines_table', ':not(.attachment)');
            resetLineNumbers();
        });

        Event.live('.attachments_delete', 'click', function (el, event) {

            var table = el.up('table').readAttribute('id').replace('_table', '');
            deleteElement(el, table);
            checkCount(table, 'attachments_row', 3);
        });


        Event.live('.attachments_add', 'click', function (el, event) {
            var table = el.readAttribute('id').replace('add_', '');
            var fieldId = el.readAttribute('id').replace('add_return_line_attachments_', '').replace('add_return_attachments_', '');
            Attachments.prototype.add(table, 'lineattachments[new][' + fieldId + ']');
            event.stop();
        });

        Event.live('.return_attachments_add', 'click', function (el, event) {
            var table = el.readAttribute('id').replace('add_', '');
            Attachments.prototype.add(table, 'attachments[new]');
            event.stop();
        });

        Event.live('.expand-row', 'mouseover', function (element) {
            element.style.cursor = "pointer";
        });

        Event.live('.expand-row', 'click', function (element) {
            id = element.down(".plus-minus").readAttribute('id');
            if ($('row_' + id)) {
                $('row_' + id).toggle();
                element.down(".plus-minus").innerHTML == '-' ? element.down(".plus-minus").innerHTML = '+' : element.down(".plus-minus").innerHTML = '-';
            }
        });

        Event.live('#lines-submit', 'click', function (el, event) {
            linesform = $('lines-form');
            let validFileSize = isValidFileSize();
            var valid = jQuery("#" + linesform.id).validation() && jQuery("#" + linesform.id).validation('isValid');
            let attachmentError = isAttachmentFileNameTooLong();
            /**BOF JIRA 8401**/
            //check line level attachment of return uploaded or not
            const attachmentLineLevelTableTrace = 'return_lines_table tbody tr.attachment table.data-grid tbody tr.attachments_row';
            const uploaded = attachmentUploaded(attachmentLineLevelTableTrace);
            /**EOF JIRA 8401**/
            configuratorError = false;
            $$('.return_line_configured').each(function (e) {
                if (e.value == 'TBC') {
                    configuratorError = true;
                }
            });
            if (!valid || configuratorError || attachmentError || !validFileSize) {

                if (configuratorError) {
                    alert('One or more lines require configuration, please see lines with a "Configure" link\n');
                }
                if (!validFileSize) {
                    alert('Error attachment file is empty contains 0 bytes ');
                }

                if (attachmentError) {
                    alert('Error an attachment file name exceeds ' + getAttachmentFileLimit() + ' characters');
                }

                event.stop();
            } else if (!uploaded) {
                alert("One or more line level attachment of return is not uploaded.Please upload the attachment!");
                event.stop();
                return false;
            } else {
                el.hide();
                $('lines-please-wait').show();
            }
        });

        Event.live('#attachments-submit', 'click', function (el, event) {
            var validFileSize = isValidFileSize();
            var attachmentError = isAttachmentFileNameTooLong();
            var error = false;
            /**BOF JIRA 8401**/
            //check  additional attachments of return uploaded or not
            const attachmentHeaderTableTrace = 'customer_returns_attachment_lines_table tbody tr.attachments_row';
            const uploadedHeaderLevel = attachmentUploaded(attachmentHeaderTableTrace);
            /**EOF JIRA 8401**/
            if (!validFileSize) {
                error = true;
                alert('Error attachment file is empty contains 0 bytes ');
            }

            if (attachmentError) {
                error = true;
                alert('Error an attachment file name exceeds ' + getAttachmentFileLimit() + ' characters');
            }
            if (!uploadedHeaderLevel) {
                error = true;
                alert("One or more additional attachment of return is not uploaded.Please upload the attachment!");
            }

            if (error) {
                event.stop();
            } else {
                el.hide();
                $('attachments-please-wait').show();
            }
        });

    });

    function isValidFileSize() {
        let attachmentRows = jQuery('tr.attachments_row');
        let validFileSize = true;
        attachmentRows.each(function (i, e) {
            let attachmentData = jQuery(this).find('input[type="file"]');
            if (isFileNameSetOnInputField(attachmentData)) {
                let fileSize = attachmentData[0]['files'][0]['size'];
                if (fileSize < 1) {
                    validFileSize = false;
                    return true;
                }
            }
        });

        return validFileSize;
    }


    function resetLineNumbers() {
        next_line_number = 1;
        $$('#return_lines_table .return_line_number').each(function (el) {
            el.update(next_line_number);
            next_line_number += 1;
        });
    }

    function isAttachmentFileNameTooLong() {
        var attachmentLimit = getAttachmentFileLimit();
        if (!attachmentLimit) {
            return false;
        }
        var attachmentRows = jQuery('tr.attachments_row');
        var attachmentError = false;
        attachmentRows.each(function (i, e) {
            var attachmentData = jQuery(this).find('input[type="file"]');

            if (isFileNameSetOnInputField(attachmentData)) {
                var fileName = attachmentData[0]['files'][0]['name'];

                if (fileName.length > attachmentLimit) {
                    attachmentError = true;
                    return true;
                }
            }
        });

        return attachmentError;
    }

    function getAttachmentFileLimit() {
        var formData = jQuery('#returnSteps');
        if (formData.attr('data-attachment') > 0) {
            return formData.attr('data-attachment');
        } else {
            return false;
        }
    }
    function isFileNameSetOnInputField(attachmentData) {
        if (attachmentData) {
            return attachmentData[0] !== undefined
                && attachmentData[0]['files'] !== undefined
                && attachmentData[0]['files'][0] !== undefined
                && attachmentData[0]['files'][0]['name'] !== undefined
        }
    }

    return Attachments;

});