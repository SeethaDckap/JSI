/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery',
    'Magento_Ui/js/form/form',
    'Magento_Ui/js/modal/prompt',
    'Magento_Ui/js/modal/alert'
], function ($, Form, prompt, alert) {
    'use strict';

    return Form.extend({
        setAdditionalData: function (data) {
            _.each(data, function (value, name) {
                this.source.set('data.' + name, value);
            }, this);

            //erp Account Data POST
            var erpAccounts = $("input[name='links[erpaccounts]']").val();
            var customers = $("input[name='links[customers]']").val();
            var hierarchyParent = $("input[name='hierarchy[parent]']:checked").val();
            var hierarchyChildren = $("input[name='hierarchy[children]']").val();

            if (erpAccounts !== undefined) {
                this.source.set('data.' + 'links[erpaccounts]', erpAccounts);
            }

            //Customer
            if (customers !== undefined) {
                this.source.set('data.' + 'links[customers]', customers);
            }

            //Rules Data POST
            var fieldSetName = 'group_rule_fieldset';
            var rules = $("#" + fieldSetName + " input, #" + fieldSetName + " select");
            var _this = this;
            if (rules.length) {
                rules.each(function (value, index) {
                    _this.source.set('data.' + index.name, index.value);
                });
            }

            //Hierarchy Parent
            if (hierarchyParent !== undefined) {
                this.source.set('data.' + 'hierarchy[parent]', hierarchyParent);
            }

            //Hierarchy children
            if (hierarchyChildren !== undefined) {
                this.source.set('data.' + 'hierarchy[children]', hierarchyChildren);
            }

            this.source.set('data.' + 'group[is_budget_active]', this.source.data.budget.is_budget_active);

            return this;
        },
    });
});
