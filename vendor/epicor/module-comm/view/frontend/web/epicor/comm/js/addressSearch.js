/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

if (typeof Solarsoft == 'undefined') {
    var Solarsoft = {};
}

function populateBillingAddressSelect(row, event) {
    var trElement = event.findElement('tr');
    var objectFromJson = trElement.select('input[name=details]')[0].value;
    var arrayFromJson = objectFromJson.evalJSON();
    //           
    $$('select#billing-address-select option').each(function(o) {
        if (o.value == arrayFromJson.id) {
            o.selected = true;
        }
    });
    $('window-overlay').hide();
    event.stop();
    return false;
}
function populateShippingAddressSelect(row, event) {
    var trElement = event.findElement('tr');
    var objectFromJson = trElement.select('input[name=details]')[0].value;
    var arrayFromJson = objectFromJson.evalJSON();
    //           
    $$('select#shipping-address-select option').each(function(o) {
        if (o.value == arrayFromJson.id) {
            o.selected = true;
        }
    });
    $('window-overlay').hide();
    event.stop();
    return false;
}

Solarsoft.checkoutAddressSearch = Class.create();
Solarsoft.checkoutAddressSearch.prototype = {
    target: null,
    wrapperId: "selectCheckoutAddressSearchWrapperWindow",
    height: 0,
    boxAccountPaddingHeight: 0,
    boxAccountPaddingBottom: 0,
    initialize: function() {
//        if(!$('window-overlay')){
//            $(document.body).insert('<div id="window-overlay" class="window-overlay" style="display:none;"></div>');
//        }
    },
    openpopup: function(newtarget, path, search) {

        this.target = newtarget;
        this.path = path;

        if ($(this.wrapperId))
            $(this.wrapperId).remove();
        // create Popup Wrapper
        var wrappingDiv = new Element('div');
        wrappingDiv.id = this.wrapperId;


        $(document.body).insert(wrappingDiv);
        $('loading-mask').show();
        $(this.wrapperId).hide();
        baseurl = location.protocol + "//" + location.hostname;
        url = baseurl + '/comm/onepage/' + path;
        url = url + (url.match(new RegExp('\\?')) ? '&isAjax=true' : '?isAjax=true');
        this.ajaxRequest = new Ajax.Request(url, {
            method: 'post',
            onComplete: function(request) {
                this.ajaxRequest = false;
            }.bind(this),
            onSuccess: function(request) {
                $(this.wrapperId).insert(request.responseText);
                $('window-overlay').insert($(this.wrapperId));
                $(this.wrapperId).show();
                $('window-overlay').show();
                this.height = $(this.wrapperId).getHeight();
                $('loading-mask').hide();
                this.updateWrapper();


            }.bind(this),
            onFailure: function(request) {
                alert('Error occured loading billing address search grid');
                this.closepopup();
            }.bind(this),
            onException: function(request, e) {
                alert('Error occured loading billing address search grid');
                this.closepopup();
            }.bind(this)
        });
    },
    updateWrapper: function() {
        var availableHeight = $(document.viewport).getHeight();
        var elementHeight = 0;
        if (this.height) {
            if (this.height < availableHeight * .6) {
                elementHeight = availableHeight * .6;     //  make display always at least 60% of available height
            } else {
                elementHeight = this.height;
            }
        } else {
            elementHeight = parseInt(availableHeight * .8);
        }
        var elementWidth = $(this.wrapperId).getWidth();
        $(this.wrapperId).select('.box-account').each(function(z) {
            layout = new Element.Layout(z);
            this.boxAccountPaddingHeight = layout.get('padding-top');
            this.boxAccountPaddingBottom = layout.get('padding-bottom');
            elementHeight += this.boxAccountPaddingHeight;
        });
        $(this.wrapperId).setStyle({'height': elementHeight + 'px'});          // reset wrapper height if changed above
        elementWidth = $(document.viewport).getWidth() * .8;
        var availableWidth = $(document.viewport).getWidth();
        if ((availableWidth - elementWidth) < 0) {
            var left = 0;
        } else {
            var left = (availableWidth - elementWidth) / 2;
        }
        if ((availableHeight - elementHeight) < 0) {
            var top = 20;
        } else {
            var top = (availableHeight - elementHeight) / 2;
        }

        if ($(this.wrapperId)) {
            var height = 22;
            $$('#' + this.wrapperId).each(function(item) {
                height += item.getHeight();
            });

            if (height > ($(document.viewport).getHeight() - 40))
                height = $(document.viewport).getHeight() - 40;

            if (height < 35) {
                height = 35;
                top:0;
            }
            $(this.wrapperId).setStyle({
                'height': height + 'px',
                'width': elementWidth + 'px',
                'marginTop': top + 'px',
                'marginLeft': left + 'px',
            });
        }

    },
    closepopup: function() {
        $(this.wrapperId).remove();
        $('window-overlay').hide();
    },
};
var onepageAddressSearch = new Solarsoft.checkoutAddressSearch();
document.observe('dom:loaded', function() {
    var onepageAddressSearch = new Solarsoft.checkoutAddressSearch();
    if ($('billing-address-search-button')) {
        $('billing-address-search-button').observe('click', function(event) {
            onepageAddressSearch.openpopup('billing-address-search-grid', 'billingpopup');
        });
    }

    if ($('shipping-address-search-button')) {
        $('shipping-address-search-button').observe('click', function(event) {
            onepageAddressSearch.openpopup('shipping-address-search-grid', 'shippingpopup');
        });
    }

    if ($('billing-address-search-button') || $('shipping-address-search-button')) {
        Event.observe(window, "resize", function() {
            onepageAddressSearch.updateWrapper();
        });
    }
});
