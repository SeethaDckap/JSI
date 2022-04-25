/**
 * Copyright © 2010-2020 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery',
    'Magento_Captcha/js/model/captchaList'
], function ($, captchaList) {
    'use strict';

    var mixin = {

        formId: 'element-payment-form',

        /**
         * Sets custom template for Payflow Pro
         *
         * @param {Object} payment
         * @returns {Object}
         */
        createComponent: function (payment) {

            var component = this._super(payment);

            if (component.component === 'Epicor_Elements/js/view/payment/elements') {
                $(window).off('clearTimeout')
                    .on('clearTimeout', this.clearTimeout.bind(this));
            }

            return component;
        },

        /**
         * Overrides default window.clearTimeout() to catch errors from iframe and reload Captcha.
         */
        clearTimeout: function () {
            var captcha = captchaList.getCaptchaByFormId(this.formId);

            if (captcha !== null) {
                captcha.refresh();
            }
            clearTimeout();
        }
    };

    /**
     * Overrides `Magento_Checkout/js/view/payment/list::createComponent`
     */
    return function (target) {
        return target.extend(mixin);
    };
});
