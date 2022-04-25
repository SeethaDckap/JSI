define([
    'underscore',
    'jquery',
    'Epicor_Lists/epicor/lists/js/auto-number-position'
], function (_, $, position) {
    'use strict';
    return function () {
        $(document).on('click', '.auto-position-button', function (e) {
            if (!position.checkInputs()) {
                return false;
            }
            let mainTable = position.getMainTable();
            position.currentAutoValue = parseInt(position.getAutoStartNumber());
            mainTable.find('tr').each(function (index, e) {
                let selectedIndex = 0;
                let row = $(e);
                let selectedProducts = row.find('td.col-selected_products input[type=checkbox]');
                let isSelected = selectedProducts.prop('checked');
                if (isSelected) {
                    selectedIndex++;
                    position.setPositionValue(row, index, selectedIndex);
                }
            });
        });
    };
});
