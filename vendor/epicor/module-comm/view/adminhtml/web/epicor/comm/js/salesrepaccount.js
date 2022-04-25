/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


if(typeof Solarsoft == 'undefined') {
    var Solarsoft = {};
}
Solarsoft.salesrepaccount = Class.create();
Solarsoft.salesrepaccount.prototype = {
    target: null,
    wrapperId: "selectErpAccountWrapperWindow",//"selectSalesRepAccountWrapperWindow",
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
        this.ajaxRequest = new Ajax.Request(salesRepAccountGridUrl,{
            method: 'post',
            parameters: { field_id:newtarget, website:website},
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
                alert('Error occured loading Sales Rep Accounts grid');
                this.closepopup();
            }.bind(this),
            onException: function(request,e){
                alert('Error occured loading Sales Rep Accounts grid');
                this.closepopup();
            }.bind(this)
        });
        
    },
    closepopup:function() {
        $(this.wrapperId).remove();
        $('window-overlay').hide();
    },    
    selectSalesRepAccount: function(grid, event) {
        if(typeof event != 'undefined') {
            var row = Event.findElement(event, 'tr');
            var salesrepaccount_id = row.title;
            var salesrepaccount_name = row.select('td.last')[0].innerHTML;
            $(this.target).value = salesrepaccount_id;
            $(this.target+'_name').innerHTML = salesrepaccount_name;
            this.closepopup();
        }
    },
    removeSalesRepAccount: function(target) {
        $(target).value = '';
        $(target+'_name').innerHTML = salesRepNotSelectedLabel;
    },
    updateWrapper: function() {
        if($(this.wrapperId)) {
            var height = 200;
        
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

var salesRepAccountSelector = 'test';
document.observe('dom:loaded', function() { 
    salesRepAccountSelector = new Solarsoft.salesrepaccount();
    
   
        Event.observe(window, "resize", function() { salesRepAccountSelector.updateWrapper();} );
});