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
            'validate-cpassword',
            function () {
                var conf = $('#confirmation').length > 0 ? $('#confirmation') : $($('.validate-cpassword')[0]),
                    pass = false,
                    passwordElements, i, passwordElement;

                if ($('#password')) {
                    pass = $('#password');
                }
                passwordElements = $('.validate-password');

                for (i = 0; i < passwordElements.length; i++) {
                    passwordElement = $(passwordElements[i]);

                    if (passwordElement.closest('form').attr('id') === conf.closest('form').attr('id')) {
                        pass = passwordElement;
                    }
                }

                if ($('.validate-admin-password').length) {
                    pass = $($('.validate-admin-password')[0]);
                }

                if ($('.validate-customer-password').length) {
                    pass = $($('.validate-customer-password')[0]);
                }

                return pass.val() === conf.val();
            },
            $.mage.__('Please make sure your passwords match.')
        );

        $.validator.addMethod(
            'validate-tracking-url',
            function (v, elm) {
                var regex = /^((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w-_]*)?\??(?:[-\+=&;%@.\w_{}]*)#?(?:[\w]*))?)$/i.test(v);
                return Validation.get('IsEmpty').test(v) || regex
            },
            $.mage.__('Please enter a valid Tracking URL.')
        );

        $.validator.addMethod(
            'validate-tracking-url-tnum',
            function (v, elm) {
                if((v.indexOf('{{TNUM}}') <= 0) && (v.length > 0)){
                    return false;
                }
                return true;
            },
            $.mage.__('URL should contain this value {{TNUM}} (otherwise tracking replacement wont work)')
        );
    }
});