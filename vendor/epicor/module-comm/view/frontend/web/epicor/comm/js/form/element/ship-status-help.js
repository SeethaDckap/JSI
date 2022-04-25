/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define([
    "jquery",
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select'
], function ($, _, uiRegistry, select) {
    'use strict';

    return select.extend({

        /**
         * On shipping checkout -
         * Ship Status help(toggle) change on update option.
         *
         * @param value
         * @returns {*}
         */
        onUpdate: function (value) {
            if (this.optionHelp) {
                var optionHelp = JSON.parse(this.optionHelp);

                $.each(optionHelp, function (i, v) {
                    if (i === value) {
                        $(".field.ecc_ship_status_erpcode .field-tooltip-content,.field.becc_ship_status_erpcode .field-tooltip-content").html(v);
                    }
                });
            }
            return this._super();
        }
    });
});