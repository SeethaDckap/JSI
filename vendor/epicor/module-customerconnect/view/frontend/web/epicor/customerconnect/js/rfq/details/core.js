/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

var attachments_count = 0;
var line_attachment_count = 0;
var contact_count = 0;
var salesrep_count = 0;
var line_count = 0;
var lineadd_count = 1;
var laSearchForm = [];
var configurator_line = '';
var configure_id = '';
var optionsPrice;
var rfqHasChanges = false;

require([
    'jquery',
    'prototype',
    'Epicor_Common/epicor/common/js/capture-details',
    'Epicor_Common/epicor/common/js/attachment-validations',
    'mage/validation'
], function (jQuery) {
    jQuery(function () {

        //  jQuery('body').loader({});
        Event.live('#rfq_save', 'click', function (el, event) {
            //     jQuery('body').loader('show');
            //jQuery('body').trigger('processStart');
            jQuery('.modal-inner-wrap').remove();  // stop previous modal contents showing when loader appears
            if (!validateRfqForm()) {
                jQuery('body').loader('hide');
                //  event.stop();
            } else {
                jQuery('body').trigger('processStart');
                rfqSubmit();
                if (window.nonErpProductItems) {
                    if (jQuery('.nonerpconfirmation').length) {
                        jQuery('.nonerpconfirmation').remove();
                    }
                    moreInformationBox(window.msgText, 200, 150, 'confirm_html', true, 'quote');
                    return false;
                }
                var url = $('rfq_update').readAttribute('action');
                var rfqDatas = $('rfq_update').serialize();
                if ($('rfq_serialize_data').setValue(rfqDatas)) {
                    $$('#rfq_update input[type="file"]').each(function (elem) {
                        if (elem.name != '') {
                            $('rfq_serialize_data').insert({
                                after: elem.clone(true)
                            });
                        }

                    });
                    $('rfq_submit_wrapper_form').submit();
                }
            }
        })

        Event.live('#rfq_duplicate', 'click', function (el, event) {
            if (!rfqHasChanges || confirm(jQuery.mage.__('There are unsaved changes to this quote. These changes will be lost. Are you sure you wish to continue?'))) {
                //  $('loading-mask').show();
                jQuery('body').loader('show');
                //jQuery.trigger('processStart');
                window.location = $('duplicate_url').value;
            }
            event.stop();
        });

        Event.live('#rfq_confirm', 'click', function (el, event) {
            //   $('loading-mask').show();
            jQuery('body').loader('show');
            jQuery('#rfq_update').find(':input').removeAttr('disabled');
            submitConfirmReject('confirm');
            event.stop();
        });

        Event.live('#rfq_reject', 'click', function (el, event) {
            //    $('loading-mask').show();
            jQuery('body').loader('show');
            jQuery('#rfq_update').find(':input').removeAttr('disabled');
            submitConfirmReject('reject');
            event.stop();
        });

        Event.live('#rfq_checkout', 'click', function (el, event) {
            //     $('loading-mask').show();
            jQuery('body').loader('show');
            window.location.replace($('checkout_url').value);
            event.stop();
        });

        // if custom address reselected
        Event.live('#rfq_update input', 'keypress', function (el, event) {
            if (event.keyCode === 13) {
                event.preventDefault();
            }
        });

        Event.live('#rfq_update *', 'change', function (el, event) {
            if (!el.up('#line-add') && el.type != 'checkbox') {
                rfqHasChanged();
            }
        });

    });
});

function validateRfqForm() {

    valid = true;
    var errorMessage = '';

    var rfqform = jQuery('#rfq_update');
    var valid = rfqform.validation() && rfqform.validation('isValid');

    if (!valid) {
        errorMessage += jQuery.mage.__('One or more options is incorrect, please see page for details') + '\n';
    }
    /**BOF JIRA 8401**/
    //header level RFQ attachment
    const attachmentHeaderTableTrace = 'rfq_attachments_table tbody tr.attachments_row';
    const validateAttachmentUpload = attachmentUploaded(attachmentHeaderTableTrace);
    if (!validateAttachmentUpload) {
        errorMessage += jQuery.mage.__('One or more header level RFQ attachment is not uploaded. Please upload the attachment!') + '\n';
    }
    //rfq line level attachment validation
    const attachmentLineLevelTableTrace = 'rfq_lines_table tbody tr.attachment table.data-grid tbody tr.line_attachment_row';
    const validateLineAttachmentUpload = attachmentUploaded(attachmentLineLevelTableTrace);
    if (!validateLineAttachmentUpload) {
        errorMessage += jQuery.mage.__('One or more line level RFQ attachment is not uploaded. Please upload the attachment!') + '\n';
    }
    /**EOF JIRA 8401**/
    if (!isValidAttachments()) {
        valid = false;
        errorMessage += jQuery.mage.__('Error attachment exceeds ' + getAttachmentFileLimit() + ' characters') + '\n';
    }

    if (!isFileAttachmentValidFileSize()) {
        valid = false;
        errorMessage += jQuery.mage.__('Error attachment file is empty contains 0 bytes') + '\n';
    }

    if ($('rfq_new')) {
        var contacts = $$('#rfq_contacts_table tbody tr.contacts_row').findAll(function (el) {
            return el.visible();
        }).length;
        if (contacts === 0) {
            errorMessage += jQuery.mage.__('You must supply at least one Contact') + '\n';
        }
        var lines = $$('#rfq_lines_table tbody tr.lines_row').findAll(function (el) {
            return el.visible();
        }).length;
        if (lines === 0) {
            errorMessage += jQuery.mage.__('You must supply at least one Line') + '\n';
        }
    }

    var configuratorError = false;

    $$('.lines_configured').each(function (e) {
        if (e.value == 'TBC') {
            configuratorError = true;
        }
    });

    if (configuratorError) {
        errorMessage += jQuery.mage.__('One or more lines require configuration, please see lines with a "Configure" link') + '\n';
    }

    if (errorMessage != '') {
        jQuery('body').loader('hide');
        valid = false;
        alert(errorMessage);
    }

    return valid;
}

function isValidAttachments() {
    return isValidateAttachmentLines() && isValidAdditionalAttachments();
}

function isValidateAttachmentLines() {
    var attachmentLimit = getAttachmentFileLimit();
    if (!attachmentLimit) {
        return true;
    }
    var validAttachments = true;
    var lines = jQuery('table#rfq_lines_table tr.attachment');

    lines.each(function (i, e) {
        var linesAttachments = jQuery(this).find('tr.line_attachment_row');
        linesAttachments.each(function (a, b) {
            var attachmentData = jQuery(this).find('input[type="file"]');
            if (isFileNameSetOnInputField(attachmentData) && !isFileInputDisabled(attachmentData)) {
                var fileName = attachmentData[0]['files'][0]['name'];

                if (fileName.length > attachmentLimit) {
                    validAttachments = false;
                }
            }
        });


    });

    return validAttachments;
}

function isValidAdditionalAttachments() {
    var attachmentLimit = getAttachmentFileLimit();
    if (!attachmentLimit) {
        return true;
    }
    var validAttachments = true;
    var lines = jQuery('#rfq_attachments_table tr.attachments_row');
    lines.each(function (i, e) {
        var lineAttachments = jQuery(this).find('input[type="file"]');
        lineAttachments.each(function (a, b) {
            var attachmentData = jQuery(this);
            if (isFileNameSetOnInputField(attachmentData) && !isFileInputDisabled(attachmentData)) {
                var fileName = attachmentData[0]['files'][0]['name'];

                if (fileName.length > attachmentLimit) {
                    validAttachments = false;
                }
            }
        });
    });

    return validAttachments;
}

function isFileInputDisabled(attachmentData) {
    if (attachmentData) {
        return attachmentData[0] !== undefined
            && attachmentData[0]['disabled'] !== undefined
            && attachmentData[0]['disabled'] === true;
    }

    return false;
}

function getAttachmentFileLimit() {
    var formData = jQuery('#rfq_update').find('div.file-name-size');
    if (formData.attr('data-attachment') > 0) {
        return formData.attr('data-attachment');
    } else {
        return 0;
    }
}

function isFileAttachmentValidFileSize() {
    return isLineAttachmentsFileSizeValid() && isAdditionalAttachmentsFileSizeValid();
}

function isLineAttachmentsFileSizeValid() {
    var validAttachments = true;
    var lines = jQuery('table#rfq_lines_table tr.attachment');

    lines.each(function (i, e) {
        var linesAttachments = jQuery(this).find('tr.line_attachment_row');
        linesAttachments.each(function (a, b) {
            var attachmentData = jQuery(this).find('input[type="file"]');
            if (isFileNameSetOnInputField(attachmentData) && !isFileInputDisabled(attachmentData)) {
                var fileSize = attachmentData[0]['files'][0]['size'];

                if (fileSize < 1) {
                    validAttachments = false;
                }
            }
        });


    });

    return validAttachments;
}

function isAdditionalAttachmentsFileSizeValid() {
    var validAttachments = true;
    var lines = jQuery('#rfq_attachments_table tr.attachments_row');
    lines.each(function (i, e) {
        var lineAttachments = jQuery(this).find('input[type="file"]');
        lineAttachments.each(function (a, b) {
            var attachmentData = jQuery(this);
            if (isFileNameSetOnInputField(attachmentData) && !isFileInputDisabled(attachmentData)) {
                var fileSize = attachmentData[0]['files'][0]['size'];

                if (fileSize < 1) {
                    validAttachments = false;
                }
            }
        });
    });

    return validAttachments;
}

function isFileNameSetOnInputField(attachmentData) {
    if (attachmentData) {
        return attachmentData[0] !== undefined
            && attachmentData[0]['files'] !== undefined
            && attachmentData[0]['files'][0] !== undefined
            && attachmentData[0]['files'][0]['name'] !== undefined
    }
}

function submitConfirmReject(action) {
    var url = $(action + '_url').value;
    var form_data = $('rfq_update').serialize(true);

    common.performAjax(url, 'post', form_data, function (data) {
        var json = data.responseText.evalJSON();
        if (json.type == 'success') {
            if (json.redirect) {
                window.location.replace(json.redirect);
            }
        } else {
            //        $('loading-mask').hide();
            jQuery('body').loader('hide');
            if (json.message) {
                showMessage(json.message, json.type);
            }
        }
    });

}

function hideButtons() {
    if ($('rfq_confirm')) {
        $('rfq_confirm').hide();
    }
    if ($('rfq_reject')) {
        $('rfq_reject').hide();
    }
    if ($('rfq_checkout')) {
        $('rfq_checkout').hide();
    }
}

function escapeRegExp(str) {
    return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
}

function showMessage(txt, type, position) {

    var html = '<ul class="messages"><li class="' + type + '-msg"><ul><li>' + txt + '</li></ul></li></ul>';
    if (position == false) {
        $('messages').update(html);
    } else {
        $('messages').insert(html);
    }
}

function deleteWarning(el) {
    var allowDelete = true;
    if (confirm(jQuery.mage.__('Are you sure you want to delete selected line?')) === false) {
        allowDelete = false;
    }
    return allowDelete;
}

function valueEmpty(value) {

    if (value == '' || value == undefined || value == 0) {
        return true;
    }

    return false;
}

function rfqHasChanged() {
    rfqHasChanges = true;
}