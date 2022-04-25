/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * Quick Add JS
 */

/*
if (!window.Epicor)
    var Epicor = new Object();
*/
if(typeof (currentSkuFieldId) != 'undefined')
    var currentSkuFieldId;

if(typeof Epicor == 'undefined') {
    var Epicor = {};
}


/**
 * Quick Search form client model
 */
require([
    'jquery',
    'Magento_Customer/js/customer-data',
    'mage/cookies',
    'prototype',
     'mage/translate',
], function (jQuery,customerData) {
    Epicor.searchForm = Class.create();
    Epicor.searchForm.prototype = {
        keyDelay: null,
        clickoutHandler: null,
        lastHeight: 0,
        qtyHasFocus: false,
        sku: null ,
        initialize: function (form, field, supergroup, emptyText, uom, packsize, productid, location, qtyfield, configurator) {
            this.form = $(form);
            this.field = $(field);
            this.qtyfield = $(qtyfield);
            this.supergroup = $(supergroup);
            this.emptyText = emptyText;
            this.sku = null;
            if (uom) {
                this.uom = $(uom);
            }

            if (packsize) {
                this.packsize = $(packsize);
            }

            if (productid) {
                this.productid = $(productid);
            }

            if (location) {
                this.location = $(location);
            }

            //Insert default element, when the page was loaded
            this.defaultupdate();

            this.field.setAttribute('autocomplete', 'off');

            Event.observe(this.form, 'submit', this.submit.bind(this));
            Event.observe(this.field, 'keyup', this.fieldUpdated.bind(this));
            Event.observe(this.field, 'focus', this.focus.bind(this));
            Event.observe(this.field, 'blur', this.blur.bind(this));
            Event.observe(this.qtyfield, 'focus', this.qtyFocus.bind(this));
            Event.observe(this.qtyfield, 'blur', this.qtyBlur.bind(this));
            //If there is a change in sku input, then it will clear product Id
            Event.observe(this.field, 'input', this.clearProductId.bind(this));
            this.clickoutHandler = this.clickoutEvent.bind(this);
            this.blur();

            currentSkuFieldId = this.field.identify();
        },
        fieldUpdated: function (event) {
            if (this.field.value.length >= 2) {
                clearTimeout(this.keyDelay);
                this.keyDelay = setTimeout(this.gotoPage.bind(this), 250);
                Event.stop(event);
            } else {
                if (typeof (this.destinationElement) != 'undefined') {
                    this.destinationElement.hide();
                    document.stopObserving('click', this.clickoutHandler);
                }
            }

        },
        clearProductId: function (event) {
            var currentSkuFieldIds   = this.field.identify();
            //clear product ids for home page quick add autosuggest
            if(currentSkuFieldIds    =='qa_sku') {
                var productElement   =  $('qa_product_id');
                productElement.value = "";
            } else {
                //clear product ids for Quick Add customerconnect rfqs add line
                var fields           = currentSkuFieldIds.split(/_/);
                var prefix           = fields[0];
                var productPrefix    = '_product_id_';
                var productPrefixId  = fields[2];
                var createProductId  = prefix + productPrefix + productPrefixId;
                var productElement   = $(createProductId);
                if (typeof(productElement) != 'undefined' && productElement != null){
                    productElement.value = "";
                }
            }
        },
        submit: function (event) {

                var configuratorValue = $('qa_configurator').value;
                var productId = $('qa_product_id').value;
                var qty = $('qa_qty').value;
                var sku = $('qa_sku').value;
                // var currentStoreId = $('currentStoreId').value;
                var currentStoreId = window.checkout.storeId;
                var location =  jQuery('#qa_location [name = "location_code"]').val();

                if (location == undefined){
                    location = '';
                }

                var result;
                if (configuratorValue == '') {

                    var erp_url =  window.location.protocol + '//' + window.location.hostname + '/rest/V1/configuratorCheck';
                    // var productId = this.productId.value;
                    if (!this.productid){
                        var productId = 'AUTOCOMPLETENOTUSED';
                    }else{
                        var productId = this.productid.value;
                    }
                    var data = JSON.stringify({'id' : productId, 'sku' : sku});
                    jQuery.ajax({
                        url: erp_url,
                        type: 'post',
                        data: data,
                        dataType: 'json',
                        contentType: "application/json; charset=utf-8",
                        async: false,
                        context: this,
                        complete: function (request)  {
                        }.bind(this),
                        success: function (request) {
                            //reload messages on this page to display consistently
                            json = request.evalJSON();
                            customerData.reload(['messages'], true);
                            productId = json.productId;
                            if(json.type == 'success'){
                                configuratorValue = json.ecc_configurator;
                            }
                            this.ajaxRequest = false;
                        }.bind(this),
                        fail: function (request) {
                            alert(jQuery.mage.__('Error occured retrieving additional data'));
                        }.bind(this)
                    });

                }

                if (configuratorValue == 1) {
                    var target =  jQuery("#cart-quick-add input[name='target']:checked").val();

                    if (target == 'wishlist') {

                        this.addToWishList(productId, qty);
                        Event.stop(event);
                        return false;
                    }


                    if (location != null || location == '') {
                        ewaProduct.submit({
                            sku: sku,
                            productId: productId,
                            currentStoreId: currentStoreId,
                            location: location,
                            qty: qty,
                            target: target,
                        }, false);
                    } else {
                        ewaProduct.submit({
                            sku: sku,
                            productId: productId,
                            currentStoreId: currentStoreId,
                            qty: qty,
                            target: target
                        }, false);
                    }
                    Event.stop(event);
                    return false;
                }

                if (this.field.value == this.emptyText || this.field.value == '') {
                    Event.stop(event);
                    return false;
                }
                return true;
        },
        focus: function (event) {
            if (this.field.value == this.emptyText) {
                this.field.value = '';
            }
            this.qtyHasFocus = false;
            currentSkuFieldId = this.field.identify();
        },
        qtyFocus: function (event) {
            this.qtyHasFocus = true;
            if (typeof (this.destinationElement) != 'undefined') {
                this.destinationElement.hide();
                document.stopObserving('click', this.clickoutHandler);
            }
        },
        blur: function (event) {

            // clear the existing qa_configurator value
            if ($('qa_configurator') != null) {
                $('qa_configurator').value = null;
            }

            var currentSkuFieldId = this.field.identify();
            if($('qa_sku_locations_on') && $(currentSkuFieldId).value != '') {
                if(($('last_searched_sku') == null || $('last_searched_sku').value != $(currentSkuFieldId).value)){
                    var sku = $(currentSkuFieldId).value;
                    var url = $('qa_sku_locations_on').value;
                    url = url + (url.match(new RegExp('\\?')) ? '&isAjax=true' : '?isAjax=true');
                jQuery.ajax({
                    url: url,
                    data: {
                         'sku': sku,                 
                         'form_key': jQuery.mage.cookies.get('form_key'),
                    },
                    type: 'post',
                    dataType: 'json',
                    context: this,
                    complete: function (request)  {
                        var json = request.responseText.evalJSON();
                        if(json.message == 'success'){
                            var locations = json.locations;
                            sku = $(currentSkuFieldId).value;
                            this.locations(sku, locations);
                            if (this.productid) {
                                this.productid.value = json.productid;
                            }
                        }
                        if(json.message == 'noproduct' && jQuery("#qa_location").length > 0){
                            $('qa_location').update().hide();
                            if (this.productid) {
                                this.productid.value = '';
                            }
                        }
                        if (this.supergroup) {
                            this.supergroup.value = '';
                        }

                    }.bind(this),
                    success: function (request) {
                        this.ajaxRequest = false;
                    }.bind(this),
                    fail: function (request) {
                               alert(jQuery.mage.__('Error occured retrieving additional data'));
                            }.bind(this)
                });
            
            
                }
            }


        },
        qtyBlur: function (event) {
            this.qtyHasFocus = false;
        },
        gotoPage: function (event) {
            var page = 1;
            if (event) {
                var button = Event.findElement(event, 'button');
                page = button.value;
            }
            var page_data = null;
            if (page > 1 && this.destinationElement.select('#qa_page_data').length > 0) {
                page_data = this.destinationElement.select('#qa_page_data')[0].value;
            } else {
                this.destinationElement.update('');
                this.lastHeight = 0;
            }

            if (this.field.value == '') {
                if (typeof (this.destinationElement) != 'undefined') {
                    this.destinationElement.hide();
                    document.stopObserving('click', this.clickoutHandler);
                }
                return;
            }


            jQuery.ajax({
                url: this.url,
                data: {
                    'qa_page': page,
                    'qa_page_data': page_data,                   
                    'form_key': jQuery.mage.cookies.get('form_key'),
                    'sku': this.field.value,
                },
                type: 'post',
                dataType: 'json',
                context: this,
                complete: function (request)  {
                    document.stopObserving('click', this.clickoutHandler);

                    if (this.qtyHasFocus == true || currentSkuFieldId != this.field.identify()){
                        return;
                    }

                    document.observe('click', this.clickoutHandler);
                    // copy existing list items to a temp container
                    // This is due to an issue in IE where it loses the text on the li elements
                    // if you remove them from the DOM before using them. (god only knows why)
                    var existingListItems = this.destinationElement.select('ul li');
                    var tmpContainer = null;

                    if (existingListItems.length > 0 && page > 1) {
                        tmpContainer = new Element('ul');
                        existingListItems.each(function (item) {
                            tmpContainer.insert(item);
                        }.bind(this));
                    }

                    // update the autofill with the new results
                    var result = request.responseText;
                    if (result.search("<li") >= 0) {
                        this.destinationElement.update(result).show();
                    }

                    //grab the list container
                    var listContainer = this.destinationElement.select('ul')[0];

                    if (listContainer == undefined) {
                        return;
                    }

                    // set container to vertical scroll, as if we don't the height we get back next will be wrong
                    listContainer.setStyle({
                        overflowY: 'scroll'
                    });

                    // get the last largest hieght eg if the last page has only 1 item you
                    // don't what you box showing 5 items to shrink to show only 1
                    // Used getLayout as that does not include border thickness and stuff unlike getHeight()
                    var listContainerHeight = listContainer.getLayout().get('height');
                    if (listContainerHeight > this.lastHeight) {
                        this.lastHeight = listContainerHeight;
                    }
                    // fix the list container hieght and enable vertical scrolling
                    listContainer.setStyle({
                        height: this.lastHeight + 'px',
                    });

                    // add the existing items back into the list container
                    // at the top if there were any
                    if (tmpContainer) {
                        var tmpExistingListItems = tmpContainer.select('li');
                        if (tmpExistingListItems.length > 0) {
                            tmpExistingListItems.reverse().each(function (item) {
                                listContainer.insert({top: item});
                            }.bind(this));
                        }
                    }
                    // force scroll the list container to the bottom
                    this.destinationElement.select('ul')[0].scrollTop = 500000000;


                    this.destinationElement.select('ul li').each(function (item) {
                        item.observe('mouseover', function () {
                            item.addClassName('selected');
                        });
                        item.observe('mouseout', function () {
                            item.removeClassName('selected');
                        });
                        Event.observe(item, 'mousedown', this.selectProduct.bind(this));
                    }.bind(this));

                    this.destinationElement.select('button').each(function (item) {
                        Event.observe(item, 'click', this.gotoPage.bind(this));
                    }.bind(this));

                    this.field.focus();

                }.bind(this),
                success: function (request) {
                    this.ajaxRequest = false;
                }.bind(this),
                fail: function (request) {
                           alert(jQuery.mage.__('Error occured retrieving additional data'));
                        }.bind(this)
            });
            
        },
        clickoutEvent: function (e, el) {
            if (!e.target.descendantOf(this.destinationElement)) {
                this.destinationElement.hide();
                document.stopObserving('click', this.clickoutHandler);
            }
        },
        initAutocomplete: function (url, destinationElement) {
            this.url = url;
            this.destinationElement = $(destinationElement);
        },
        selectProduct: function (event) {
            var element = Event.findElement(event, 'li');
            if (element.title) {
                this.field.value = element.title;
                
                var decimalPlaces = element.readAttribute('decimal');
                if (decimalPlaces != "") {
                    var dataValidate = '{"validatedecimalplace":' + decimalPlaces + '}';
                    this.qtyfield.setAttribute('data-validate', dataValidate);
                }
                var id = element.readAttribute('id');

                if (id != undefined && id.indexOf('super_group') != -1) {
                    if (this.supergroup) {
                        this.supergroup.value = id.replace('super_group_', '');
                    }
                    if (this.uom) {
                        this.uom.value = element.readAttribute('data-uom');
                    }

                    if (this.productid) {
                        this.productid.value = element.readAttribute('data-parent');
                    }
                    if (this.packsize) {
                        this.packsize.down('.packsize').update(element.readAttribute('data-pack'));
                        this.packsize.show();
                    }

                } else {

                    if (id != undefined && this.productid) {
                        this.productid.value = id;
                    }

                    if (this.supergroup) {
                        this.supergroup.value = '';
                    }

                    if (this.uom) {
                        this.uom.value = '';
                    }

                    if (this.packsize) {
                        this.packsize.down('.packsize').update('');
                        this.packsize.hide();
                    }
                }


                if (this.location) {
                    var locations = element.readAttribute('data-locations').evalJSON();
                    this.locations(element.title, locations);
                }

            }
            if($$('#quickadd_autocomplete ul').length > 0){
                $('quickadd_autocomplete').select('ul').invoke('remove');
            }
            this.qtyfield.focus();
            this.destinationElement.hide();
            document.stopObserving('mousedown', this.clickoutHandler);
            Event.stop(event);
        },
        locations: function (sku, locations) {
            if (locations) {
                if (jQuery("#qa_location").length > 0) {
                    $('qa_location').update();
                }
                var loc_html = '';
                var loc_html = '<label class="required" for="qty"><em>*</em>Location</label>';
                var loc_html = loc_html + '<div class="location_input">';
                if (locations.length > 1) {
                    loc_html = loc_html + '<select name="location_code">';

                    for (var i = 0; i < locations.length; i++) {
                        loc_html = loc_html + '<option value="' + locations[i].code + '">' + locations[i].name + '</option>';
                    }

                    loc_html = loc_html + '</select>';
                } else {
                    loc_html = loc_html + '<input type="hidden" name="location_code" value="' + locations[0].code + '">' + locations[0].name;
                }
                loc_html = loc_html + '<input id="last_searched_sku" type="hidden" value="' + sku + '">';
                loc_html = loc_html + '</div>';
                if (jQuery("#qa_location").length > 0) {
                    $('qa_location').update(loc_html);
                    $('qa_location').show();
                }
            }
        },
        defaultupdate: function () {
            var qaLocation = $('qa_location');
            if(qaLocation) {
                var def_html = '';
                var def_html = def_html + '<div class="location_input">';
                def_html = def_html + '<input type="hidden" name="location_code" value="">';
                def_html = def_html + '<input id="last_searched_sku" type="hidden" value="">';
                def_html = def_html + '</div>';
                if (jQuery("#qa_location").length > 0) {
                    $('qa_location').update(def_html);
                    $('qa_location').hide();
                }
            }
        },
        addToWishList: function(productId, qty){
            if (qty == 0){
                qty = 1;
            }
            var url =  window.location.protocol + '//' + window.location.hostname + '/rest/V1/addToWishlist';
            var data = JSON.stringify({'id' : productId, 'qty' : qty});
            jQuery.ajax({
                url: url,
                data: data,
                type: 'post',
                dataType: 'json',
                contentType: "application/json; charset=utf-8",
                async: false,
                context: this,
                complete: function (request)  {
                }.bind(this),
                success: function (request) {
                    this.ajaxRequest = false;
                }.bind(this),
                fail: function (request) {
                    alert(jQuery.mage.__('Error occured retrieving additional data'));
                }.bind(this)
            });
        },
    }
    var option_selected = false;
    return Epicor.searchForm;

});

