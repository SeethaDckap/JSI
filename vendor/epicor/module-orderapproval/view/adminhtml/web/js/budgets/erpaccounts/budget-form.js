/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    "jquery",
    'Epicor_OrderApproval/js/budgets/erpaccounts/messages',
    'Epicor_OrderApproval/js/budgets/erpaccounts/grid-utilities',
    'jquery/ui',
    'mage/mage',
    'mage/validation'
], function ($, messages, grid) {
    return {
        loadBudgetForm: function (formLoadUrl, formKey, budgetId='') {
            messages.clearMessages();
            var formData = {};
            if(budgetId){
                formData.form_key = formKey;
                formData.budget_id = budgetId;
            }else{
                formData.form_key = formKey;
            }
            $('body').trigger('processStart');
            $.ajax({
                url: formLoadUrl,
                data: formData,
                type: "POST",
                dataType: 'html',
            }).done(function (data) {
                $('body').trigger('processStop');
                $('div#budget-add-form-container').html(data);
                //initialise form validation
                let budgetForm = $('#add_budget_form');
                budgetForm.mage('validation', {})
            });
        },
        saveBudgetForm: function (gridUrl) {
            let budgetForm = $('#add_budget_form');
            if (budgetForm.validation('isValid')) {
                $('body').trigger('processStart');
                $.ajax({
                    url: budgetForm.attr('action'),
                    data: budgetForm.serialize(),
                    type: "POST",
                    dataType: 'html',
                }).done(function (data) {
                    messages.clearMessages();
                    $('body').trigger('processStop');
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
                    grid.loadBudgetGrid(gridUrl, messageData);
                });
            }
        }
    }
});