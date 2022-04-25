/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'Magento_Customer/js/customer-data',
], function($, customerData) {
    'use strict';
    return function(targetModule) {
        return targetModule.extend({
            isPunchout: function () {
                return window.checkout.isPunchout;
            },
            redirectpunchout: function () {
                $('[data-block="minicart"]').find('[data-role="dropdownDialog"]').dropdownDialog('close');
                $('#top-cart-btn-punchout').click(function() {
                    location.href = window.checkout.punchoutUrl;
                });
            }
        });
    };

});