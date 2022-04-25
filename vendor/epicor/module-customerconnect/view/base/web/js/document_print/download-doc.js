define([
    'jquery',
    'Epicor_Customerconnect/js/document_print/send-ajax'
], function ($, transport) {
    'use strict';
  $(document).on('click', '.preq-wait .action-close', function () {
        $.cookie('freq_process_cancel', true, {expires: 365, path: '/'});
    });

   $(document).on('click', '.link-download-doc', function () {
        transport.setDownloadDocumentParams($(this));
        transport.sendFreqAjax();
    });
});