/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

define([
    'underscore',
    'jquery'
], function (_, $) {
    'use strict';
    return function(config) {
        let assignProductsSelector = 'input[name="assign-products"]';
        $(assignProductsSelector).val(JSON.stringify(config.assignedProducts));

        $(document).on('click', 'table#list_products_table tr', function (e) {
            let targetRow = e.target;
            let rowSelector = targetRow.closest('tr');
            let currentRow = $(rowSelector).find('td input.admin__control-checkbox');
            let isCurrentRowChecked = currentRow.prop('checked');
            let positionInput = $(rowSelector).find('td.col-qty input');
            if (!isCurrentRowChecked) {
                positionInput.prop('disabled', true);
            } else {
                positionInput.prop('disabled', false);
            }
        });
    };
});
