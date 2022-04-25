/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

require([
    'jquery',
    'prototype',
    'mage/translate'
], function (jQuery) {
    jQuery(function () {

        // Sales Reps

        salesrep_count = $$('#rfq_salesreps_table tbody tr.salesrep_row').length;

        Event.live('.salesreps_delete', 'click', function (el) {
            if (el.checked && confirmSalesRepDelete(el)) {
                el.up('tr').hide();
                hideButtons();
                common.deleteElement(el, 'rfq_salesreps');
                common.colorRows('rfq_salesreps','');
                common.checkCount('rfq_salesreps', 'salesreps_row', 3);
                rfqHasChanged();
            } else {
                el.checked = false;
            }
        });

        Event.live('#add_salesrep', 'click', function (el, event) {
            hideButtons();
            addSalesrepRow();
            event.stop();
        });

    });

    function confirmSalesRepDelete(el) {
        var allowDelete = true;
        if (confirm(jQuery.mage.__('Are you sure you want to delete selected sales rep?')) === false) {
            allowDelete = false;
        }
        return allowDelete;
    }

    function addSalesrepRow() {

        $$('#rfq_salesreps_table tbody tr:not(.salesreps_row)').each(function (e) {
            e.remove();
        });

        var row = $('salesreps_row_template').clone(true);
        row.addClassName('new');
        row.setAttribute('id', 'salesreps_' + salesrep_count);
        row = resetInputs(row);

        row.down('.salesreps_delete').writeAttribute('name', 'salesreps[new][' + salesrep_count + '][delete]');
        row.down('.salesreps_name').writeAttribute('name', 'salesreps[new][' + salesrep_count + '][name]');

        $('rfq_salesreps').down('tbody').insert({bottom: row});
        colorRows('rfq_salesreps', '');
        salesrep_count += 1;
        rfqHasChanged();
    }
});
