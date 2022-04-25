/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    "jquery",
    'Epicor_OrderApproval/js/budgets/erpaccounts/budget-form',
    'Epicor_OrderApproval/js/budgets/erpaccounts/grid-utilities',
    'Magento_Ui/js/modal/alert'
], function ($, form, grid, alert) {
    return function (config) {
        $(document).on('click','#erpaccount_budget_table tr td.a-left', function (e) {
            let budgetId = grid.getSelectedBudgetId(e);
            form.loadBudgetForm(config.formUrl, config.formKey, budgetId);
        });
        $(document).on('click','#erpaccount_budget_table a.edit-budget', function (e) {
            let budgetId = grid.getSelectedBudgetId(e);
            form.loadBudgetForm(config.formUrl, config.formKey, budgetId);
        });
        $(document).on('click','#erpaccount_budget_table a.delete-budget', function (e) {
            let budgetId = grid.getSelectedBudgetId(e);
            let deleteAction = grid.getSelectedAction(e);
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
                        grid.deleteBudget(deleteUrl, config.gridUrl, budgetId)
                    }
                }]
            });

        });
    };
});