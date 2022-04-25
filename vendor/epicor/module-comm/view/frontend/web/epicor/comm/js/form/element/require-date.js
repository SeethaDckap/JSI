/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define([
    "jquery",
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract',
    'Magento_Ui/js/modal/modal',
    'mage/url',
    'Magento_Checkout/js/model/error-processor',
    'Magento_Ui/js/lib/validation/validator'
], function ($, _, uiRegistry, date, modal, urlBuilder, errorProcessor,validator) {
    'use strict';
    return date.extend({

        initialize: function () {
            this._super();

            //custom rule date validation
            validator.addRule(
                'validate-before-today-date',
                function (value) {
                    if(value) {
                        return (new Date(new Date(value).toDateString()) >= new Date(new Date().toDateString()));
                    }
                    return true;
                },
                $.mage.__("Required Date should be greater than or equal to today.")
            );
        },
        /**
         * On cart checkout -
         * Update require date
         *
         * @param value
         * @returns {*}
         */
        onUpdate: function (value) {
            var isValid = this.validate();
            if(!isValid.valid){
                return false;
            }
            this._super();
            var urls = 'checkout/cart/updateRequireDate';
            var url = urlBuilder.build(urls);
            var isCart = this.isCart;
            $('body').trigger('processStart');
            $.ajax({
                global: false,
                url: url,
                data: {"ecc_required_date": value},
                type: "POST",
                dataType: 'json'
            }).done(function (response) {
                $('body').trigger('processStop');
                var serviceUrl = urlBuilder.build('checkout/cart/', {});
                if (response.error) {
                    window.location = serviceUrl;
                }
                if(isCart){
                    window.location = serviceUrl;
                }
            }).fail(
                function (response) {
                    $('body').trigger('processStop');
                    errorProcessor.process(response);
                }
            );
            return true;
        }
    });
});