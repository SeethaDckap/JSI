/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * @author aakimov
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/storage',
    'ko',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/action/set-shipping-information',
    'Magento_Checkout/js/model/step-navigator',
    'mage/utils/wrapper',
    'Epicor_Comm/epicor/comm/js/order/cartpopajax',
    'Magento_Checkout/js/model/quote',
    'Epicor_Comm/epicor/comm/js/model/noticeList',
], function (
        $,
        storage,
        ko,
        urlBuilder,
        fullScreenLoader,
        setShippingInformationAction,
        stepNavigator,
        wrapper,
        cartpopajax,
        quote,
        messageManager
) {
    'use strict';

    return function (getpaymentinfo) {

        /** Override place-order-mixin for set-payment-information action as they differs only by method signature */
        return wrapper.wrap(getpaymentinfo, function (originalAction, deferred, messageContainer) {
            var result = originalAction(deferred, messageContainer);
            cartpopajax('payment').done(
                        function () {
                            if(this.showpopup){
                                stepNavigator.navigateTo('shipping');
                            }else{
                                console.log('Success');
                            }
                         }
                    );

            /**
             * OrderApproval warning message
             * for approval group
             */
            result.then(function handlePayMsg(response) {
                //OrderApproval message
                if(typeof response.extension_attributes !== "undefined" &&  response.extension_attributes.is_approval_require){
                    messageManager.addNoticeMessage({
                        message: response.extension_attributes.is_approval_require
                    });
                }
            });

            return result;
        });
    };
});
