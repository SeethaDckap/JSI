/**
 * Copyright © 2010-2019 Epicor Software Corporation: All Rights Reserved
 */
define([
    'uiRegistry',
    'mage/utils/wrapper'
], function (registry, wrapper) {
    'use strict';

    var config = {};
    if(window.checkoutConfig) {
        var config = window.checkoutConfig.vertexAddressValidationConfig || {};
    }

    return function (target) {
        if (!config.isAddressValidationEnabled) {
            return target;
        }

        var validationMessage = registry.get(
            'checkout.steps.shipping-step.shippingAddress' +
            '.before-shipping-method-form.shippingAdditional'
        );

        target.setSelectedShippingAddress = wrapper.wrap(target.setSelectedShippingAddress, function (original, args) {
            var addressValidator = registry.get(
                'checkout.steps.shipping-step.shippingAddress' +
                '.before-shipping-method-form.shippingAdditional' +
                '.address-validation-message.validator'
            );

            addressValidator.isAddressValid = false;
            validationMessage.clear();

            return original(args);
        });

        return target;
    }
});
