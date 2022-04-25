define(
    [
        'Magento_Ui/js/form/element/abstract',
        'Magento_Checkout/js/model/payment/additional-validators',
        'Epicor_Comm/epicor/comm/js/model/checkout-captcha-validator'
    ],
    function (Component, additionalValidators, captchaValidation) {
        'use strict';
        additionalValidators.registerValidator(captchaValidation);
        return Component.extend({});
    }
);