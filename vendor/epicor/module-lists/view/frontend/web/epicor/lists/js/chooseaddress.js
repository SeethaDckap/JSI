/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

if (typeof Epicor_cartChooseaddress == 'undefined') {
    var Epicor_cartChooseaddress = {};
}
Epicor_cartChooseaddress.cartselect = Class.create();
Epicor_cartChooseaddress.cartselect.prototype = {
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
        var cartpopupurl = $('deliverycartpopupurl').value;
        this.ajaxRequest = new Ajax.Request(cartpopupurl, {
            method: 'post',
            parameters: {
                field_id: newtarget,
                removeval: JSON.stringify(removeId),
                addressid: addressid,
                page: 'onepage'
            },
            onComplete: function(request) {
                this.ajaxRequest = false;
            }.bind(this),
            onSuccess: function(request) {
                $('loading-mask').hide();
                $(this.wrapperId).insert(request.responseText);
                $(this.wrapperId).show();
                $('window-overlay').show();
                this.updateWrapper();
            }.bind(this),
            onFailure: function(request) {
                alert('Error occured loading products grid');
                this.closepopup();
            }.bind(this),
            onException: function(request, e) {
                alert('Error occured loading products grid');
                this.closepopup();
            }.bind(this)
        });
    },
    checkselectedoption : function () {
            var shippingBranch = document.getElementsByName('selectshipping');
            for (var i = 0; i < shippingBranch.length; i++) {
                if (shippingBranch[i].checked) {
                    return selectedShipMValue = shippingBranch[i].value;
                }
            }        
    },
    changeBillingAdresss: function(action_value) {
        var ajax_url = $('ajaxdeliveryaddressurl').value;
        var url = decodeURIComponent(ajax_url);
        var action_value = action_value;
        this.ajaxRequest = new Ajax.Request(url, {
            method: 'post',
            parameters: {
                'addressid': action_value
            },
            onComplete: function(request) {
                this.ajaxRequest = false;
            }.bind(this),
            onSuccess: function(data) {
                var json = data.responseText.evalJSON();
                var theHTML = json.html;
                var theValues = json.values;
                var addressid = json.addressid;
                var getDetails = json.details;
                if ((json.type == 'success') && (Object.keys(theValues).length != 0)) {
                    cartChooseaddress.openpopup('ecc_deliveryaddress_cart', this.href, theValues,
                        addressid);
                } else {
                    if (getKeys(getDetails)) {
                        cartChooseaddress.setaddress('ecc_deliveryaddress_cart', this.href,
                            addressid);
                    }
                }
                $('loading-mask').hide();
            }.bind(this),
            onFailure: function(request) {
                alert('Error occured in Ajax Call');
            }.bind(this),
            onException: function(request, e) {
                alert(e);
            }.bind(this)
        });
    },
    changeShippingAdresss: function(action_value) {
        var ajax_url = $('ajaxdeliveryaddressurl').value;
        var url = decodeURIComponent(ajax_url);
        var action_value = action_value;
        this.ajaxRequest = new Ajax.Request(url, {
            method: 'post',
            parameters: {
                'addressid': action_value
            },
            onComplete: function(request) {
                this.ajaxRequest = false;
            }.bind(this),
            onSuccess: function(data) {
                var json = data.responseText.evalJSON();
                var theHTML = json.html;
                var theValues = json.values;
                var addressid = json.addressid;
                var getDetails = json.details;
                if ((json.type == 'success') && (Object.keys(theValues).length != 0)) {
                    cartChooseaddress.openpopup('ecc_deliveryaddress_cart', this.href, theValues,
                        addressid);
                } else {
                    if (getKeys(getDetails)) {
                        cartChooseaddress.setshippingaddress('ecc_deliveryaddress_cart', this.href,
                            addressid);
                    }
                }
                $('loading-mask').hide();
            }.bind(this),
            onFailure: function(request) {
                alert('Error occured in Ajax Call');
            }.bind(this),
            onException: function(request, e) {
                alert(e);
            }.bind(this)
        });
    },
    setBillingData: function(){
        var billingButtons = $('billing-buttons-container');
        for (i = j = 0; i < billingButtons.childNodes.length; i++) {
            if (billingButtons.childNodes[i].nodeName == 'BUTTON') {
                j++;
                var input = billingButtons.childNodes[i];
                input.setAttribute("onclick", "return checkbillingdata();");
            }
        }        
    },
    setShippingData: function(){
        var shippingButtons = $('shipping-buttons-container');
        for (i = j = 0; i < shippingButtons.childNodes.length; i++) {
            if (shippingButtons.childNodes[i].nodeName == 'BUTTON') {
                j++;
                var input = shippingButtons.childNodes[i];
                input.setAttribute("onclick", "return checkoptiondata();");
            }
        }        
    },    
    submitBillingInformation: function(serializeVals) {
        var ajax_url = $('changebillingaddressajax').value;
        var url = decodeURIComponent(ajax_url);
        var action_value = action_value;
        this.ajaxRequest = new Ajax.Request(url, {
            method: 'post',
            parameters: {
                'addressid': serializeVals,
            },
            onComplete: function(request) {
                this.ajaxRequest = false;
            }.bind(this),
            onSuccess: function(data) {
                var json = data.responseText.evalJSON();
                var theHTML = json.html;
                var theValues = json.values;
                var addressid = json.addressid;
                var getDetails = json.details;
                if ((json.type == 'success') && (Object.keys(theValues).length != 0)) {
                    cartChooseaddress.openpopup('ecc_deliveryaddress_cart', this.href, theValues,
                        addressid);
                } else {
                    billing.save();
                }
                $('loading-mask').hide();
            }.bind(this),
            onFailure: function(request) {
                alert('Error occured in Ajax Call');
            }.bind(this),
            onException: function(request, e) {
                alert(e);
            }.bind(this)
        });
    },
    submitBillingInShippingInformation: function(serializeVals) {
        var ajax_url = $('changebillingaddressajax').value;
        var url = decodeURIComponent(ajax_url);
        var action_value = action_value;
        this.ajaxRequest = new Ajax.Request(url, {
            method: 'post',
            parameters: {
                'addressid': serializeVals,
            },
            onComplete: function(request) {
                this.ajaxRequest = false;
            }.bind(this),
            onSuccess: function(data) {
                var json = data.responseText.evalJSON();
                var theHTML = json.html;
                var theValues = json.values;
                var addressid = json.addressid;
                var getDetails = json.details;
                if ((json.type == 'success') && (Object.keys(theValues).length != 0)) {
                    cartChooseaddress.openpopup('ecc_deliveryaddress_cart', this.href, theValues,
                        addressid);
                } else {
                    shipping.save();
                }
                $('loading-mask').hide();
            }.bind(this),
            onFailure: function(request) {
                alert('Error occured in Ajax Call');
            }.bind(this),
            onException: function(request, e) {
                alert(e);
            }.bind(this)
        });
    },    
    submitShippingInformation: function(serializeVals) {
        var ajax_url = $('changeshippingaddressajax').value;
        var url = decodeURIComponent(ajax_url);
        var action_value = action_value;
        this.ajaxRequest = new Ajax.Request(url, {
            method: 'post',
            parameters: {
                'addressid': serializeVals,
            },
            onComplete: function(request) {
                this.ajaxRequest = false;
            }.bind(this),
            onSuccess: function(data) {
                var json = data.responseText.evalJSON();
                var theHTML = json.html;
                var theValues = json.values;
                var addressid = json.addressid;
                var getDetails = json.details;
                if ((json.type == 'success') && (Object.keys(theValues).length != 0)) {
                    cartChooseaddress.openpopup('ecc_deliveryaddress_cart', this.href, theValues,
                        addressid);
                } else {
                    var elementBranch = $('branch_pickup');
                    if (typeof(elementBranch) != 'undefined' && elementBranch != null) {
                        cartPage.checkoptiondata();
                    } else {
                        shipping.save();
                    }
                }
                $('loading-mask').hide();
            }.bind(this),
            onFailure: function(request) {
                alert('Error occured in Ajax Call');
            }.bind(this),
            onException: function(request, e) {
                alert(e);
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
                billing.save();
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
    //set the address in session
    setshippingaddress: function(newtarget, ahref, addressid) {
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
                var elementBranch = $('branch_pickup');
                if (typeof(elementBranch) != 'undefined' && elementBranch != null) {
                    cartPage.checkoptiondata();
                } else {
                    shipping.save();
                }
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
    //Assign the values into hidden field 
    //Also changing the shipping form values when the user pressed continue in shipping information page
    closepopup: function() {
        $(this.wrapperId).remove();
        var elementBranch = $('branch_pickup');
        if (typeof(elementBranch) != 'undefined' && elementBranch != null) {
            var defaultValue = $('selectedbranch').value;
            $('branch_pickup').value = defaultValue;
        }
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
    appendBillingSteps: function() {
        //Append a new function into the Billing Information Tab
        var billingButtons = $('billing-buttons-container');
        for (i = j = 0; i < billingButtons.childNodes.length; i++) {
            if (billingButtons.childNodes[i].nodeName == 'BUTTON') {
                j++;
                var input = billingButtons.childNodes[i];
                input.setAttribute("onclick", "return checkbillingdata();");
            }
        }        
    },
    appendShippingSteps: function() {
        //Append a new function into the shipping Information Tab
        var shippingButtons = $('shipping-buttons-container');
        for (i = j = 0; i < shippingButtons.childNodes.length; i++) {
            if (shippingButtons.childNodes[i].nodeName == 'BUTTON') {
                j++;
                var input = shippingButtons.childNodes[i];
                input.setAttribute("onclick", "return checkshippingdata();");
            }
        }      
    },    
    checkbranchpickupresent: function() {
        var elementBranch = $('branch_pickup');
        if (typeof(elementBranch) != 'undefined' && elementBranch != null) {
            var selectedShipping = cartChooseaddress.checkselectedoption();
            if(selectedShipping =='branchpickupshipping') {
               cartPage.checkoptiondata();
               return 'branchpickupflow';
            }
            if(selectedShipping =='normalshipping') {
               return 'normalflow';
            }        
        }
        return 'normalflow';        
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

function selectItemByValue(elmnt, value) {
    for (var i = 0; i < elmnt.options.length; i++) {
        if (elmnt.options[i].value == value) elmnt.selectedIndex = i;
    }
}
var cartChooseaddress = '';
document.observe('dom:loaded', function() {
    cartChooseaddress = new Epicor_cartChooseaddress.cartselect();
    Event.observe(window, "resize", function() {
        cartChooseaddress.updateWrapper();
    });
});


/* One page checkout changes starts */
document.observe('dom:loaded', function() {
cartChooseaddress.appendBillingSteps();
cartChooseaddress.appendShippingSteps();    
Checkout.prototype.reloadProgressBlock = Checkout.prototype.reloadProgressBlock.wrap(
    function(parentMethod, toStep) {
        if(toStep =="salesrep_contact") {
            cartChooseaddress.appendBillingSteps();
            cartChooseaddress.appendShippingSteps();             
        }
        parentMethod(toStep);
    }
);    

//    var elementBranch = $('branch_pickup');
//    if (typeof(elementBranch) != 'undefined' && elementBranch != null) {
//        if (typeof($('billing:use_for_shipping_no')) != "undefined" && $('billing:use_for_shipping_no')) {
//            $('billing:use_for_shipping_no').observe('click', function() {
//                setBranchShippingData();
//            });
//        }
//
//        if (typeof($('billing:use_for_shipping_yes')) != "undefined" && $('billing:use_for_shipping_yes')) {
//            $('billing:use_for_shipping_yes').observe('click', function() {
//                setBranchBillingData();
//            });
//        }       
//
//        if (typeof($('branchpickupshipping')) != "undefined" && $('branchpickupshipping')) {
//            $('branchpickupshipping').observe('click', function() {
//                setBranchShippingData();
//            });
//        }  
//
//        if (typeof($('normalshipping')) != "undefined" && $('normalshipping')) {
//            $('normalshipping').observe('click', function() {
//                setBranchBillingData();
//            });
//        } 
//    }
});

function checkbillingdata() {
    if (typeof($('billing:use_for_shipping_yes')) != 'undefined' && ($('billing:use_for_shipping_yes') != null) &&
        typeof($('billing-address-select')) != 'undefined' && ($('billing-address-select') != null)) {
        if (($('billing:use_for_shipping_yes')) && ($('billing:use_for_shipping_yes').checked) && ($(
                'billing-address-select').value != '')) {
            cartChooseaddress.changeBillingAdresss($('billing-address-select').value);
        } else if ($('billing-address-select').value == '' && typeof($('billing:use_for_shipping_yes')) != 'undefined' &&
            ($('billing:use_for_shipping_yes') != null) && ($('billing:use_for_shipping_yes').checked)) {
            //send new address details
            cartChooseaddress.submitBillingInformation($(billing.form).serialize());
        }
        else {
            billing.save();
        }
    } else if (typeof($('billing:use_for_shipping_yes')) != 'undefined' && ($('billing:use_for_shipping_yes') != null) && ($('billing-address-select') == null)) {
        //IF billing address select is not present
        if($('billing:use_for_shipping_yes').checked){
            cartChooseaddress.submitBillingInformation($(billing.form).serialize());
        } else {
            billing.save();
        }
    } else {
        billing.save();
    }
}

function checkshippingdata() {
    var checkbranchpicks = cartChooseaddress.checkbranchpickupresent();
    if (checkbranchpicks == 'normalflow') {
        if (typeof($('shipping:same_as_billing')) != "undefined" && $('shipping:same_as_billing') && typeof($(
                'shipping-address-select')) != 'undefined' && ($('shipping-address-select') != null)) {
            if ((!$('shipping:same_as_billing').checked) && ($('shipping-address-select').value != '')) {
                cartChooseaddress.changeShippingAdresss($('shipping-address-select').value)
            } else if ((!$('shipping:same_as_billing').checked) && ($('shipping-address-select').value == '')) {
                cartChooseaddress.submitShippingInformation($(shipping.form).serialize());
            } else if (($('shipping:same_as_billing').checked) && ($('shipping-address-select').value == '')) {
                if ($('billing-address-select').value == '') {
                    cartChooseaddress.submitBillingInShippingInformation($(billing.form).serialize());
                }
                if ($('billing-address-select').value != '') {
                    cartChooseaddress.changeShippingAdresss($('billing-address-select').value);
                }
            } else if (($('shipping:same_as_billing').checked) && ($('shipping-address-select').value != '')) {
                if ($('billing-address-select').value == '') {
                    cartChooseaddress.submitBillingInShippingInformation($(billing.form).serialize());
                }
                if ($('billing-address-select').value != '') {
                    cartChooseaddress.changeShippingAdresss($('billing-address-select').value);
                }
            } else {
                shipping.save();
            }
        } else if (typeof($('shipping:same_as_billing')) != "undefined" && $('shipping:same_as_billing') && ($('shipping-address-select') == null)) {
            //If address select drop down is not present 
            if (!$('shipping:same_as_billing').checked) { //If same as billing was not checked  
                cartChooseaddress.submitShippingInformation($(shipping.form).serialize());
            } else if ($('shipping:same_as_billing').checked) { //If same as billing was  checked  
                cartChooseaddress.submitBillingInShippingInformation($(billing.form).serialize());
            } else {
                shipping.save();
            }
        } else {
            shipping.save();
        }
    }
}
//function setBranchShippingData() {
//    var shippingButtons = $('shipping-buttons-container');
//    for (i = j = 0; i < shippingButtons.childNodes.length; i++) {
//        if (shippingButtons.childNodes[i].nodeName == 'BUTTON') {
//            j++;
//            var input = shippingButtons.childNodes[i];
//            input.setAttribute("onclick", "return cartPage.checkoptiondata();");
//        }
//    }
//}
//
//function setBranchBillingData() {
//    var billingButtons = $('billing-buttons-container');
//    for (i = j = 0; i < billingButtons.childNodes.length; i++) {
//        if (billingButtons.childNodes[i].nodeName == 'BUTTON') {
//            j++;
//            var input = billingButtons.childNodes[i];
//            input.setAttribute("onclick", "return checkbillingdata();");
//        }
//    }
//}