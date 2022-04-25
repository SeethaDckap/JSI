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
    'Epicor_Comm/epicor/comm/js/order/ddacall',
    'mage/cookies'
], function(
    $,
    ddacall
) {
    'use strict';
    return function(targetModule) {
        //if targetModule is a uiClass based object
        return targetModule.extend({
            showpopup: false,
            selectAddress: function () {
                var result = this._super();
                if(this.address().getKey() == 'new-customer-address'){                    
                    $.cookie('erp_shipping_customer_addressId', "new-address", {expires: 365, path: '/' });
                                          
                }
                ddacall(false,'default_shippingdates');
                return result;
            },

        editAddress: function() {
            var result = this._super();
            if(window.checkoutConfig.isSalesRep || window.checkoutConfig.isB2BHierarchyMasquerade){
               $('#co-shipping-form .choice').hide();
            }
           return result;
        }
        });
    };

});