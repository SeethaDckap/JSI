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
    'Epicor_Lists/epicor/lists/js/addressselector',
    'Magento_Checkout/js/model/quote',
    'Epicor_SalesRep/epicor/salesrep/js/model/contact',
], function (
        $,
        storage,
        urlBuilder,
        fullScreenLoader,
        addressselector,
        quote,
        contact
) {
    'use strict';

    /** Override default place order action and add comment to request */
    return function (salesrep_contact,type,choose) {
        var address = quote.shippingAddress();
        
            var params = {salesrep_contact:salesrep_contact,type:type,choose:choose};
            var urls = 'salesrep/onepage/getcontactaddress';
       
 
        var url = urlBuilder.build(urls);
        fullScreenLoader.startLoader();
        
        return  $.ajax({
                showLoader: true,
                url: url,
                data:params,
                type: "POST",
                dataType: 'json'
             }).done(
                function(result) {
                    this.contactaddress = result;
                    contact.selectedcontact(result.selected_contact);
                    contact.shippingAddressids([]);
                    contact.shippingAddressids(result.shippingAddressids);
                    contact.billingAddressids([]);
                    contact.billingAddressids(result.billingAddressids);
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
