/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

if (typeof Solarsoft == 'undefined') {
    var Solarsoft = {};
}
require([
    'jquery',
    'Magento_Customer/js/customer-data',
    'prototype'
], function (jQuery, customerData) {
    Solarsoft.lineContractSelect = Class.create();
    Solarsoft.lineContractSelect.prototype = {
        type: null,
        target: null,
        wrapperId: 'line-contract-select',
        form: null,

        initialize: function (form) {
            if (!$('window-overlay')) {
                $(document.body).insert('<div id="window-overlay" class="window-overlay" style="display:none;"></div>');
            }

            if (!$('loading-mask')) {
                $(document.body).insert('<div id="loading-mask" style="display:none;"><p class="loader" id="loading_mask_loader">Please wait...</p></div>');
            }
        },
        openpopup: function (itemid) {
            if ($(this.wrapperId)) {
                $(this.wrapperId).remove();
            }
            // create Popup Wrapper
            var wrappingDiv = new Element('div');
            wrappingDiv.id = this.wrapperId;
            $('loading-mask').show();
            $(document.body).insert(wrappingDiv);
            $(this.wrapperId).hide();
            var url = $('line_contract_select_url').value;
            var formKey = FORM_KEY;
            this.ajaxRequest = new Ajax.Request(url, {
                method: 'post',
                parameters: {'itemid': itemid, 'return_url': window.location.href, 'form_key': formKey},
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
                    $('loading-mask').hide();
                    this.closepopup();                    
                }.bind(this),
                onException: function (request, e) {
                    alert('Error occured loading products grid');
                    $('loading-mask').hide();
                    this.closepopup();                    
                }.bind(this)
            });
        },
        closepopup: function () {
            $(this.wrapperId).hide();
            $('window-overlay').hide();
        },
        updateWrapper: function () {
            if ($(this.wrapperId)) {
                var height = 20;
                $$('#' + this.wrapperId + ' > *').each(function (item) {
                    height += item.getHeight();
                });
                if (height > ($(document.viewport).getHeight() - 100))
                    height = $(document.viewport).getHeight() - 100;
                if (height < 35)
                    height = 35;
                $(this.wrapperId).setStyle({
                    'height': height + 'px',
                });
            }
        }
    };
    var lineContractSelect = 'test';
    document.observe('dom:loaded', function () {
        lineContractSelect = new Solarsoft.lineContractSelect();
        Event.observe(window, "resize", function () {
            lineContractSelect.updateWrapper();
        });
        window.lineContractSelect = lineContractSelect;
    });
    return function () {
        //refresh minicart so if cart has updated on this page, minicart is correct
        var updateCartIfinPath = ['catalogsearch/result'];
        if (window.location.href.indexOf(updateCartIfinPath) > -1) {
            var sections = ['cart'];
            customerData.invalidate(sections);
            customerData.reload(sections, true);
        }
    }
});
//Epicor.lineContractSelect = Class.create();
//Epicor.lineContractSelect.prototype = {
//    wrapperId: 'line-contract-select',
//    height: 0,
//    boxAccountPaddingHeight: 0,
//    boxAccountPaddingBottom: 0,
//    initialize: function () {
//        if (!$('window-overlay')) {
//            $(document.body).insert('<div id="window-overlay" class="window-overlay" style="display:none;"></div>');
//        }
//        if (!$('loading-mask')) {
//            $(document.body).insert('<div id="loading-mask" class="loading-mask" style="display:none;"></div>');
//        }
//        if (!$(this.wrapperId)) {
//            $(document.body).insert('<div id="' + this.wrapperId + '" class="' + this.wrapperId + '" style="display:none;"></div>');
//        }
//    },
//    openpopup: function (itemid) {
//        $(this.wrapperId).hide();
//        $(this.wrapperId).update('');
//        // create Popup Wrapper
//        $('loading-mask').show();
//        
//        var url = $('line_contract_select_url').value;
//        url = url + (url.match(new RegExp('\\?')) ? '&isAjax=true' : '?isAjax=true');
//        performAjax(url, 'post', {'itemid': itemid, return_url: window.location.href}, this.showContent);
//    },
//    showContent: function (request) {
//        $('line-contract-select').update(request.responseText);
//        $('window-overlay').insert($('line-contract-select'));
//        $('line-contract-select').show();
//        $('window-overlay').show();
//        $('loading-mask').hide();
//        lineContractSelect.updateWrapper();
//    },
//    updateWrapper: function () {
//        positionOverlayElement('line-contract-select');
//    },
//    closepopup: function () {
//        $(this.wrapperId).hide();
//        $('window-overlay').hide();
//    },
//};
//
//var lineContractSelect;
// 
//document.observe('dom:loaded', function () {
//    lineContractSelect = new Epicor.lineContractSelect();
//    if ($('line_contract_select_url')) {
//        Event.observe(window, 'resize', function () {
//            lineContractSelect.updateWrapper();
//        });
//    }
//});
