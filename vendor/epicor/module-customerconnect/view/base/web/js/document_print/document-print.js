define([
    'jquery',
    'Epicor_Customerconnect/js/document_print/send-ajax'
], function ($, transport) {
    'use strict';

    function main() {
        $('.link-print').on('click', function () {
            transport.setPrintParams($(this));
            transport.sendPreqAjax();
            return false;
        });
    }

    return main;
});
