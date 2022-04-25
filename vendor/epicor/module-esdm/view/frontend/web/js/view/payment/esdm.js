/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/payment/renderer-list'
    ],
    function (
        Component,
        rendererList
    ) {
        'use strict';
        rendererList.push(
            {
                type: 'esdm',
                component: 'Epicor_Esdm/js/view/payment/method-renderer/esdm-method'
            }
        );
        return Component.extend({});
    }
);