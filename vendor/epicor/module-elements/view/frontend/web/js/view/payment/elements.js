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
                type: 'elements',
                component: 'Epicor_Elements/js/view/payment/method-renderer/elements-method'
            }
        );
        return Component.extend({});
    }
);