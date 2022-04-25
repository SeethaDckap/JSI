/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
require([
    'jquery',
    'prototype',
    'mage/validation',
    'Epicor_Common/epicor/common/js/attachment-validations'
], function (
    jQuery,
) {
    jQuery(function () {

        if ($('supplier_connect_order_comments')) {
        }

        // Orders New List Page / confirm changes page
        $$(".po_confirm").invoke('observe', 'click', function() {
            if (this.checked) {
                id = this.readAttribute('id').replace('confirm', 'reject');
                $(id).checked = false;
            }
        });

        $$(".po_reject").invoke('observe', 'click', function() {
            if (this.checked) {
                id = this.readAttribute('id').replace('reject', 'confirm');
                $(id).checked = false;
            }
        });

        if ($('purchase_order_confirmreject_save')) {
            $('purchase_order_confirmreject_save').observe('click', function() {
                $('purchase_order_confirmreject').submit();
            });
        }

        // Orders Details Page

        $$(".purchase_order_changed").invoke('observe', 'click', function() {
            var disabled = true;
            if (this.checked) {
                disabled = false;
            }

            this.parentNode.parentNode.select('input[type=text]', 'textarea').each(function(el) {
                el.disabled = disabled;
            });
        });

        window.validateSupplierForm = function (form, type, attachmentLine) {
            valid = true;

            var errorMessage = '';

            var valid = form.validation() && form.validation('isValid');

            if (!valid) {
                errorMessage += jQuery.mage.__('One or more options is incorrect, please see page for details') + '\n';
            }

            //header level attachment
            const attachmentHeaderTableTrace = 'supplier_' + type + '_attachments_table tbody tr.attachments_row';
            const validateAttachmentUpload = attachmentUploaded(attachmentHeaderTableTrace);
            if (!validateAttachmentUpload) {
                errorMessage += jQuery.mage.__('One or more header level attachment is not uploaded. Please upload the attachment!') + '\n';
            }

            // line level attachment validation
            if (typeof attachmentLine != undefined) {
                const attachmentLineLevelTableTrace = 'supplierconnect_' + type + '_lines_table tbody tr.attachment table.data-grid tbody tr.line_attachment_row';
                const validateLineAttachmentUpload = attachmentUploaded(attachmentLineLevelTableTrace);
                if (!validateLineAttachmentUpload) {
                    errorMessage += jQuery.mage.__('One or more line level attachment is not uploaded. Please upload the attachment!') + '\n';
                }
            }

            if (errorMessage != '') {
                jQuery('body').loader('hide');
                valid = false;
                alert(errorMessage);
            }

            return valid;
        }
    });
});
