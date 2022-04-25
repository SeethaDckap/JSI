/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'mage/validation/validation',
    'mage/dataPost'
], function($, mageTemplate, alert) {
    'use strict';

    return function(widget) {
        $.widget('mage.wishlist', widget, {
            _beforeAddToCart: function (event) {
                var wishlistForm = $(event.currentTarget).closest("form");
                var valid = wishlistForm.validation() && wishlistForm.validation('isValid');
                if (!valid) {
                    event.stopPropagation(event);
                    event.preventDefault();
                    return false;
                }
                this._super(event);
            }
        });
        return $.mage.wishlist;
    };

});