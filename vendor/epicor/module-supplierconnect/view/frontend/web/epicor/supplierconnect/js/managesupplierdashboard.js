require([
        'jquery',
        'mage/url',
        'Magento_Ui/js/modal/modal',
        'domReady!'// wait for dom ready
    ],
    function($,url) {
        "use strict";
        //creating jquery widget
        window.supplierJs = {
            options: {
                modalButton: '[data-role=open-supplier-dashboard-popup]',
                modalSupplierDashboardForm: '#modal-supplier-dashboard-form',
                modalReminderButton: '[data-role=open-supplier-reminderrfqs-popup]',
                modalSupplieReminderRfqsForm: '#modal-supplier-reminderrfqs-form',
                modalSupplieReminderOrdersForm: '#modal-supplier-reminderorders-form',
                accordionsupplier: '.accordionsupplier'
            },
            _getSupplierModalOptions: function() {
                /**
                 * Modal options
                 */
                var self=this;
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll:true,
                    title: 'Manage Dashboard',
                    modalClass: 'suppliermanagedashboard',
                    buttons: [{
                        text: 'Save',
                        class: 'action save primary',
                        click: function () {
                            var $form = $("#supplier-form-validate");
                            var dataVals = self.getFormData($form);
                            $.ajax({
                                showLoader: true,
                                data: dataVals,
                                url: url.build('supplierconnect/dashboard/managesave'),
                                type: "POST",
                                //dataType:'json',
                            }).done(function(data) {
                                window.location.reload();
                            });
                        }
                    }]
                };

                return options;
            },
            _getSupplierRfqsModalOptions: function() {
                /**
                 * Modal options
                 */
                var self=this;
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll:true,
                    title: 'Manage Reminder notification',
                    modalClass: 'suppliermanagerfqsoption',
                    buttons: [{
                        text: 'Save',
                        class: 'action save primary',
                        click: function () {
                            var $form = $("#supplier-form-validate");
                            var dataVals = self.getFormData($form);
                            $.ajax({
                                showLoader: true,
                                data: dataVals,
                                url: url.build('supplierconnect/dashboard/managesave'),
                                type: "POST",
                                //dataType:'json',
                            }).done(function(data) {
                                window.location.reload();
                            });
                        }
                    }]
                };

                return options;
            },
            _getSupplierOrderModalOptions: function() {
                /**
                 * Modal options
                 */
                var self=this;
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll:true,
                    title: 'Manage Reminder notification',
                    modalClass: 'suppliermanageorderoption'
                };

                return options;
            },
            getFormData: function($form) {
                var unindexed_array = $form.serializeArray();
                return unindexed_array;
            },
            _bind: function(){
                var modalSupplierDashboardOption = supplierJs._getSupplierModalOptions();
                var modalSupplierDashboardForm = supplierJs.options.modalSupplierDashboardForm;
                var accordionsupplier = supplierJs.options.accordionsupplier;
                    if($('#supplier-scripts').length) {
                        $('#supplier-scripts').remove();
                    }
                    if ($('#modal-supplier-dashboard-data').length) {
                        $('#modal-supplier-dashboard-data').remove();
                    }
                    $.ajax({
                        showLoader: true,
                        data: {
                            searchpopup: true
                        },
                        url: url.build('supplierconnect/dashboard/manage'),
                        type: "POST",
                        //dataType:'json',
                    }).done(function(data) {
                        $("#modal-supplier-dashboard-form").append("<div id='modal-supplier-dashboard-data'></div>");
                        $(modalSupplierDashboardForm).modal(modalSupplierDashboardOption);
                        $(modalSupplierDashboardForm).trigger('openModal');
                        var html = $.parseHTML( data, document, true);
                        $('#modal-supplier-dashboard-data').append(html);
                        $('.modal-footer').hide();
                    });
            },
            _bindReminderRfq: function(){
                var modalSupplierRfqsOption = supplierJs._getSupplierRfqsModalOptions();
                var modalSupplieReminderRfqsForm = supplierJs.options.modalSupplieReminderRfqsForm;
                var accordionsupplier = supplierJs.options.accordionsupplier;
                if($('#supplier-scripts').length) {
                    $('#supplier-scripts').remove();
                }
                if ($('#modal-supplier-reminderrfqs-data').length) {
                    $('#modal-supplier-reminderrfqs-data').remove();
                }
                $.ajax({
                    showLoader: true,
                    data: {
                        searchpopup: true
                    },
                    url: url.build('supplierconnect/dashboard/reminder'),
                    type: "POST",
                    //dataType:'json',
                }).done(function(data) {
                    $("#modal-supplier-reminderrfqs-form").append("<div id='modal-supplier-reminderrfqs-data'></div>");
                    $(modalSupplieReminderRfqsForm).modal(modalSupplierRfqsOption);
                    $(modalSupplieReminderRfqsForm).trigger('openModal');
                    var html = $.parseHTML( data, document, true);
                    $('#modal-supplier-reminderrfqs-data').append(html);
                    $('.modal-footer').hide();
                });
            },
            _submitSave: function() {
                if ($('#supplier-rfqs-validate').valid()) {
                    var $form = $("#supplier-rfqs-validate");
                    var dataVals = $form.serializeArray();
                    $.ajax({
                        showLoader: true,
                        data: dataVals,
                        url: url.build('supplierconnect/dashboard/remindersave'),
                        type: "POST",
                    }).done(function (data) {
                        $(".action-close").click(); // Close pop modal
                        location.reload(true);
                    });
                }
            },
            _sendInstantEmail: function() {
                if ($('#supplier-rfqs-validate').valid()) {
                    var $form = $("#supplier-rfqs-validate");
                    var dataVals = $form.serializeArray();
                    $.ajax({
                        showLoader: true,
                        data: dataVals,
                        url: url.build('supplierconnect/dashboard/sendinstantemail'),
                        type: "POST",
                    }).done(function (data) {
                        alert(data);
                    });
                }
            }
        }
    }
);