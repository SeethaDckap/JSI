define(
    [
        'jquery',
        'mage/validation',
        'Magento_Ui/js/modal/alert'
    ],
    function ($,validation,alertbox) {
        'use strict';

        return {

            /**
             * Validate checkout agreements
             *
             * @returns {Boolean}
             */
            validate: function () {
                var element = $('.payment-method._active input[name="captcha_string"]');
                if(element !== undefined){
                    if (element.val() !== undefined && element.val() == "") {
                        element.filter(":visible").focus();
                        alertbox({
                            title: 'Error',
                            content: 'Please enter Captcha'
                        });
                        return false;
                    }
                }

                return true;
            }
        };
    }
);