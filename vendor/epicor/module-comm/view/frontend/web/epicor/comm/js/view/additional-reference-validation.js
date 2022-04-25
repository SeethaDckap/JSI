define(
    [
        'Magento_Ui/js/form/element/abstract',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Epicor_Comm/epicor/comm/js/model/additional-reference-validator'
    ],
    function (Component, additionalValidators, gmailValidation) {
        'use strict';
        additionalValidators.registerValidator(gmailValidation);
        return Component.extend({});
    }
);