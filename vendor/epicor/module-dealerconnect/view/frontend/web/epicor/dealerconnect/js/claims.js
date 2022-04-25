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
//var rfqHasChanges = false;

require([
    'jquery',
    'ko',
    'Magento_Ui/js/modal/modal',
    'mage/url',
    'prototype',
    'mage/validation',
    'mage/calendar',
    'Epicor_Common/epicor/common/js/attachment-validations'
], function (jQuery, ko, modal, url) {

    var options = {
        type: 'popup',
        responsive: true,
        innerScroll: true,
        buttons: [{
            text: jQuery.mage.__('Close'),
            class: 'primary action btn-default',
            click: function () {
                this.closeModal();
            }
        }]
    };

    window.dealerClaim = {
        newQuote: function () {
            if ($("claim_rfq_confirmreject_save")) {
                $("claim_rfq_confirmreject_save").hide();
            }


            var newQuoteUrl = jQuery('#claim_new_quote').val();
            jQuery.ajax({
                url: newQuoteUrl,
                type: "POST",
                showLoader: true
            }).done(function (data) {
                $('claim_quotes').hide();
                $('claim_quotes_form').innerHTML = data;
                $('claim_quotes_form').down('div.column.main').setStyle({
                    width: '100%',
                });
                $('quotes_block').show();
                jQuery("#required_date").calendar({
                    showsTime: false,
                    dateFormat: "M/d/yy",
                    buttonText: "Select Date",
                    changeMonth: true,
                    changeYear: true,
                    showOn: "both"
                });
            });
        },
        confirmQuote: function () {
            jQuery('body').loader('show');
            jQuery("#claim_update :input").removeAttr("disabled");
            claimQuoteConfirmReject('confirm');
        },
        rejectQuote: function () {
            jQuery('body').loader('show');
            jQuery("#claim_update :input").removeAttr("disabled");
            claimQuoteConfirmReject('reject');
        },
        duplicateQuote: function (url) {
            if ($("claim_rfq_confirmreject_save")) {
                $("claim_rfq_confirmreject_save").hide();
            }

            var duplicateQuote = url;
            jQuery.ajax({
                url: duplicateQuote,
                type: "POST",
                showLoader: true
            }).done(function (data) {
                $('claim_quotes').hide();
                $('claim_quotes_form').innerHTML = data;
                $('claim_quotes_form').down('div.column.main').setStyle({
                    width: '100%',
                });
                $('quotes_block').show();
                jQuery("#required_date").calendar({
                    showsTime: false,
                    dateFormat: "M/d/yy",
                    buttonText: "Select Date",
                    changeMonth: true,
                    changeYear: true,
                    showOn: "both"
                });
                jQuery("#claim_quotes_form").find('.lines_request_date').each(function () {
                    if (jQuery(this).prop('id') != '_request_date') {
                        jQuery(this).calendar();
                    }
                });
            });
        },
        editQuote: function (url) {
            if ($("claim_rfq_confirmreject_save")) {
                $("claim_rfq_confirmreject_save").hide();
            }

            var editQuoteUrl = url;
            jQuery.ajax({
                url: editQuoteUrl,
                type: "POST",
                showLoader: true
            }).done(function (data) {
                $('claim_quotes').hide();
                $('claim_quotes_form').innerHTML = data;
                $('claim_quotes_form').down('div.column.main').setStyle({
                    width: '100%',
                });
                $('quotes_block').show();
                jQuery("#required_date").calendar({
                    showsTime: false,
                    dateFormat: "M/d/yy",
                    buttonText: "Select Date",
                    changeMonth: true,
                    changeYear: true,
                    showOn: "both"
                });
                jQuery("#claim_quotes_form").find('.lines_request_date').each(function () {
                    if (jQuery(this).prop('id') != '_request_date') {
                        jQuery(this).calendar();
                    }
                });

                if (jQuery("#claim_isFormAccessAllowed").val() == 0) {
                    jQuery("#claim_quotes_form :input").attr("disabled", true);
                    jQuery('#claim_update').find('p.sorter a').css('pointer-events', '');
                    jQuery('#claim_quotes_form').find('a.attachment_view').css('pointer-events', '');

                    if (jQuery("#claim_rfq_duplicate") != undefined) {
                        jQuery("#claim_rfq_duplicate").removeAttr('disabled');
                    }
                    if (jQuery("#claim_rfq_confirm") != undefined) {
                        jQuery("#claim_rfq_confirm").removeAttr('disabled');
                    }
                    if (jQuery("#claim_rfq_reject") != undefined) {
                        jQuery("#claim_rfq_reject").removeAttr('disabled');
                    }
                }
            });
        },
        closeQuote: function () {
            $('quotes_block').hide();
            $('claim_quotes_form').innerHTML = "";
            $('claim_quotes').show();
            if ($("claim_rfq_confirmreject_save")) {
                $("claim_rfq_confirmreject_save").show();
            }

        },
        searchClaim: function () {
            if (!jQuery('#find_inventory_claim').valid()) {
                alert('Please enter valid search criteria');
                return false;
            }
            var request_url = url.build('dealerconnect/claims/findclaiminventory');

            jQuery.ajax({
                url: request_url,
                type: "POST",
                showLoader: true,
                data: {
                    claimby: jQuery('#findclaim_byoption').val(),
                    claim_number: jQuery('#claim_number').val()

                },
            }).done(function (data) {
                if (data.hasOwnProperty("type")) {
                    if (data.type === 'error') {
                        alert(data.message);
                    } else if (data.type === 'success') {
                        var modeldata = data.model;
                        jQuery("#sp_serial_num").html(modeldata.serialNumber);
                        jQuery("#sp_identity_num").html(modeldata.identificationNumber);
                        jQuery("#input_location_num").val(modeldata.locationNumber);
                        jQuery("#sp_prod_code").html(modeldata.productCode);
                        var bom_url = url.build('dealerconnect/claims/billOfMaterials/location/') + modeldata.locationNumber;
                        jQuery("#bom_url").val(bom_url);
                        jQuery("#product_code").show();
                        jQuery(".dealerconnect-claims-new #claim_update").show();
                    }
                } else {
                    $('showclaimresult').innerHTML = data;
                    var popup = modal(options, jQuery('#showclaimresult'));
                    jQuery('#showclaimresult').modal('openModal');
                }
            });
        },
        claimDetail: function (ele) {
            var getclaimdetailUrl = ele.getAttribute('href');
            jQuery('#showclaimresult').modal('closeModal');
            jQuery.ajax({
                url: getclaimdetailUrl,
                type: "GET",
                showLoader: true
            }).done(function (data) {
                var type = data.type;
                if (type != null && type === 'success') {
                    var modeldata = data.model;
                    jQuery("#sp_serial_num").html(modeldata.serialNumber);
                    jQuery("#sp_identity_num").html(modeldata.identificationNumber);
                    jQuery("#input_location_num").val(modeldata.locationNumber);
                    jQuery("#sp_prod_code").html(modeldata.productCode);
                    var bom_url = url.build('dealerconnect/claims/billOfMaterials/location/') + modeldata.locationNumber;
                    jQuery("#bom_url").val(bom_url);
                    jQuery("#product_code").show();
                    jQuery(".dealerconnect-claims-new #claim_update").show();
                } else {
                    if (data.message != null) {
                        alert(data.message);
                    } else {
                        alert('No Record found');
                    }
                }
            });
        },
        getBom: function (bomurl) {
            jQuery.ajax({
                url: bomurl,
                type: "GET",
                showLoader: true
            }).done(function (data) {
                $('claim_bom').update();
                $('claim_bom').update(data);
                $('claim_bom').innerHTML;
                jQuery('.loading-mask').hide();
            });
        },
        bomModal: function (bomurl) {
            jQuery.ajax({
                url: bomurl,
                type: "GET",
                showLoader: true
            }).done(function (data) {
                $('claim_bom').update(data);
                $('claim_bom').innerHTML;
                jQuery('.loading-mask').hide();
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    buttons: []
                };
                modal(options, jQuery('#claim_bom'));
                jQuery('#claim_bom').modal("openModal");
            });
        },
        addToQuote: function () {
            var lines = new Epicor_Lines.lines();
            var skudata = [];
            var customdata = [];
            var showConfiguratorMessage = false;
            var errors = [];
            var self = this;

            $$('.bom_row').each(function (el) {
                var elchecked = el.select('.la_row_checked').shift().checked;
                if (elchecked) {
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

                        skudata[skudata.length] = {
                            'sku': sku,
                            'sendSku': sendSku,
                            'uom': uom,
                            'qty': qty,
                            'productid': productid
                        }
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

            var postData = {
                'from': 'rfq',
                'sku[]': skuArr,
                'qty[]': qtyArr,
                'id[]': idArr,
                'currency_code': $('quote_currency_code').value,
                'use_index': 'row_id'
            }

            //$('loading-mask').show();
            common.performAjax(url, 'post', postData, function (data) {
                var msqData = data.responseText.evalJSON();

                if (msqData['has_errors']) {
                    message = jQuery.mage.__('One or more lines had errors:') + '\n\n';
                    for (index = 0; index < skudata.length; index++) {
                        sku = skudata[index].sendSku;
                        qty = skudata[index].qty;
                        pData = msqData[index];//msqData[sku];

                        pData.sku = self.getNiceSkuwithUom(pData, skudata[index].sendSku);
                        if (pData.error == 1) {
                            message += jQuery.mage.__('SKU') + ' ' + pData.sku + ' ';
                            if ($$('.la_custompart').length > 0) {
                                message += jQuery.mage.__('Does not exist - Select Custom Part') + '\n';
                            } else {
                                message += jQuery.mage.__('Does not exist') + '\n';
                            }
                        }
                        if (pData.status_error == 1) {
                            message += jQuery.mage.__('SKU') + ' ' + pData.sku + ' ' + jQuery.mage.__('Not currently available') + '\n';
                        }
                        jQuery('.loading-mask').hide();
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

                if (showConfiguratorMessage) {
                    message += '\n\n' + jQuery.mage.__('One or more products require configuration. Please click on each "Configure" link in the lines list');
                    //alert(message);
                }
                jQuery('.loading-mask').hide();
                jQuery('#claim_bom').modal("closeModal");
                rfqHasChanged();
            });

            event.stop();
        },
        getNiceSkuwithUom: function (pData, defaultValue) {
            if (pData.sku) {
                if (pData.sku.search(escapeRegExp($('la_separator').value)) != -1) {
                    pData.sku = pData.sku.replace($('la_separator').value + pData.uom, '');
                }
            } else {
                if (defaultValue.search(escapeRegExp($('la_separator').value)) != -1) {
                    var laseparator = $('la_separator').value;
                    var i = defaultValue.indexOf(laseparator);
                    defaultValue = defaultValue.substring(0, i);
                }
                pData.sku = defaultValue;
            }
            return pData.sku;
        }
    };
    jQuery(function () {
        if ($('loading-mask')) {
            $('loading-mask').hide();
        }

        Event.live('#claim_save', 'click', function (el, event) {
            $('loading-mask').show();

            if (!validateClaimForm()) {
                $('loading-mask').hide();
                event.stop();
            } else {
                $('claim_update').submit();
            }
        });
        Event.live('#add_claim_attachment', 'click', function (el, event) {
            addClaimAttachmentRow();
            event.stop();
        });

        Event.live('.claimattachments_delete', 'click', function (el) {
            if (deleteWarning(el)) {
                hideButtons();
                common.deleteElement(el, 'claim_attachments');
                common.checkCount('claim_attachments', 'attachments_row', 3);
            }
        });

        Event.live('#bom', 'click', function (el) {
            var bomUrl = jQuery('#bom_url').val();
            dealerClaim.bomModal(bomUrl);
        });

        Event.live('#claim_bom .boxed-content a', 'click', function (el, event) {
            var bomUrl = el.href;
            dealerClaim.getBom(bomUrl);
            event.stop();
        });

        Event.live('#addtoquote_btn', 'click', function (el, event) {
            dealerClaim.addToQuote();
        });

    });

});

function validateClaimForm() {

    valid = true;
    errorMessage = '';

    var claimform = jQuery('#claim_update');
    var valid = claimform.validation() && claimform.validation('isValid');

    if (!valid) {
        errorMessage += jQuery.mage.__('One or more options is incorrect, please see page for details') + '\n';
    }
    /**BOF JIRA 8401**/
    // claim_attachments_table = header level
    const attachmentHeaderTableTrace = 'claim_attachments_table tbody tr.attachments_row';
    const validateAttachmentUpload = attachmentUploaded(attachmentHeaderTableTrace);
    if (!validateAttachmentUpload) {
        errorMessage += jQuery.mage.__('One or more claim attachment is not uploaded.Please upload the attachment!') + '\n';
    }
    //rfq_attachments_table = claim quote header level attachments
    const attachmentClaimQuoteHeaderTable = 'rfq_attachments_table tbody tr.attachments_row';
    let validateHeaderAttachmentUpload = attachmentUploaded(attachmentClaimQuoteHeaderTable);
    if (!validateHeaderAttachmentUpload) {
        errorMessage += jQuery.mage.__('One or more header level attachment of quote is not uploaded.Please upload the attachment!') + '\n';
    }
    //rfq_lines_table=claim quote line level attachments
    const attachmentClaimQuoteLineLevel = 'rfq_lines_table tbody tr.attachment table.data-grid tbody tr.line_attachment_row';
    const validateLineLevelAttachmentUpload = attachmentUploaded(attachmentClaimQuoteLineLevel);
    if (!validateLineLevelAttachmentUpload) {
        errorMessage += jQuery.mage.__('One or more line level attachment of quote is not uploaded.Please upload the attachment!') + '\n';
    }
    /**EOF JIRA 8401**/
    if ($('claim_new')) {
        var comment = jQuery("#claim_comment").val();
        if (comment == "") {
            errorMessage += jQuery.mage.__("Please enter Comment.") + '\n';
        }

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

    if (errorMessage != '') {
        valid = false;
        alert(errorMessage);
    }

    return valid;
}

function escapeRegExp(str) {
    return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
}

function showMessage(txt, type) {

    var html = '<ul class="messages"><li class="' + type + '-msg"><ul><li>' + txt + '</li></ul></li></ul>';
    $('messages').update(html);
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

function addClaimAttachmentRow() {
    $$('#claim_attachments_table tbody tr:not(.attachments_row)').each(function (e) {
        e.remove();
    });

    var row = $('claim_attachments_row_template').clone(true);
    row.addClassName('new');
    row.setAttribute('id', 'attachments_' + attachments_count);
    row = common.resetInputs(row);

    row.down('.claimattachments_delete').writeAttribute('name', 'claimattachmentss[new][' + attachments_count + '][delete]');
    row.down('.claimattachments_description').writeAttribute('name', 'claimattachments[new][' + attachments_count + '][description]');
    row.down('.claimattachments_filename').writeAttribute('name', 'claimattachments[new][' + attachments_count + '][filename]');

    $('claim_attachments').down('tbody').insert({bottom: row});
    common.colorRows('claim_attachments', '');
    attachments_count += 1;
}

function claimQuoteConfirmReject(action) {
    var url = $(action + '_url').value;
    var form_data = $('claim_update').serialize(true);

    common.performAjax(url, 'post', form_data, function (data) {
        var json = data.responseText.evalJSON();
        if (json.type == 'success') {
            if (json.redirect) {
                window.location.reload();
            }
        } else {
            jQuery('body').loader('hide');
            if (json.message) {
                alert(json.message);
            }
        }
    });

}


