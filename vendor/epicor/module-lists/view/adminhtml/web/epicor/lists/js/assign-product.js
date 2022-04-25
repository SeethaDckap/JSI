define([
    "jquery",
    'Epicor_Lists/epicor/lists/js/products-pricing-link'
], function ($, PricingLink) {
    'use strict';
    return function (config){
        let assignProductsSelector = 'input[name="assign-products"]';
        $(assignProductsSelector).val(JSON.stringify(config.assignedProducts));

        $('#productsGrid').bind('positionUpdate', function () {
            // Import product after update position
            let positions = $("td.ecc-list-position input");
            let assignedProducts = JSON.parse($(assignProductsSelector).val());
            $.each(positions, function (key, option) {
                let input = option;
                let targetValue = option.value;
                if (targetValue === 0 || targetValue === '') {
                    targetValue = null;
                }
                let entityId = $(input).attr('data-entity-id');
                if (assignedProducts.hasOwnProperty(entityId)) {
                    assignedProducts[entityId] = targetValue;
                }
            });
            $(assignProductsSelector).val(JSON.stringify(assignedProducts));
        });

        $(document).on('change', 'td.ecc-list-position ',function(e){
            let input = e.target;
            let targetValue = e.target.value;
            if(targetValue === 0 || targetValue === ''){
                targetValue = null;
            }
            let entityId = $(input).attr('data-entity-id');
            let assignedProducts = JSON.parse($(assignProductsSelector).val());
            if(assignedProducts.hasOwnProperty(entityId)){
                assignedProducts[entityId] = targetValue;
                $(assignProductsSelector).val(JSON.stringify(assignedProducts))
            }
        });

        $(document).on('click', 'table#productsGrid_table tr', function (e) {
            let targetRow = e.target;
            let rowSelector = targetRow.closest('tr');
            let currentRow = $(rowSelector).find('td input.admin__control-checkbox');
            let isCurrentRowChecked = currentRow.prop('checked');
            let positionInput = $(rowSelector).find('td.ecc-list-position input');
            if (!isCurrentRowChecked) {
                positionInput.prop('disabled', true);
            } else {
                positionInput.prop('disabled', false);
            }
        });

        $(document).on('change', 'table#productsGrid_table tr td.col-selected_products input', function (e) {
            let checkboxSelector = e.target;
            let checkbox = $(checkboxSelector);
            let isRowSelected = checkbox.is(':checked');
            let currentRow = checkbox.closest('tr');
            let nextRow = currentRow.next('tr');
            let nextRowIdName = nextRow.attr('id');
            let positionInput = $(currentRow).find('td.ecc-list-position input');
            if (!isRowSelected) {
                positionInput.prop('disabled', true);
            } else {
                positionInput.prop('disabled', false);
            }
            if (typeof nextRowIdName === 'string' && nextRowIdName.search('pricing_grid_row') !== -1 && !isRowSelected) {
                nextRow.hide();
            }
        });
    };


});