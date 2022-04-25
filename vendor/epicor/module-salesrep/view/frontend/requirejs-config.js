/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

var config = {
    config: {
        mixins: {                        
            'Magento_Checkout/js/model/shipping-save-processor/default': {
                'Epicor_SalesRep/epicor/salesrep/js/mixins/shipping-save-processor/default-mixin':true
            },
            'Magento_Checkout/js/model/shipping-rate-processor/customer-address': {
                'Epicor_SalesRep/epicor/salesrep/js/mixins/shipping-rate-processor/customer-address-mixin':true
            },
            'Temando_Shipping/js/model/shipping-rate-processor/customer-address': {
                'Epicor_SalesRep/epicor/salesrep/js/mixins/shipping-rate-processor/customer-address-mixin':true
            },
            'Magento_Checkout/js/model/resource-url-manager': {
                'Epicor_SalesRep/epicor/salesrep/js/model/resource-url-manager-mixin':true
            },
            'Magento_Checkout/js/action/set-shipping-information': {
                'Epicor_SalesRep/epicor/salesrep/js/view/checkout/set-shipping-information-mixin': true
            },
            'Magento_Checkout/js/model/step-navigator': {
                'Epicor_SalesRep/epicor/salesrep/js/mixins/shipping-navigate-mixin': true
            },
            'Magento_Customer/js/model/customer/address': {
                'Epicor_SalesRep/epicor/salesrep/js/mixins/customer/address-mixin': true
            },
            'Magento_Checkout/js/action/place-order': {
                'Epicor_SalesRep/epicor/salesrep/js/mixins/model/place-order-mixin': true
            }
        }
    }
};