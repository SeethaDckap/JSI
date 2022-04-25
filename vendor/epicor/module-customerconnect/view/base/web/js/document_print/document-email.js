require([
        'jquery',
        'Epicor_Customerconnect/js/document_print/send-ajax',
        'Epicor_Customerconnect/js/document_print/email-popup'
    ], function ($, transport, popup) {
        'use strict';

        $(document).on('click', '.link-email', function () {
            transport.setPrintParams($(this));
            popup.showPopup($(this).attr("data-entity-document"));
        });

        $(document).on('click', '.preq-wait .action-close', function () {
            $.cookie('preq_process_cancel', true, {expires: 365, path: '/'});
        });

        $(document).on('click', 'button#email-doc-popup-submit', function (e) {
            e.preventDefault();
            transport.setEmailParams();
            if (transport.validateEmailForm()) {
                popup.closePopup();
                transport.sendPreqAsync();
            }
        });
    }
);
