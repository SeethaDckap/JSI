/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define(
    [
        'Magento_Checkout/js/model/quote',
        'Magento_Checkout/js/model/url-builder',
        'Magento_Customer/js/model/customer',
        'Magento_Checkout/js/model/place-order'
    ],
    function (quote, urlBuilder, customer, placeOrderService) {
        'use strict';

        return function (paymentData, messageContainer) {
            var serviceUrl, payload;
            console.log(paymentData);
            console.log(jQuery('[name="ordercomment[comment]"]').val());
            paymentData.additional_data={comment:jQuery('[name="ordercomment[comment]"]').val()};
            payload = {
                cartId: quote.getQuoteId(),
                billingAddress: quote.billingAddress(),
                paymentMethod: paymentData
            };

            if (customer.isLoggedIn()) {
                serviceUrl = urlBuilder.createUrl('/carts/mine/payment-information', {});
            } else {
                serviceUrl = urlBuilder.createUrl('/guest-carts/:quoteId/payment-information', {
                    quoteId: quote.getQuoteId()
                });
                payload.email = quote.guestEmail;
            }

            return placeOrderService(serviceUrl, payload, messageContainer);
        };
    }
);
