/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

function changeShippingAdresss(action_value) {
    var ajax_url = $('ajaxdeliveryaddressurl').value;
    var ajaxcode = $('ajaxcode');
    var url = decodeURIComponent(ajax_url);
    if (typeof (ajaxcode) != "undefined" && ajaxcode) {
        var action_value = action_value.getAttribute("href");
        //$('window-overlay').show();
       // $('loading-mask').show();
    }
    this.ajaxRequest = new Ajax.Request(url, {
        method: 'post',
        parameters: {
            'addressid': action_value
        },
        onComplete: function (request) {
            this.ajaxRequest = false;
        }.bind(this),
        onSuccess: function (data) {
            var json = data.responseText.evalJSON();
            var theHTML = json.html;
            var theValues = json.values;
            var addressid = json.addressid;
            var getDetails = json.details;
            var cartPage = new Epicor_cartPage.cartselect();
            if ((json.type == 'success') && (Object.keys(theValues).length != 0)) {
                cartPage.openpopup('ecc_deliveryaddress_cart', this.href, theValues, addressid);
                return false;
            } else {
                if (getKeys(getDetails)) {
                    cartPage.setaddress('ecc_deliveryaddress_cart', this.href, addressid);
                    return false;
                }
            }
            $('loading-mask').hide();
        }.bind(this),
        onFailure: function (request) {
            alert('Error occured in Ajax Call');
        }.bind(this),
        onException: function (request, e) {
            alert(e);
        }.bind(this)
    });
}


if (typeof Epicor_cartPage == 'undefined') {
    var Epicor_cartPage = {};
}
require([
    'jquery',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/checkout-data',
    'prototype'
], function (jQuery,fullScreenLoader,checkoutdata) {
Epicor_cartPage.cartselect = Class.create();
Epicor_cartPage.cartselect.prototype = {
    target: null,
    wrapperId: "selectchooseaddressWrapperWindow", //selectbrancPickupWrapperWindow
    initialize: function() {
        if (!$('window-overlay')) $(document.body).insert(
            '<div id="window-overlay" class="window-overlay" style="display:none;"></div>');
        if ($('loading-mask')) {
            $("loading-mask").remove();
        }
        if (!$('loading-mask')) $(document.body).insert(
            '<div id="loading-mask" style="display:none;"><p class="loader" id="loading_mask_loader">Please wait...</p></div>'
        );
    },
    openpopup: function(newtarget, ahref, removeId, addressid) {
        this.target = newtarget;
        if ($(this.wrapperId)) $(this.wrapperId).remove();
        // create Popup Wrapper
        var wrappingDiv = new Element('div');
        wrappingDiv.id = this.wrapperId;
        $('loading-mask').show();
        $(document.body).insert(wrappingDiv);
        $(this.wrapperId).hide();
        var cartpopupurl = $('cartpopupurl').value;
        var form_key = jQuery.cookie('form_key');

        fullScreenLoader.startLoader();
        this.ajaxRequest = new Ajax.Request(cartpopupurl, {
            method: 'post',
            parameters: {
                field_id: newtarget,
                removeval: JSON.stringify(removeId),
                form_key:form_key,
                addressid: addressid,
                shippingmethod: checkoutdata.getSelectedShippingRate()
            },
            onComplete: function(request) {
                this.ajaxRequest = false;
            }.bind(this),
            onSuccess: function(request) {
                $('loading-mask').hide();
                $(this.wrapperId).insert(request.responseText);
                $(this.wrapperId).show();
                $('window-overlay').hide();
                fullScreenLoader.stopLoader();
               // this.updateWrapper();
            }.bind(this),
            onFailure: function(request) {
                alert('Error occured loading products grid');
                this.closepopup();
            }.bind(this),
            onException: function(request, e) {
                alert('Error occured loading products grid');
                fullScreenLoader.stopLoader();
                this.closepopup();
            }.bind(this)
        });
    },
    //set the address in session
    setaddress: function(newtarget, ahref, addressid) {
        this.target = newtarget;
        if ($(this.wrapperId)) $(this.wrapperId).remove();
        // create Popup Wrapper
        var wrappingDiv = new Element('div');
        wrappingDiv.id = this.wrapperId;
        $('loading-mask').show();
        $(document.body).insert(wrappingDiv);
        $(this.wrapperId).hide();
        var selectaddressurl = $('selectaddress').value;
        this.ajaxRequest = new Ajax.Request(selectaddressurl, {
            method: 'post',
            parameters: {
                field_id: newtarget,
                addressid: addressid
            },
            onComplete: function(request) {
                this.ajaxRequest = false;
            }.bind(this),
            onSuccess: function(request) {
                $('loading-mask').hide();
                var ajaxcode = $('ajaxcode');
                var isCheckoutAjax = false;
                if (typeof(ajaxcode) != "undefined" && ajaxcode && !(isCheckoutAjax)) {
                    window.location.reload();
                }
                $(this.wrapperId).remove();
                $('window-overlay').hide();
            }.bind(this),
            onFailure: function(request) {
                alert('Error occured loading delivery address grid');
                this.closepopup();
            }.bind(this),
            onException: function(request, e) {
                alert('Error occured loading delivery address grid');
                this.closepopup();
            }.bind(this)
        });
    },
    loaderoptions: function(parentDiv, childDiv) {
        if (childDiv == parentDiv) {
            alert("The parent div cannot be removed.");
        } else if (document.getElementById(childDiv)) {
            var child = document.getElementById(childDiv);
            var parent = document.getElementById(parentDiv);
            parent.removeChild(child);
            if (!$('loading-mask')) $(document.body).insert(
                '<div id="loading-mask" style="display:none;"><p class="loader" id="loading_mask_loader">Please wait...</p></div>'
            );
        } else {
            return false;
        }
    },
    //Assign the values into hidden field
    //Also changing the shipping form values when the user pressed continue in shipping information page

    closepopup: function() {
        $(this.wrapperId).remove();
        var elementBranch = $('branch_pickup');
        if (typeof(elementBranch) != 'undefined' && elementBranch != null) {
            var defaultValue = $('selectedbranch').value;
            $('branch_pickup').value = defaultValue;
        }
       // $('window-overlay').hide();
        jQuery(".action-close").click(); // Close pop modal
        $('window-overlay').hide();
    },
    getSelectedValues: function() {
        var shippingBranch = document.getElementsByName('selectshipping');
        for (var i = 0; i < shippingBranch.length; i++) {
            if (shippingBranch[i].checked) {
                return selectedShipMValue = shippingBranch[i].value;
            }
        }
    },
    removeClass: function(ele, cls) {
        if (this.hasClass(ele, cls)) {
            var reg = new RegExp('(\\s|^)' + cls + '(\\s|$)');
            ele.className = ele.className.replace(reg, ' ');
        }
    },
    hasClass: function(element, className) {
        if (element) {
            return element.className && new RegExp("(^|\\s)" + className + "(\\s|$)").test(element.className);
        }
    },
    checkselected: function() {
        var checkPresent = $('s_method_epicor_branchpickup_epicor_branchpickup');
        if (typeof(checkPresent) != "undefined" && checkPresent) {
            var optionChecked = $('s_method_epicor_branchpickup_epicor_branchpickup').checked;
            var action_value = $('branch_pickup').value;
            if ((optionChecked) && (!action_value)) {
                $('brancherrormsg').show();
                $('branchvalidationrmsg').hide();
                return false;
            } else {}
        }
    },

    updateWrapper: function() {
        if ($(this.wrapperId)) {
            var height = 20;
            $$('#' + this.wrapperId + ' > *').each(function(item) {
                height += item.getHeight();
            });
            if (height > ($(document.viewport).getHeight() - 40)) height = $(document.viewport).getHeight() -
                40;
            if (height < 35) height = 35;
            $(this.wrapperId).setStyle({
                'height': height + 'px',
                'marginTop': '-' + (height / 2) + 'px'
            });
        }
    }
};

function selectItemByValue(elmnt, value) {
    for (var i = 0; i < elmnt.options.length; i++) {
        if (elmnt.options[i].value == value) elmnt.selectedIndex = i;
    }
}

document.observe('dom:loaded', function() {
    cartPage = new Epicor_cartPage.cartselect();
    Event.observe(window, "resize", function() {
        //cartPage.updateWrapper();
    });

    window.cartPage = cartPage;
});
});

function getKeys(objects) {
    var r = [];
    for (var key in objects) {
        var keyValue = objects[key];
        var keyIdentify = 'b' + key;
        if ($(keyIdentify)) {
            var keyvaluesinsert = $(keyIdentify).value = keyValue;
        }
    }
    return true;
}

var cartPage = '';
