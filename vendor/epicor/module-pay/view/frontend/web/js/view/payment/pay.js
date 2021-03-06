/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*browser:true*/
/*global define*/
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
                type: 'pay',
                component: 'Epicor_Pay/js/view/payment/method-renderer/pay'
            }
        );
        /** Add view logic here if needed */
        return Component.extend({});
    }
);
