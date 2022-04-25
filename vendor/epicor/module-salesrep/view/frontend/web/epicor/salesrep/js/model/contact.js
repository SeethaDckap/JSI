/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define(
    [
        'ko'
    ],
    function(ko) {
        "use strict";
        var selectedcontact = ko.observable(null);
        var billingAddressids =  ko.observableArray([]);
        var shippingAddressids = ko.observableArray([]);
        
        return {
            selectedcontact: selectedcontact,
            billingAddressids: billingAddressids,
            shippingAddressids: shippingAddressids,
        };
    }
);
