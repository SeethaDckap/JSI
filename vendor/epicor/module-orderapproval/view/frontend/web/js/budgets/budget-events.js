/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define([
    'jquery',
    'Epicor_OrderApproval/js/budgets/budget-actions',
    'Epicor_OrderApproval/js/budgets/messages',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'mage/mage',
    'mage/validation'
], function ($, budgetActions, messages, alert) {
    return function (config) {
        $(document).on('click', 'input#add_budget_action.form-button.primary', function () {
            let budgetForm = $('form#add_budget_form');
            let gridUrl = config.gridUrl;
            budgetForm.mage('validation', {})
            budgetActions.saveBudgetForm(gridUrl);
        });
        $(document).on('click', 'button#add-budget-button', function () {
            let gridUrl = config.gridUrl;
            budgetActions.loadBudgetGrid(gridUrl);
            budgetActions.loadBudgetForm(config.formUrl, config.formKey);

        });
        $(document).on('click','#erpaccount_budget_table a.edit-budget', function (e) {
            let budgetId = budgetActions.getSelectedBudgetId(e);
            let gridUrl = config.gridUrl;
            budgetActions.loadBudgetForm(config.formUrl, config.formKey, budgetId, true);
            budgetActions.loadBudgetGrid(gridUrl);
        });
        $(document).on('click','#erpaccount_budget_table a.delete-budget', function (e) {
            let budgetId = budgetActions.getSelectedBudgetId(e);
            let deleteAction = budgetActions.getSelectedAction(e);
            let deleteUrl = deleteAction.attr('data-url');
            alert({
                title: $.mage.__('Budget Delete'),
                content: $.mage.__('Are you sure you want to delete this Budget? This cannot be undone'),
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
                        this.closeModal(true);
                        budgetActions.deleteBudget(deleteUrl, config.gridUrl, budgetId)
                    }
                }]
            });

        });
    }});