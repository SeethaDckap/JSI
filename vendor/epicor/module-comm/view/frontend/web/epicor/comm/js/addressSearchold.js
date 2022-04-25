/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

//function populateAddressSelect(row, event){
function populateBillingAddressSelect(row, event){
    var trElement = event.findElement('tr');
    var objectFromJson = trElement.select('input[name=details]')[0].value;
    var arrayFromJson = objectFromJson.evalJSON();
//           
    $$('select#billing-address-select option').each(function(o){
           if(o.value == arrayFromJson.id){
               o.selected = true;
           }
    })
    $('billing-address-search-grid').hide();
    $('window-overlay').hide();
    event.stop();
    return false;
}
function populateShippingAddressSelect(row, event){
    var trElement = event.findElement('tr');
    var objectFromJson = trElement.select('input[name=details]')[0].value;
    var arrayFromJson = objectFromJson.evalJSON();
//           
    $$('select#shipping-address-select option').each(function(o){
           if(o.value == arrayFromJson.id){
               o.selected = true;
           }
    })
    $('shipping-address-search-grid').hide();
    $('window-overlay').hide();
    event.stop();
    return false;
}
function openOverlay(id_to_show) {

    $('window-overlay').select('.box-account').each(function(e) {
        e.hide();
    });
    var body = document.body,
    html = document.documentElement;
    var width = Math.max(body.scrollWidth, body.offsetWidth, html.clientWidth, html.scrollWidth, html.offsetWidth);
    var height = Math.max(body.scrollHeight, body.offsetHeight,html.clientHeight, html.scrollHeight, html.offsetHeight);
//    if(!$('window-overlay').down(id_to_show)){
        $('window-overlay').update($(id_to_show));
//    }
    $('window-overlay').setStyle({width: width + 'px', height: height + 'px'});
    $('window-overlay').show();
    var formdiv = $(id_to_show);
    $(id_to_show).down('.box-account').show();
    var elementHeight = $(id_to_show).getHeight();
    var elementWidth = $(id_to_show).getWidth();
    var formwidth = ((html.clientWidth - elementWidth)/2);
    var viewport = document.viewport.getDimensions();   // Gets the viewport as an object literal
    var visibleheight = viewport.height;                       // Usable window height
    var topoffset  = (visibleheight - elementHeight - 50)/2;
        
    $(id_to_show).setStyle({'top':topoffset + 'px', 'left': formwidth + 'px'});
        
    formdiv.show();
    formdiv.scrollTop = 0;

}
function closeBillingAddressPopup(){
    $("billing-address-search-grid").hide();
    $("window-overlay").hide();
} 
function closeShippingAddressPopup(){
    $("shipping-address-search-grid").hide();
    $("window-overlay").hide();
} 


if(typeof Solarsoft == 'undefined') {
    var Solarsoft = {};
}
Solarsoft.addressSearch = Class.create();
Solarsoft.addressSearch.prototype = {
    target: null,
    wrapperId: "selectAddressSearchWrapperWindow",
    initialize: function(){
//        if(!$('window-overlay')){
//            $(document.body).insert('<div id="window-overlay" class="window-overlay" style="display:none;"></div>');
//            alert('gets here');
//        }
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
        
        this.ajaxRequest = new Ajax.Request(billingAddressSearchGridUrl,{
            method: 'post',
            parameters: { field_id:newtarget},
            onComplete: function(request){
                this.ajaxRequest = false;
            }.bind(this),
            onSuccess: function(request){
                $('loading-mask').hide();
                $(this.wrapperId).insert(request.responseText);
                $(this.wrapperId).show();
                $('window-overlay').show();
                
                this.updateWrapper();
                
            }.bind(this),
            onFailure: function(request){
                alert('Error occured loading billing address search grid');
                this.closepopup();
            }.bind(this),
            onException: function(request,e){
                alert('Error occured loading billing address search grid');
                this.closepopup();
            }.bind(this)
        });
        
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

var billingAddressSearch = 'test';
document.observe('dom:loaded', function() { 
    billingAddressSearch = new Solarsoft.addressSearch();
    $('billing-address-search-grid').hide();
    $('billing-address-search-button').observe('click', function(event) {
//        $('billing-address-search-grid').show();
           openOverlay('billing-address-search-grid');
    });
    $('shipping-address-search-grid').hide();
    $('shipping-address-search-button').observe('click', function(event) {
        $('shipping-address-search-grid').show();
           openOverlay('shipping-address-search-grid');
    });
   // Event.observe(window, "resize", function() { billingAddressSearch.updateWrapper();} );
});   