/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


if(typeof Epicor_Lists == 'undefined') {
    var Epicor_Lists = {};
}
Epicor_Lists.analyse = Class.create();
Epicor_Lists.analyse.prototype = {
    wrapperId: "selectErpAccountWrapperWindow",
    initialize: function(){
        if(!$('window-overlay'))
            $(document.body).insert('<div id="window-overlay" class="window-overlay" style="display:none;"></div>');
    },
    openpopup: function(row) {
        var data = row.up('tr').select('.data')[0].value;
        
        if ($(this.wrapperId))
            $(this.wrapperId).remove();
        var wrappingDiv = new Element('div');
        wrappingDiv.id = this.wrapperId;
        $(document.body).insert(wrappingDiv);
        
        $(this.wrapperId).hide();
        this.ajaxRequest = new Ajax.Request(analyseListProductsGridUrl,{
            method: 'post',
            parameters: {data: data},
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
                alert('Error occured loading products grid');
                this.closepopup();
            }.bind(this),
            onException: function(request,e){
                alert('Error occured loading products grid');
                this.closepopup();
            }.bind(this)
        });
        
    },
    
     openpopupallproducts: function(row) {
        var data = row.up('tr').select('.data')[0].value;
        
        if ($(this.wrapperId))
            $(this.wrapperId).remove();
        var wrappingDiv = new Element('div');
        wrappingDiv.id = this.wrapperId;
        $(document.body).insert(wrappingDiv);
        
        $(this.wrapperId).hide();
        this.ajaxRequest = new Ajax.Request(analyseListAllProductsGridUrl,{
            method: 'post',
            parameters: {data: data},
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

var listsAnalyse = '';
document.observe('dom:loaded', function() { 
    listsAnalyse = new Epicor_Lists.analyse();
    Event.observe(window, "resize", function() { listsAnalyse.updateWrapper();} );
});