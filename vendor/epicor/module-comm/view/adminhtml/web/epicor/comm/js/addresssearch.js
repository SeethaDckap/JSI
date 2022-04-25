/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


if(typeof Solarsoft == 'undefined') {
    var Solarsoft = {};
}
Solarsoft.addresssearch = Class.create();
Solarsoft.addresssearch.prototype = {
    target: null,
    wrapperId: "selectAddressSearchWrapperWindow",
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
        
        this.ajaxRequest = new Ajax.Request(addressSearchGridUrl,{
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
                alert('Error occured loading erp accounts grid');
                this.closepopup();
            }.bind(this),
            onException: function(request,e){
                alert('Error occured loading erp accounts grid');
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
            var addresssearch_id = row.title;
            var addresssearch_name = row.select('td.last')[0].innerHTML;
            $(this.target).value = addresssearch_id;
            $(this.target+'_name').innerHTML = addresssearch_name;
            this.closepopup();
        }
    },
    removeAddress: function(target) {
        $(target).value = '';
        $(target+'_name').innerHTML = 'No Address Selected';
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

var erpAccountSelector = 'test';
document.observe('dom:loaded', function() { 
    erpAccountSelector = new Solarsoft.addresssearch();
    
   
        Event.observe(window, "resize", function() { erpAccountSelector.updateWrapper();} );
});