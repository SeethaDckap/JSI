/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * mixin to close the Avaliable locations
 * pop up after product is added to cart.
 */
define([
    'jquery',
    'mage/translate',
    'jquery/ui'
], function($, $t) {
    "use strict";
    return function (target) {
        $.widget('mage.catalogAddToCart', target, {

            enableAddToCartButton: function (form) {
                var addToCartButtonTextAdded = this.options.addToCartButtonTextAdded || $t('Added');
                var self = this,
                        addToCartButton = $(form).find(this.options.addToCartButtonSelector);

                addToCartButton.find('span').text(addToCartButtonTextAdded);
                addToCartButton.attr('title', addToCartButtonTextAdded);
                if (addToCartButton.closest('form').attr('id') === 'ecc-tocart-form') {
                    jQuery(".action-close").click(); // Close pop modal
                }
                setTimeout(function () {
                    var addToCartButtonTextDefault = self.options.addToCartButtonTextDefault || $t('Add to Cart');
                    addToCartButton.removeClass(self.options.addToCartButtonDisabledClass);
                    addToCartButton.find('span').text(addToCartButtonTextDefault);
                    addToCartButton.attr('title', addToCartButtonTextDefault);
                }, 1000);
            }
        });

        return $.mage.catalogAddToCart;
    };
});
