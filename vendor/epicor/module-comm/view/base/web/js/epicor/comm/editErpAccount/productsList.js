/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


if(typeof Epicor_ProductSelector == 'undefined') {
    var Epicor_ProductSelector = {};
}

require([
    'jquery',
    'prototype'
], function (jQuery) {
    Epicor_ProductSelector.productSelector = Class.create();
    Epicor_ProductSelector.productSelector.prototype = {
        target: null,
        wrapperId: "selectErpAccountWrapperWindow", //selectProductWrapperWindow
        initialize: function(){
            if(!$('window-overlay'))
                $(document.body).insert('<div id="window-overlay" class="window-overlay" style="display:none;"></div>');

        },
        openpopup:function(newtarget) {

            this.target = newtarget;
            if($(this.wrapperId))
                $(this.wrapperId).remove();
            // create Popup Wrapper
            var wrappingDiv = new Element('div');
            wrappingDiv.id = this.wrapperId;


            $(document.body).insert(wrappingDiv);

            $(this.wrapperId).hide();
            var website = 0;
            $$('select#_accountwebsite_id option').each(function(o){				// id = messages1
                if(o.selected == true){
                    website = o.value;
                }
            })
            this.ajaxRequest = new Ajax.Request(productGridUrl,{
                method: 'post',
                parameters: { field_id:newtarget, website:website},
                onComplete: function(request){
                    this.ajaxRequest = false;
                }.bind(this),
                onSuccess: function(request){
                    jQuery('.loading-mask').hide();
                    $(this.wrapperId).insert(request.responseText);
                    $(this.wrapperId).show();
                    $('window-overlay').show();

                    this.updateWrapper();

                }.bind(this),
                onFailure: function(request){
                    alert('Error occured loading products grid');
                    this.closepopup();
                }.bind(this),
                onException: function(request,e){
                    alert('Error occured loading products grid');
                    this.closepopup();
                }.bind(this)
            });

        },
        closepopup:function() {
            $(this.wrapperId).remove();
            $('window-overlay').hide();
        },
        selectErpAccount: function(grid, event) {
            if(typeof event != 'undefined') {
                var row = Event.findElement(event, 'tr');
                var erpaccount_id = row.title;
                var erpaccount_name = row.select('td')[0].innerHTML;
                $(this.target).value = erpaccount_id;
                $(this.target+'_name').innerHTML = erpaccount_name;
                this.closepopup();
            }
        },
        removeProduct: function(target) {
            $(target).value = '';
            $(target+'_name').innerHTML = 'No Product Selected';
        },
        updateWrapper: function() {
            if($(this.wrapperId)) {
                var height = 20;

                $$('#'+this.wrapperId +' > *').each(function(item){
                    height += item.getHeight();
                });

                if(height > ($(document.viewport).getHeight()-40))
                    height = $(document.viewport).getHeight()-40;

                if(height < 35)
                    height = 35;

                $(this.wrapperId).setStyle({
                    'height':height+'px',
                    'marginTop': '-'+(height/2)+'px'
                });
            }
        }
    };

    var productSelector = 'test';
    document.observe('dom:loaded', function() {
        productSelector = new Epicor_ProductSelector.productSelector();

        Event.observe(window, "resize", function() { productSelector.updateWrapper();} );
        window.productSelector = productSelector;
    });

    window.onkeypress = function (event) {
        if (event.which == 13 || event.keyCode == 13) {
            return false;
        }
        return true;
    };
});