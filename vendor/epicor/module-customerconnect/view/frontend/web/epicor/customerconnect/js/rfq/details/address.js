/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


require([
    'jquery',
    'prototype'
], function (jQuery) {
    jQuery(function () {

        // General form
        if ($('delivery_method')) {
            var el = $('delivery_method');
            if (el.tagName == 'SELECT' || el.tagName == 'select') {
                if (el.options[el.selectedIndex].value == 'other') {
                    $('delivery_method_other_content').show();
                } else {
                    $('delivery_method_other_content').hide();
                }
            }
        }

        Event.live('#delivery_method', 'change', function (el, event) {
            hideButtons();
            if (el.options[el.selectedIndex].value == 'other') {
                $('delivery_method_other_content').show();
            } else {
                $('delivery_method_other_content').hide();
            }
        });

        // Addresses
        Event.live('#quote-address-select', 'change', function (el) {
            hideButtons();
            updateAddressDetails('quote');
        });

        Event.live('#delivery-address-select', 'change', function (el) {
            hideButtons();
            updateAddressDetails('delivery');
        });

    });

    function updateAddressDetails(type) {
        el = $(type + '-address-select');

        var selectedAddress = el.options[el.selectedIndex].value
        $('loading-mask').show();
        url = $('rfq_address_details').readAttribute('value');
        url = url + (url.match(new RegExp('\\?')) ? '&isAjax=true' : '?isAjax=true');
        var form_data = {'addressid': selectedAddress, 'type': type, 'address-data': el.options[el.selectedIndex].readAttribute('data-address')};
        this.ajaxRequest = new Ajax.Request(url, {
                method: 'GET',
                parameters: form_data,
                onComplete: function (request) {
                    this.ajaxRequest = false;
                }.bind(this),
                onSuccess: function (data) {
                    $('loading-mask').hide();
            $(type + '-address-content').update(data.responseText);
                }.bind(this),
                onFailure: function (transport) {
                    if ($('loading-mask')) {
                        $('loading-mask').hide();
                    }
                    alert('error');
                }.bind(this),
                onException: function (request, e) {
                    if ($('loading-mask')) {
                        $('loading-mask').hide();
                    }
                    alert(e);
                }.bind(this)
            });
    }

    window.resetDeliveryAddress = function() {
        if ($('delivery_county_id')) {
            var selectedDefault = $('delivery_county_id').readAttribute('defaultValue');

            var options = $$('select#delivery_county_id option');
            var len = options.length;
            for (var oi = 0; oi < len; oi++) {
                if (options[oi].value == selectedDefault) {
                    options[oi].selected = true;
                }
            }
        }
    }
});
