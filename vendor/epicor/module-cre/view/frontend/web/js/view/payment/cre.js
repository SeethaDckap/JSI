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
                type: 'cre',
                component: 'Epicor_Cre/js/view/payment/method-renderer/cre-method'
            }
        );
        return Component.extend({});
    }
);