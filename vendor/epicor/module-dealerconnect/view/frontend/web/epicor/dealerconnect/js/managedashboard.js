/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery',
    'mage/url',
    'Magento_Ui/js/modal/modal',
    'domReady!', // wait for dom ready
    'jquery/ui'
], function ($,url) {
    'use strict';

    $.widget('dealer.managedashboard', {
        options: {
            modalButton: '[data-role=open-dashboard-popup]',
            modalDashboardForm: '#modal-dashboard-form',
            modalReminderButton: '[data-role=open-reminder-popup]',
            modalReminderForm: '#modal-reminder-form',
            modalSupplieReminderOrdersForm: '#modal-reminderorders-form',
            accordionsupplier: '.accordiondashboard',
            title: 'Manage Dashboard',
            event: 'click',
            managepopurlpath: undefined,
            refreshClaimUrl: 'dealerconnect/dashboard/refreshclaimdata',
            saveClaimUrl: 'dealerconnect/dashboard/saveclaimdata',
            action: undefined
        },

        /**
         * This method binds elements found in this widget.
         * @private
         */
        _bind: function () {
            var handlers = {};

            handlers[this.options.event] = this.options.action;
            this._on(handlers);
        },

        /**
         * This method constructs a new widget.
         * @private
         */
        _create: function () {
            this._bind();
        },

        /**
         * This method set the url for the redirect.
         * @private
         */
        _getModalOptions: function(title) {
            /**
             * Modal options
             */
            var self=this;
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll:true,
                title:title,
                modalClass: 'managedashboard'
            };

            return options;
        },
        managedashboardpopup: function () {
            var modalDashboardOption = this._getModalOptions(this.options.title);
            var modalDashboardForm = this.options.modalDashboardForm;
            var accordionsupplier = this.options.accordionsupplier;
            if($('#supplier-scripts').length) {
                $('#supplier-scripts').remove();
            }
            if ($('#modal-dashboard-data').length) {
                $('#modal-dashboard-data').remove();
            }
            $.ajax({
                showLoader: true,
                data: {
                    searchpopup: true
                },
                url: url.build(this.options.managepopurlpath),
                type: "POST",
                //dataType:'json',
            }).done(function(data) {
                $(modalDashboardForm).append("<div id='modal-dashboard-data'></div>");
                $(modalDashboardForm).modal(modalDashboardOption);
                $(modalDashboardForm).trigger('openModal');
                var html = $.parseHTML( data, document, true);
                $('#modal-dashboard-data').append(html);
                $('.modal-footer').hide();
            });
        },
        test1: function () {
            alert('test1');
        },
        saveClaim: function(claimData) {
            $.ajax({
                showLoader: false,
                data: claimData,
                url: url.build(this.options.saveClaimUrl),
                type: "POST",
            }).done(function(data) {
                $(".claim-updating").hide();
                location.reload(true);
            });
        },
        refreshClaim: function() {
            $(".claim-refresher").show();
            var self = this;
            var refreshCall = $.ajax({
                showLoader: false,
                url: url.build(this.options.refreshClaimUrl),
                type: "POST",
            }).done(function(data) {
                $(".claim-refresher").hide();
                $(".claim-updating").show();
                self.saveClaim(data);
            });
            $('.claim-section').data('ajaxcall', refreshCall);
        },
        cancelClaimRefresh: function() {
            var ajaxcall = $('.claim-section').data('ajaxcall');
            ajaxcall.abort();
            $(".claim-refresher").hide();
        }
    });
    return $.dealer.managedashboard;
});
