define([
    'jquery',
    'jquery/ui',
    'jquery/validate',
    'mage/translate'
], function($){
    'use strict';
    return function() {
        $.validator.addMethod(
            "validate-multiple-emails",
            function(value) {
                if (value) {
                    var emailReg = new RegExp(/^(\s?[^\s,;]+@[^\s,;]+\.[^\s,;]+\s*;\s*)*(\s?[^\s,;]+@[^\s,;]+\.[^\s,;]+)$/g);
                    return emailReg.test(value);
                }
                return true;
            },
            $.mage.__("Please enter a valid email address.")
        );
    }
});