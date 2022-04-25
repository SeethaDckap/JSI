/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($) {
    "use strict";
    return function () {
        $.validator.addMethod(
            'validatedecimalplace',
            function (value, element, decimalPlaces) {
                this.decimalPlace = decimalPlaces;
                if (this.decimalPlace !== '' && this.decimalPlace > 0) {
                    var pattern = '^[0-9]+(\.[0-9]{1,' + decimalPlaces + '})?$';
                    return this.optional(element) || new RegExp(pattern).test(value);
                } else if (this.decimalPlace !== '' && this.decimalPlace == 0) {
                    return $.mage.isEmptyNoTrim(value) || !/[^\d]/.test(value);
                }
            },
            function () {
                this.msg = '';
                if (this.decimalPlace !== '' && this.decimalPlace > 0) {
                    for (var j = 0; j < this.decimalPlace; j++) {
                    this.msg = this.msg + 'x';
                    }
                    return $.mage.__('Qty must be in the form of xxx.%1').replace('%1', this.msg);
                } else if (this.decimalPlace !== '' && this.decimalPlace == 0) {
                    return $.mage.__('Decimal Places not Permitted.')
                }
            }
        );
    }
});