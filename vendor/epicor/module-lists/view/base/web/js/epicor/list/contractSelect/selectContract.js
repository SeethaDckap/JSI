/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (typeof Solarsoft == 'undefined') {
    var Solarsoft = {};
}
var accountTypesHhtml;
require([
    'jquery',
    'prototype'
], function (jQuery) {

    Solarsoft.productSelector = Class.create();
    Solarsoft.productSelector.prototype = {
        type: null,
        target: null,
        wrapperId: 'selectContractProductWrapperWindow',
        initialize: function () {
            if (!$('window-overlay')){
                $(document.body).insert('<div id="window-overlay" class="window-overlay" style="display:none;"></div>');
            }

            if (!$('loading-mask')){
                $(document.body).insert('<div id="loading-mask" style="display:none;"><p class="loader" id="loading_mask_loader">Please wait...</p></div>');
            }

        },
        openpopup: function (newtarget,ahref) {

            this.target = newtarget;
            if ($(this.wrapperId)) {
                $(this.wrapperId).remove();
            }

            // create Popup Wrapper
            var wrappingDiv = new Element('div');
            wrappingDiv.id = this.wrapperId;
            $('loading-mask').show();
            $(document.body).insert(wrappingDiv);
            $(this.wrapperId).hide();
            var website = 0;
            $$('select#_accountwebsite_id option').each(function (o) {				// id = messages1
                if (o.selected == true) {
                    website = o.value;
                }
            })

        var productGridUrl = ahref; 

            this.ajaxRequest = new Ajax.Request(productGridUrl, {
                method: 'post',
                parameters: {field_id:newtarget, website:website},
                onComplete: function (request) {
                    this.ajaxRequest = false;
                }.bind(this),
                onSuccess: function (request) {
                    $('loading-mask').hide();
                    $(this.wrapperId).insert(request.responseText);
                    $(this.wrapperId).show();
                    $('window-overlay').show();
                    this.updateWrapper();
                }.bind(this),
                onFailure: function (request) {
                    alert('Error occured loading products grid');
                    this.closepopup();
                }.bind(this),
                onException: function (request, e) {
                    alert('Error occured loading products grid');
                    this.closepopup();
                }.bind(this)
            });

        },
        closepopup: function () {
            $(this.wrapperId).remove();
            $('window-overlay').hide();
        },
        updateWrapper: function () {
            if ($(this.wrapperId)) {
                var height = 20;

                $$('#' + this.wrapperId + ' > *').each(function (item) {
                    height += item.getHeight();
                });

                if (height > ($(document.viewport).getHeight() - 40))
                    height = $(document.viewport).getHeight() - 40;

                if (height < 35)
                    height = 35;

                $(this.wrapperId).setStyle({
                    'height': height + 'px',
                    'marginTop': '-' + (height / 2) + 'px'
                });
            }
        }
    };
    var productSelector = 'test';
    var addressSelector = 'test';
    document.observe('dom:loaded', function () {
        productSelector = new Solarsoft.productSelector();
        Event.observe(window, "resize", function () {
            productSelector.updateWrapper();
        });
        window.productSelector = productSelector;
        addressSelector = new Solarsoft.addressSelector();
        Event.observe(window, "resize", function () {
            addressSelector.updateWrapper();
        });
        window.addressSelector = addressSelector;
    });
    
    Solarsoft.addressSelector = Class.create();
    Solarsoft.addressSelector.prototype = {
        type: null,
        target: null,
        wrapperId: 'selectContractLocationWrapperWindow',
        initialize: function () {
            if (!$('window-overlay')){
                $(document.body).insert('<div id="window-overlay" class="window-overlay" style="display:none;"></div>');
            }

            if (!$('loading-mask')){
                $(document.body).insert('<div id="loading-mask" style="display:none;"><p class="loader" id="loading_mask_loader">Please wait...</p></div>');
            }

        },
        openpopup: function (newtarget,ahref) {

            this.target = newtarget;
            if ($(this.wrapperId)) {
                $(this.wrapperId).remove();
            }

            // create Popup Wrapper
            var wrappingDiv = new Element('div');
            wrappingDiv.id = this.wrapperId;
            $('loading-mask').show();
            $(document.body).insert(wrappingDiv);
            $(this.wrapperId).hide();
            var website = 0;
            $$('select#_accountwebsite_id option').each(function (o) {				// id = messages1
                if (o.selected == true) {
                    website = o.value;
                }
            })

        var addressGridUrl = ahref; 

            this.ajaxRequest = new Ajax.Request(addressGridUrl, {
                method: 'post',
                parameters: {field_id:newtarget, website:website},
                onComplete: function (request) {
                    this.ajaxRequest = false;
                }.bind(this),
                onSuccess: function (request) {
                    $('loading-mask').hide();
                    $(this.wrapperId).insert(request.responseText);
                    $(this.wrapperId).show();
                    $('window-overlay').show();
                    this.updateWrapper();
                }.bind(this),
                onFailure: function (request) {
                    alert('Error occured loading products grid');
                    this.closepopup();
                }.bind(this),
                onException: function (request, e) {
                    alert('Error occured loading products grid');
                    this.closepopup();
                }.bind(this)
            });

        },
        closepopup: function () {
            $(this.wrapperId).remove();
            $('window-overlay').hide();
        },
        updateWrapper: function () {
            if ($(this.wrapperId)) {
                var height = 20;

                $$('#' + this.wrapperId + ' > *').each(function (item) {
                    height += item.getHeight();
                });

                if (height > ($(document.viewport).getHeight() - 40))
                    height = $(document.viewport).getHeight() - 40;

                if (height < 35)
                    height = 35;

                $(this.wrapperId).setStyle({
                    'height': height + 'px',
                    'marginTop': '-' + (height / 2) + 'px'
                });
            }
        }
    };

    window.onkeypress = function (event) {
        if (event.which == 13 || event.keyCode == 13) {
            return false;
        }
        return true;
    };
});
require([
    'Magento_Customer/js/customer-data'
], function (customerData) {
    var sections = ['cart'];
    customerData.invalidate(sections);
    //customerData.reload(sections, true);
});
