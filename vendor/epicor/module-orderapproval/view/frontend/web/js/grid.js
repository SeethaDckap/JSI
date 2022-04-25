/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define([
    'jquery',
    'Magento_Ui/js/modal/alert'
], function ($, alert) {
    'use strict';
    $(document).on('click', '.epicor_orderapproval-manage-index td.col-action a[type="delete"]', function (e) {
        e.preventDefault();
        let item = e.target;
        let selector = $(item);
        let href = selector.attr('href');
        alert({
            title: $.mage.__('Group Delete'),
            content: $.mage.__('Are you sure you want to delete this Group? This cannot be undone'),
            actions: {
                always: function() {
                }
            },
            buttons: [{
                text: $.mage.__('Delete'),
                class: 'action primary accept',

                /**
                 * Click handler.
                 */
                click: function () {
                    window.location.href = href;
                    this.closeModal(true);
                }
            }]
        });
    });
});

