define([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($) {
    'use strict';
    return function () {
        $.validator.addMethod(
            "validate-digits-range-custommsg",
            function (v, elm) {
                if (Validation.get('IsEmpty').test(v)) {
                    return true;
                }

                var numValue = parseNumber(v);
                if (isNaN(numValue)) {
                    return false;
                }

                var reRange = /^digits-range-(-?\d+)?-(-?\d+)?$/,
                    range = false,
                    result = true;
                //range=reRange;
                $w(elm.className).each(function (name) {
                    var m = reRange.exec(name);
                    if (m) {
                        result = result
                            && (m[1] == null || m[1] == '' || numValue >= parseNumber(m[1]))
                            && (m[2] == null || m[2] == '' || numValue <= parseNumber(m[2]));
                        range = m[1] + '-' + m[2];
                    }
                });
                if (range) {
                    this.range = range;
                }
                return result;
            },
            function () {
                return $.mage.__("The value is not within the specified range (%1)").replace('%1', this.range);
            }
        );
    }
});