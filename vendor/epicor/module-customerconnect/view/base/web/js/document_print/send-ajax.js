define([
    'jquery',
    'Epicor_Customerconnect/js/document_print/loader-popup',
], function ($, popup) {
    'use strict';

    return {
        transportParams: {},
        emailForm: $('form#email-doc-popup-form'),
        sendPreqAjax: function () {
            popup.showPopup();
            $.ajax({
                url: '/customerconnect/document/request',
                data: jQuery.param(this.transportParams),
                type: "POST",
                dataType: 'json',
                async: true,
                custom: this.transportParams,
            }).done(function (data) {
                popup.closePopup();
                if (data.type == "success") {
                    if (!$.cookie('preq_process_cancel')) {
                        var param = this.custom;
                        if (param.action == 'P') {
                            if (typeof data.print_doc !== 'undefined'
                                && typeof data.print_doc.url !== 'undefined'
                                && typeof data.print_doc.doc_type !== 'undefined'
                            ) {
                                let printUrl = '/' + data.print_doc.url + '?doc_type=' + data.print_doc.doc_type;
                                window.open(printUrl, '_blank');
                            }
                        }
                    } else {
                        $.cookie('preq_process_cancel', null, {path: '/'});
                        //do nothing
                    }
                }

            });
        },
        sendPreqAsync: function () {
            var callAjax = this;
            $.ajax({
                url: '/customerconnect/massactions/massemail',
                showLoader: true,
                data: jQuery.param(this.transportParams),
                type: "POST",
                dataType: 'json'
            }).done(function (data) {
                window.parent.showMessage(data.message, data.type);
                callAjax.processPreq(data.id);
            });
        },
        processPreq: function (id) {
            if (id) {
                $.ajax({
                    url: '/comm/message/preq',
                    showLoader: false,
                    data: {
                        id: id
                    },
                    type: "POST",
                    dataType: 'json',
                    async: true
                }).done(function (data) {
                    //do nothing
                });
            }
        },
        setPrintParams: function (item) {
            this.transportParams = {
                account_number: item.attr("data-account-number"),
                entity_document: item.attr("data-entity-document"),
                entity_key: item.attr("data-entity-key"),
                action: item.attr("data-action"),
                email_params: {}
            };

        },
        setEmailParams: function () {
            this.transportParams.email_params.to = this.emailForm.find('input#doc-print-email-to').val();
            this.transportParams.email_params.cc = this.emailForm.find('input#doc-print-email-cc').val();
            this.transportParams.email_params.bcc = this.emailForm.find('input#doc-print-email-bcc').val();
            this.transportParams.email_params.message = this.emailForm.find('textarea#doc-print-email-message').val();
            this.transportParams.email_params.subject = this.emailForm.find('input#doc-print-email-subject').val();
        },
        validateEmailForm: function () {
            return this.emailForm.validation('isValid');
        },
        sendFreqAjax: function (item) {
            popup.showPopup();
            const ajax = new XMLHttpRequest();
            const params = '?erp_file_id=' + item.attr("data-erp-file-id")
                //+ '&web_file_id=' + item.attr("data-web-file-id")
                + '&file_name=' + item.attr("data-file-name")
                + '&order_number=' + item.attr("data-order-number")
                + '&filename_to_download=' + item.attr("data-filename-to-download")
                + '&action=' + item.attr("data-action");
            const downloadDocUrl = '/rest/default/V1/customerconnect/downloadattachment/' + params;
            ajax.open("POST", downloadDocUrl, true);
            ajax.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            ajax.setRequestHeader("Content-Type", "application/json");
            ajax.responseType = 'arraybuffer';
            ajax.onreadystatechange = function (data) {
                if (ajax.readyState === 4 && ajax.status === 200) {
                    let a;
                    const contentDisposition = ajax.getResponseHeader('Content-Disposition');
                    if (contentDisposition !== null) {
                        const fileName = ajax.getResponseHeader('Content-Disposition').split("filename=")[1];
                        const dataType = ajax.getResponseHeader('Content-Type');
                        const blob = new Blob([ajax.response], {type: dataType});
                        a = document.createElement('a');
                        a.href = window.URL.createObjectURL(blob);
                        //filename from response
                        a.download = fileName;
                        a.style.display = 'none';
                        document.body.appendChild(a);
                        a.click();
                        window.parent.showMessage('Document Download processed successfully.', 'success');
                    } else {
                        const dataView = new DataView(ajax.response);
                        const decoder = new TextDecoder('utf8');
                        const response = JSON.parse(decoder.decode(dataView));
                        window.parent.showMessage(response.response_data[0], response.response_data[1]);
                    }
                    popup.closePopup();
                }
            };
            ajax.send();
        }
    }
});
