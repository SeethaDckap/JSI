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
                var element = $('.payment-method._active input[name="additional[reference]"]');
                if(!element.val()){
                    element.filter(":visible").focus();
                    alertbox({
                        title: 'Error',
                        content: 'Please enter a additional reference'
                    });
                    return false;
                }

                return true;
            }
        };
    }
);