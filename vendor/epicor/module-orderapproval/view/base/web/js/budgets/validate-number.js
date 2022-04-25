/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define([
    'jquery',
    'Epicor_OrderApproval/js/budgets/budget-utilities',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function ($, utills) {
    'use strict';
    return function (param) {
        $.validator.addMethod(
            "currency-number",
            function (value, element) {
                let testValue = value.trim();
                return utills.isEmpty(testValue) || /^(?:\d{1,3}(?:,\d{3})*|\d+)(?:\.\d+)?$/i.test(testValue);
            },
            $.mage.__("Please enter a valid number (Ex: 1,234.00 or 1,234 or 1234.00 or 1234)")
        );
    }
});