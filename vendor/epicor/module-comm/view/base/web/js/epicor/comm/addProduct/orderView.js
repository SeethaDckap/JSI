/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */



if(typeof Solarsoft_Comm == 'undefined') {
    var Solarsoft_Comm = {};
}
Solarsoft_Comm.addProduct = Class.create();
Solarsoft_Comm.addProduct.prototype = {
    
    initialize: function(){
        this.ajaxRequest = false;
        if(showAddProductBtn) {
            addButton = new Element('button');
            addButton.id = "addProductsButton";
            addButton.innerHTML = "<span>Add Products</span>";
            addButton.toggleClassName('scalable add');
            addButton.setStyle({
                'float':'right'
            });
            addButton.observe('click', this.openpopup);
            $('sales_order_view_tabs_order_info_content').select('.head-products')[0].up().insert(addButton);

            $(document.body).insert('<div id="window-overlay" class="window-overlay" style="display:none;"></div>');
        }
    },
    openpopup:function(popup) {
        
        
        // create Popup Wrapper
        var wrappingDiv = new Element('div');
        wrappingDiv.id = "addProductWrapperWindow";
                             
            
        $('sales_order_view_tabs_order_info_content').select('.head-products')[0].up().up().next().insert({
            after: wrappingDiv
        });
        $('addProductsButton').hide();
        
        this.ajaxRequest = new Ajax.Request(addProductUrl,{
            method: 'post',
            onComplete: function(request){
                this.ajaxRequest = false;
            }.bind(this),
            onSuccess: function(request){
                $('loading-mask').hide();
                $('addProductWrapperWindow').insert(request.responseText);
            }.bind(this),
            onFailure: function(request){
                alert('Error occured loading products');
            }.bind(this),
            onException: function(request,e){
                alert('Error occured loading products');
            }.bind(this)
        });
        
    },    
    addProducts: function() {        
        this.ajaxRequest = new Ajax.Request(saveProductUrl,{
            method: 'post',
            parameters: {
                products: Object.toJSON(addProductSearchGrid.gridProducts)
                },
            onComplete: function(request){
                this.ajaxRequest = false;
            }.bind(this),
            onSuccess: function(request){
                $('loading-mask').hide();
                var json = request.responseText.evalJSON();
                this.displaySummary(json);
            }.bind(this),
            onFailure: function(request){
                alert('Error occured displaying summary');
            }.bind(this),
            onException: function(request,e){
                alert('Error occured displaying summary');
            }.bind(this)
        });
    }, 
    displaySummary: function(data) {
        var summaryDiv = new Element('div');
        summaryDiv.id = "saveProductSummary";
        summaryDiv.innerHTML = data.html;
        
        $(document.body).insert(summaryDiv);
        $('window-overlay').show();
        var current_height = $('saveProductSummary').getHeight();
        if( current_height > 400) {
            $('saveProductSummary').setStyle({
                'height':'400px',
                'overflowY':'scroll'
            });
        } else {
            $('saveProductSummary').setStyle({
                'marginTop':(current_height / -2)+'px'
            });
        }
    //overflow-y:scroll;
    },
    closeSummary: function() {
        $('saveProductSummary').remove();
        $('window-overlay').hide();
    },
    saveProducts: function() {        
        this.ajaxRequest = new Ajax.Request(saveProductUrl,{
            method: 'post',
            parameters: {
                confirmed: true, 
                products: Object.toJSON(addProductSearchGrid.gridProducts)
                },
            onComplete: function(request){
                this.ajaxRequest = false;
            }.bind(this),
            onSuccess: function(request, json){
                window.location.reload();
            }.bind(this),
            onFailure: function(request){
                alert('Error occured updating order');
            }.bind(this),
            onException: function(request,e){
                alert('Error occured updating order');
            }.bind(this)
        });
    },
   
    closeProductSearch: function() {
        $('addProductWrapperWindow').remove();
        $('addProductsButton').show();
        addProductSearchGrid = new Solarsoft_Comm.addProductSearchGrid();
    }
}
var addProduct;
document.observe('dom:loaded', function() { 
    addProduct = new Solarsoft_Comm.addProduct();
});

function configurableproductAddToCartForm(){
        alert('gets to configurable product add to cart form');
    }