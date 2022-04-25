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
    'mage/storage',
    'mage/url',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/step-navigator',
    'Epicor_Lists/epicor/lists/js/addressselector',
    'Magento_Checkout/js/model/quote'
], function (
        $,
        storage,
        urlBuilder,
        fullScreenLoader,
        stepNavigator,
        addressselector,
        quote
) {
    'use strict';

    /** Override default place order action and add comment to request */
    return function (type) {
        var address = quote.shippingAddress();
        if(address.customerAddressId){
            var params = {};
            var urls = 'lists/lists/changeshippingaddress?addressid='+ address.customerAddressId;
        }else{
            var params =  {};
            var urls = 'lists/lists/changeshippingaddressajax?'+$('#co-shipping-form').serialize() ;
        }
        
        if($(('#normalshipping').length > 0) && ($("#normalshipping").prop("checked"))){
            var urls = urls + '&branchpickupvisible=true';
        }        
        
        var url = urlBuilder.build(urls);
        fullScreenLoader.startLoader();
        
        return storage.post(
                url,
                params,
                false
            ).done(
                function(result) {
                    var theValues = result.values;
                    var theHTML = result.html;
                    var theValues = result.values;
                    var addressid = result.addressid;
                    var getDetails = result.details;
                    if(Object.keys(theValues).length != 0){
                        
                        if(type == 'shipping'){
                            cartPage = new Epicor_cartPage.cartselect();
                            Event.observe(window, "resize", function() {
                                //cartPage.updateWrapper();
                            });
                           cartPage.openpopup('ecc_deliveryaddress_cart', 'null', theValues, addressid);
                        }
                      this.showpopup = true;
                    }else{
                        this.showpopup = false;
                    }
                   // fullScreenLoader.stopLoader();
                }
            ).fail(
                function(response) {                           
                    this.testrep = false;
                    fullScreenLoader.stopLoader();
                }
            ).always(                        
                    function () {
                        fullScreenLoader.stopLoader();
                    }
            );
    };
});
