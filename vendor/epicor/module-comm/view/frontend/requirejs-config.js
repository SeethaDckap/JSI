/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

var config = {
    map: {
        '*': {
            searchForm:   'Epicor_Comm/epicor/comm/js/quickadd',
            "Magento_Checkout/js/model/checkout-data-resolver" : "Epicor_Comm/epicor/comm/js/checkout-data-resolver",
            "Magento_Checkout/js/view/billing-address" : "Epicor_Comm/epicor/comm/js/billing-address",
            "Magento_Checkout/js/view/billing-address/list" : "Magento_Checkout/js/view/billing-address/list",
            "Magento_Checkout/js/view/shipping-address/list" : "Epicor_Comm/epicor/comm/js/shipping-address/list",
            "Magento_Checkout/template/shipping-address/address-renderer/default.html": "Epicor_Comm/template/shipping-address/address-renderer/default.html",
            "Magento_Checkout/template/billing-address/list.html": "Epicor_Comm/template/billing-address/list.html",
            "Magento_Checkout/template/shipping-information/address-renderer/default.html": "Epicor_Comm/template/shipping-information/address-renderer/default.html",
            "Magento_Checkout/template/minicart/item/default.html": "Epicor_Comm/template/minicart/item/default.html",
            "Magento_Sales/js/view/last-ordered-items":"Epicor_Comm/epicor/comm/js/view/last-ordered-items",
            "lazyLoad": "Epicor_Comm/epicor/comm/js/lazy-load",
            "Magento_Checkout/template/form/element/email.html": "Epicor_Comm/template/form/element/email.html",
            'Amazon_Payment/template/form/element/email.html': 'Epicor_Comm/template/form/element/amznpayemail.html',
            "jquery/jquery.cookie": "Epicor_Comm/epicor/comm/js/jquery/jquery.cookie"
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/action/place-order': {
                'Epicor_Comm/epicor/comm/js/order/place-order-mixin': true
            },
            'Magento_Checkout/js/action/set-payment-information': {
                'Epicor_Comm/epicor/comm/js/order/set-payment-information-mixin': true
            },
            'Magento_Checkout/js/view/shipping-address/address-renderer/default': {
                'Epicor_Comm/epicor/comm/js/order/address-renderer-default-mixin': true
            },
            'Magento_Checkout/js/action/set-shipping-information': {
                'Epicor_Comm/epicor/comm/js/order/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/view/shipping': {
                'Epicor_Comm/epicor/comm/js/order/shipping-mixin': true
            },
            'Magento_Checkout/js/action/get-payment-information': {
                'Epicor_Comm/epicor/comm/js/order/get-payment-information-mixin': true
            },
            'Magento_Catalog/js/catalog-add-to-cart': {
                'Epicor_Comm/epicor/comm/js/order/catalog-add-to-cart-mixin': true
            },
            'Magento_PageCache/js/page-cache' : {
                'Epicor_Comm/epicor/comm/js/page-cache':true
            },
            'Magento_Theme/js/view/messages' : {
                'Epicor_Comm/epicor/comm/js/view/messages-mixin':true
            },
            'Magento_Checkout/js/view/shipping-information': {
                'Epicor_Comm/epicor/comm/js/view/shipping-information-mixin': true
            },
            'Magento_Checkout/js/view/minicart': {
                'Epicor_Comm/epicor/comm/js/view/checkout/minicart-mixin': true
            },
            'Magento_Checkout/js/action/create-shipping-address': {
                'Epicor_Comm/epicor/comm/js/action/create-shipping-address-mixin': true
            },
            'Magento_Ui/js/view/messages': {
                'Epicor_Comm/epicor/comm/js/messages-mixin': true
            },
            'mage/validation': {
                'Epicor_Comm/epicor/comm/js/validation-mixin': true
            },
            'Magento_Wishlist/js/wishlist': {
                'Epicor_Comm/epicor/comm/js/wishlist-mixin': true
            },
            'Magento_Checkout/js/view/form/element/email': {
                'Epicor_Comm/epicor/comm/js/form/element/email-mixin': true
            },
            'Magento_Wishlist/js/view/wishlist': {
                'Epicor_Comm/epicor/comm/js/view/wishlist-mixin': true
            },
            'Magento_Catalog/js/price-utils':{
                'Epicor_Comm/epicor/comm/js/price-utils': true
            },
            'Magento_ConfigurableProduct/js/configurable': {
                'Epicor_Comm/epicor/comm/js/configurable': true
            }
        }
    }
};
