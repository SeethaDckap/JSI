/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery',
    'Magento_Ui/js/form/element/abstract',
    'uiRegistry'
], function ($, input, registry) {
    'use strict';

    return input.extend({
        /**
         * Callback that fires when 'value' property is updated.
         */
        onUpdate: function () {
            var startDate, budgetType;
            var duration = this.value();
            startDate = registry.get(this.parentName + '.start_date').value();
            budgetType = registry.get(this.parentName + '.type').value();
            var endDate = registry.get(this.parentName + '.end_date');

            if (!startDate || !duration) {
                endDate.value("");
                return;
            }

            var sDate = new Date(startDate);
            switch (budgetType) {
                case "Daily":
                    sDate.setDate(sDate.getDate() + parseInt(duration));
                    break;
                case "Monthly":
                    sDate.setMonth(sDate.getMonth() + parseInt(duration));
                    break;
                case "Quarterly":
                    sDate.setMonth(sDate.getMonth() + (parseInt(duration) * 3));
                    break;
                case "Yearly":
                    sDate.setFullYear(sDate.getFullYear() + parseInt(duration));
                    break;
            }

            var day = sDate.getDate();
            var month = sDate.getMonth() + 1;
            var year = sDate.getFullYear();

            endDate.value(('0' + month).slice(-2) + '/' + ('0' + day).slice(-2) + '/' + year);
            this._super();
        },
    });
});
