/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    "jquery",
    'Epicor_OrderApproval/js/budgets/erpaccounts/messages',
    'jquery/ui',
], function ($,messages) {
    return {
        loadBudgetGrid: function (url, responseMessage={}) {
            $('body').trigger('processStart');
            var self = this;
            $.ajax({
                url: url,
                type: "GET",
                dataType: 'html',
            }).done(function (data) {
                messages.clearMessages();
                $('body').trigger('processStop');
                let budgetGridContainer = $('#erpaccount_budget')
                let remaining = $('div#budget-remaining-count');
                remaining.remove();
                let formContainer = $('div#budget-add-form-container');
                formContainer.html('');
                budgetGridContainer.html(data);
                if (responseMessage.hasOwnProperty('message') && responseMessage.hasOwnProperty('type')) {
                    if (responseMessage.type === 'success') {
                        messages.insertMessage(responseMessage.message, 'success-msg');
                    }
                    if (responseMessage.type === 'error') {
                        messages.insertMessage(responseMessage.message, 'error-msg');
                    }
                }
                self.disableAddButton();
            });
        },
        disableAddButton: function() {
            let gridBodySelector = $('#erpaccount_budget_table tbody tr');
            if(gridBodySelector.length >= 4){
                $('button#add-budget-button').prop('disabled', true);
            }else{
                $('button#add-budget-button').prop('disabled', false);
            }
        },
        deleteBudget: function (url, gridUrl, budgetId) {
            var self = this;
            $('body').trigger('processStart');
            $.ajax({
                url: url,
                type: "GET",
                data: {budget_id:budgetId},
                dataType: 'html',
            }).done(function (data) {
                $('body').trigger('processStop');
                messages.clearMessages();
                let resultData = JSON.parse(data);

                let messageData = {};
                if (resultData.hasOwnProperty('success')) {
                    messageData.message = resultData.success;
                    messageData.type = 'success';
                }
                if (resultData.hasOwnProperty('error')) {
                    messageData.message = resultData.error;
                    messageData.type = 'error';
                }
                self.loadBudgetGrid(gridUrl, messageData)
            });
        },
        getSelectedBudgetId: function(e){
            let actionSelector = this.getSelectedAction(e)
            let currentRow = actionSelector.closest('tr');
            let idCol = currentRow.find('td.col-id');

            return parseInt(idCol.html());
        },
        getSelectedAction: function(e){
            let selectedAction = e.target;
            return $(selectedAction);
        },
        isEmpty: function(value){
            return value === '' || value == null || value.length === 0 || /^\s+$/.test(value)
        },
        remainingBudgetCount: function () {
            let addButton = $('#budget-remaining-count');
            if (addButton.length) {
                let remaining = addButton.attr('data-budget-remaining');
                return parseInt(remaining);
            }
            return 0;
        }
    }
});