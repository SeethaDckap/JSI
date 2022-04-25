/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * @author aakimov
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
            initSidebar: function() {
                    var result = this._super();
                    var showcheckout = window.checkout.showCheckout;
                    if(showcheckout==false){
                          $("#top-cart-btn-checkout").remove();
                    }
                    return result;
                },
            update: function (updatedCart) {
                var update = this._super();
                var items = this.getCartLineItemsCount();
                if(items == 0){
                    $('.minicart-wrapper #save-cart-as-list').hide();
                }else{
                    $('.minicart-wrapper #save-cart-as-list').show();
                }
                return update;
            },
            getCartLineItemsCount: function () {
                var items = this.getCartParam('items') || [];
                return parseInt(items.length, 10);
            },
            /**
             * Allows price disabling in the minicart
             * configuration -> customers -> customers configuration
             * -> disable functionality
             * @returns []
             */
            getCartItems: function () {
                var cartItems = this._super();
                var self = this;
                if(this.isPriceDisplayDisabled()){
                    $.each(cartItems, function(i,item){
                        if(self.isProductTypeBundle(item)){
                            let itemOptions = item.options;
                            $.each(itemOptions, function(inx, option){
                                if(typeof option.value[1] !== 'undefined'){
                                    option.value[1] = '';
                                }
                            });
                        }
                    });
                    return cartItems;
                }else{
                    return cartItems;
                }
            },
            isPriceDisplayDisabled: function() {
                if(typeof window.checkout.isPriceDisplayDisabled !== 'undefined'){
                    return window.checkout.isPriceDisplayDisabled;
                }
                return false;
            },
            isProductTypeBundle: function(item) {
                if(item.hasOwnProperty('product_type')){
                    let type = item.product_type;
                    if(type === 'bundle'){
                        return true;
                    }
                }
                return false;
            }
        });
    };

});