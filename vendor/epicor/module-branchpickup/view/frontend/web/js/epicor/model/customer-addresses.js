/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
    [
        'jquery',
        'ko',
        './branchaddress'
    ],
    function($, ko, branchaddress) {
        "use strict";
        var isLoggedIn = ko.observable(window.isCustomerLoggedIn);
        return {
            getBranchAddressItems: function() {
                var items = [];
                    var customerData = window.checkoutConfig.customerBranchPickupData;
                    //console.log(customerData);
                    if (Object.keys(customerData).length) {
                        $.each(customerData.addresses, function (key, item) {
                            items.push(new branchaddress(item));
                        });
                    }
                return items;
            }
        }
    }
);
