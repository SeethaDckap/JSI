/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/place-order': {
                'Epicor_UDExample/js/model/place-order-mixin': true
            }
        }
    }
};