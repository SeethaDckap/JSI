/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */



if(typeof Solarsoft_Comm == 'undefined') {
    var Solarsoft_Comm = {};
}
Solarsoft_Comm.addProductSearchGrid = Class.create();
Solarsoft_Comm.addProductSearchGrid.prototype = {
    
    initialize: function(){
        this.gridProducts   = $H({});
    },
 
    productGridRowInit: function (grid, row) {
        var checkbox = $(row).select('input[type=checkbox]')[0];
        var customPriceCheckbox = $(row).select('input[type=checkbox]')[1];
        var customPrice = $(row).select('input[class=custom_price]')[0];
        var origPrice = $(row).select('input[class=orig_price]')[0];
        var qty = $(row).select('input[name=qty]')[0];
        
        row.checkboxElement = checkbox;
        row.customPriceCheckboxElement = customPriceCheckbox;
        row.customPriceElement = customPrice;
        row.origPriceElement = origPrice;
        row.qtyElement = qty;
        
        var inputs = $(row).select('input[type="text"]');
        if (checkbox && inputs.length > 0) {
            checkbox.inputElements = inputs;
            
            checkbox.customPriceCheckboxElement = customPriceCheckbox;
            checkbox.customPriceElement = customPrice;
            checkbox.origPriceElement = origPrice;
            checkbox.qtyElement = qty;
            
            for (var i = 0; i < inputs.length; i++) {
                var input = inputs[i];
                input.checkboxElement = checkbox;
                input.customPriceElement = customPrice;
                input.qtyElement = qty;
    
                var product = this.gridProducts.get(checkbox.value);
                if (product) {
                    var defaultValue = product[input.name];
                    if (defaultValue) {
                        if(input.name == 'custom_price' && input.value != defaultValue) {
                            customPriceCheckbox.checked = true;
                            customPrice.show();
                        }
                            
                        input.value = defaultValue;
                    }
                }
    
                input.disabled = !checkbox.checked || input.hasClassName('input-inactive');
    
                Event.observe(input,'keyup',this.productGridRowInputChange.bind(this));
                Event.observe(input,'change',this.productGridRowInputChange.bind(this));
            }
        }
    },
 
    productGridRowClick: function (grid, event) {
        var trElement = Event.findElement(event, 'tr');
        var selectedElement = trElement.checkboxElement;
        var customPriceCheckboxElement = trElement.customPriceCheckboxElement;
        var qtyElement = trElement.qtyElement;
        var customPriceElement = trElement.customPriceElement;
        var origPriceElement = trElement.origPriceElement;
        
        var eventElement = Event.element(event);
        if (trElement && eventElement != qtyElement && eventElement != customPriceElement) {
            if (selectedElement) {
                // processing non composite product
                var checked = eventElement.type != 'checkbox' ? !selectedElement.checked : selectedElement.checked;
                if(!selectedElement.checked && customPriceCheckboxElement.checked && selectedElement != event.target )
                    grid.setCheckboxChecked(selectedElement, true);            
                else {
                    grid.setCheckboxChecked(selectedElement, checked);
                    if(!checked) {
                        customPriceCheckboxElement.checked = false;
                        customPriceElement.hide();
                        
                    }
                }
                
                if(!customPriceCheckboxElement.checked) {
                    customPriceElement.value = origPriceElement.value
                    customPriceElement.hide();
                    if (selectedElement.checked) {
                        this.gridProducts.get(selectedElement.value)['custom_price'] = customPriceElement.value;
                    }
                }
                else 
                    customPriceElement.show();
            }
        }
    },
 
    productGridRowInputChange: function (event) {
        var element = Event.element(event);
        var custom_price = element.customPriceElement;
        var qty = element.qtyElement;
        
        if (element && element.checkboxElement){
            if (element.checkboxElement.checked) {
                this.gridProducts.get(element.checkboxElement.value)['qty'] = qty.value;
                this.gridProducts.get(element.checkboxElement.value)['custom_price'] = custom_price.value;
            } else if (this.gridProducts.get(element.checkboxElement.value)) {
                delete(this.gridProducts.get(element.checkboxElement.value));
            }
        }
    },
 
    productGridCheckboxCheck: function (grid, element, checked) {  
        //console.log(element.inputElements);
        //input.disabled = !checkbox.checked || input.hasClassName('input-inactive')
        if (checked) {
            if(element.inputElements) {
                this.gridProducts.set(element.value, {});
                var product = this.gridProducts.get(element.value);
                for (var i = 0; i < element.inputElements.length; i++) {
                    var input = element.inputElements[i];
                    if (!input.hasClassName('input-inactive')) {
                        input.disabled = false;
                        if (input.name == 'qty' && !input.value) {
                            input.value = 1;
                        }
                    }
                    
                    this.gridProducts.get(element.value)['qty'] = element.qtyElement.value;
                    this.gridProducts.get(element.value)['custom_price'] = element.customPriceElement.value;
                }
            }
        } else {
            if(element.inputElements){
                for(var i = 0; i < element.inputElements.length; i++) {
                    element.inputElements[i].disabled = true;
                }
            }
            this.gridProducts.unset(element.value);
        }
        
        grid.reloadParams = {
            'products[]':this.gridProducts.keys()
        };
    }
}
var addProductSearchGrid = new Solarsoft_Comm.addProductSearchGrid();
//document.observe('dom:loaded', function() { 
//    addProductSearchGrid = new Solarsoft_Comm.addProductSearchGrid();
//});