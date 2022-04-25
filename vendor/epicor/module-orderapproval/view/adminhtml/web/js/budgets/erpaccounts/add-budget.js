/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    "jquery",
    'Epicor_OrderApproval/js/budgets/erpaccounts/messages',
    'Epicor_OrderApproval/js/budgets/erpaccounts/grid-utilities',
    'Epicor_OrderApproval/js/budgets/erpaccounts/budget-form',
    'jquery/ui',
    'mage/mage',
    'mage/validation'
], function ($, messages, grid, form) {
    return function (config) {
        $(document).on('click','#add-budget-button', function (e) {
            if(grid.remainingBudgetCount() > 0){
                form.loadBudgetForm(config.formUrl, config.formKey);
            }else{
                messages.insertMessage('A maximum of 4 Budget types can be added', 'warning-msg');
            }
        });
        $(document).on('click', '#add_budget_action', function (e) {
            form.saveBudgetForm(config.gridUrl)
        });
        $(document).ready(function(){
            messages.insertMessage('Note: A maximum of 4 Budget types can be added', 'warning-msg');
        });
    };
});