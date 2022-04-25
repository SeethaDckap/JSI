/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

define([
    'underscore',
    'jquery',
    'Epicor_OrderApproval/js/tab-data',
], function (_, $,data) {
    'use strict';
    return function(config) {
        let customerTab = $('#customers-tab');
        customerTab.on('click', function(){
            data.getTab('customer', config.tabUrlData.customer)
        });
        let rulesTab = $('#rules-tab');
        rulesTab.on('click', function(){
            data.getTab('rules', config.tabUrlData.rules)
        });
        let budgetTab = $('#budgets-tab');
        budgetTab.on('click', function(){
            data.getTab('budgets', config.tabUrlData.budgets)
            $('#budget_config_form').show();
        });
        let hierarchyTab = $('#hierarchy-tab');
        hierarchyTab.on('click', function(){
            data.getTab('hierarchy', config.tabUrlData.hierarchy)
        });
        let primaryTab = $('#primary_details');
        primaryTab.on('click', function(){
            data.getTab('primary')
        });
    };
});
