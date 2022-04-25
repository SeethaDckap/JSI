/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */
define([
    'jquery',
    'Epicor_Customerconnect/js/document_print/send-ajax'
], function ($, transport) {
    'use strict';
    $(document).on('click', '.preq-wait .action-close', function () {
        $.cookie('freq_process_cancel', true, {expires: 365, path: '/'});
    });
    $(document).on('click', '.link-download-attachment', function () {
        transport.sendFreqAjax($(this));
    });
});