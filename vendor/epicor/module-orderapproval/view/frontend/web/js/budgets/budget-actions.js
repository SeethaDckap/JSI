/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    "jquery",
    'Epicor_OrderApproval/js/budgets/messages',
    'jquery/ui',
    'mage/mage',
    'mage/validation'
], function ($, messages) {
    return {
        saveBudgetForm: function (gridUrl) {
            let budgetForm = $('#add_budget_form');
            budgetForm.validation();
            var self = this;
            if (budgetForm.validation('isValid')) {
                $('body').trigger('processStart');
                $.ajax({
                    url: budgetForm.attr('action'),
                    data: budgetForm.serialize(),
                    type: "POST",
                    dataType: 'html',
                }).done(function (data) {
                    let responseData = JSON.parse(data);
                    $('body').trigger('processStop');
                    if(responseData.hasOwnProperty('type') && responseData.hasOwnProperty('message')){
                        messages.insertMessage(responseData.message, responseData.type);
                    }
                    self.clearForm();
                    self.loadBudgetGrid(gridUrl);
                });
            }
        },
        loadBudgetForm: function (formLoadUrl, formKey, budgetId='', isEdit = false) {
            messages.clearMessages();
            var formData = {};
            let groupId = $('div#budget-remaining-count').attr('data-group-id')
            if(isEdit){
                formData.is_edit = 1;
            }
            if(groupId){
                formData.group_id = groupId;
            }
            if(budgetId){
                formData.form_key = formKey;
                formData.budget_id = budgetId;
            }else{
                formData.form_key = formKey;
            }
            $('body').trigger('processStart');
            self = this;
            $.ajax({
                url: formLoadUrl,
                data: formData,
                type: "POST",
                dataType: 'html',
            }).done(function (data) {
                $('body').trigger('processStop');
                if (data.indexOf('limit-exceeded') !== -1) {
                    self.clearForm();
                    messages.insertMessage('A maximum of 4 Budget types can be added', 'warning-msg');
                } else {
                    $('div#budget-add-form-container').html(data);
                }
            });
        },
        clearForm: function () {
            let formContainer = $('div#budget-add-form-container');
            formContainer.html('');
        },
        clearGrid: function () {
            let gridContainer = $('div#erpaccount_budget');
            gridContainer.remove();
        },
        loadBudgetGrid: function (url, responseMessage={}) {
            self = this;
            $('body').trigger('processStart');
            $.ajax({
                url: url,
                type: "GET",
                dataType: 'html',
            }).done(function (data) {
                if(responseMessage.hasOwnProperty('type') && responseMessage.hasOwnProperty('message')){
                    messages.insertMessage(responseMessage.message, responseMessage.type);
                }
                $('body').trigger('processStop');
                self.clearGrid();
                let addButton = $('#add-budget-button');
                addButton.after(data);
            });
        },
        remainingBudgetCount: function () {
            let addButton = $('#budget-remaining-count');
            if (addButton.length) {
                let remaining = addButton.attr('data-budget-remaining');
                return parseInt(remaining);
            }
            return 0;
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
                if (resultData.hasOwnProperty('type') && resultData.hasOwnProperty('message') ) {
                    messageData.message = resultData.message;
                    messageData.type = resultData.type;
                }

                self.loadBudgetGrid(gridUrl, messageData)
            });
        },
    }
});