/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


if (typeof Epicor_SalesRepDiscounts == 'undefined') {
    var Epicor_SalesRepDiscounts = {};
}

Epicor_SalesRepDiscounts.discounts = Class.create();
Epicor_SalesRepDiscounts.discounts.prototype = {
    default: {
            symbol: '$',
            precision: 2,
            thousandsSep: ',',
            zerosSep: '.',
            symbolPosition: 'left',
            space: false
    },
    properties: {},
    elements: {},
    
    initialize: function (properties) {
        this.setProperties(properties);
        containers = document.getElementsByClassName('salesrep-discount-container');
        for (var i = 0; i < containers.length; i++) {
            var id = containers[i].id;
            
            var priceField = containers[i].getElementsByClassName('price')[0];
            var discountField = containers[i].getElementsByClassName('discount')[0];
            
            if(priceField !== undefined && discountField !== undefined){
                
                this.elements[id] = {
                    basePrice: this.toNumber(priceField.getAttribute('base-value')),
                    minPrice: this.toNumber(priceField.getAttribute('min-value')),
                    actualPrice: this.toNumber(priceField.value),
                    maxDiscount: this.toNumber(discountField.getAttribute('max-value')) /100,
                    actualDiscount: this.toNumber(discountField.getAttribute('value')) /100
                };
                priceField.observe('blur', function (e) {
                    discountSelector.validatePrice(e.srcElement);
                });
                discountField.observe('blur', function (e) {
                    discountSelector.validateDiscount(e.srcElement);
                });
            }
        }
    },
    setProperties: function(properties){
        this.properties = properties;
        for(var property in this.default){
            if(this.properties[property] === undefined){
                this.setProperty(property, this.default[property]);
            }
        }
    },
    setProperty: function(property, value){
        this.properties[property] = value;
    },
    toNumber: function(number){
        if(number !== null && number !== undefined){
            var zerosSep = this.properties.zerosSep;
            var regExp = new RegExp('(?!^-)[^0-9'+zerosSep+']','g');
            return Number(number.replace(regExp,''));
        }else{
            return 0;
        }
    },
    validatePrice: function (element){
        var parent = element.parentElement;
        var id = parent.id;
        var discountField = parent.getElementsByClassName('discount')[0];
        var price = this.toNumber(element.value);
        var minPrice = this.elements[id].minPrice;
        var maxDiscount = this.elements[id].maxDiscount;
        var warn = false;
        
        if(price < minPrice){
            warn = true;
            price = minPrice;
        }
        
        discount = this.calculateDiscount(price,this.elements[id].basePrice);
        
        if(discount > maxDiscount){
            warn = true;
            discount = maxDiscount;
            price = this.calculatePrice(discount,this.elements[id].basePrice);
        }
        
        if(warn){
            alert('Price value cannot be lower than designated');
        }
        
        element.value = this.toCurrency(price);
        discountField.value = this.toPercent(discount);
    },
    validateDiscount: function (element){
        var parent = element.parentElement;
        var id = parent.id;
        var priceField = parent.getElementsByClassName('price')[0];
        var discount = this.toNumber(element.value).toFixed(2) / 100;
        var maxDiscount = this.elements[id].maxDiscount;
        var minPrice = this.elements[id].minPrice;
        var warn = false;
        
        if(discount > maxDiscount){
            warn = true;
            discount = maxDiscount;  
        }
        
        price = this.calculatePrice(discount,this.elements[id].basePrice);
        
        if(price < minPrice){
            warn = true;
            price = minPrice;
            discount = this.calculateDiscount(price,this.elements[id].basePrice);
        }
        
        if(warn){
            alert('Discount value cannot be greater than designated');
        }
        
        element.value = this.toPercent(discount);
        priceField.value = this.toCurrency(price);
    },
    calculateDiscount: function(discountedPrice, originalPrice){
        return 1 - (discountedPrice/originalPrice);
    },
    calculatePrice: function(discount, originalPrice){
        return originalPrice * (1 - discount) ;
    },
    toCurrency: function(number){
        var sign = number < 0 ? "-" : "";
        var i = parseInt(number = Math.abs(+number || 0).toFixed(this.properties.precision)) + "";
        var j = (j = i.length) > 3 ? j % 3 : 0;

        var string = (this.properties.symbolPosition == 'left' ? this.properties.symbol + (this.properties.space ? ' ' : '') : '')
            + sign
            + (j ? i.substr(0, j) + this.properties.thousandsSep : "")
            + i.substr(j).replace(/(\d{3})(?=\d)/g, "$1" + this.properties.thousandsSep)
            + (this.properties.precision ? this.properties.zerosSep + Math.abs(number - i).toFixed(this.properties.precision).slice(2) : "")
            + (this.properties.symbolPosition == 'right' ? (this.properties.space ? ' ' : '') + this.properties.symbol : '');

        return string;
    },
    toPercent: function (number){
        return (number * 100).toFixed(2) + '%';
    }
};