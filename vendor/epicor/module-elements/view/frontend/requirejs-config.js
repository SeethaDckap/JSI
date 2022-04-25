/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

var config = {
    map: {
        '*': {
            iframeElementsSetup: 'Epicor_Elements/js/setup/setupreturn',
            iframeElementsArSetup: 'Epicor_Elements/js/setup/arsetupreturn'
            
        }
    },
    config: {
        mixins: {
            'Magento_Checkout/js/view/payment/list': {
                'Epicor_Elements/js/view/payment/list-mixin': true
            }
        }
    }
};