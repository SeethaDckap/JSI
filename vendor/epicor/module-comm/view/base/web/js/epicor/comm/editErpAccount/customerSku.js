/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */



// phrases grid update code 
if (typeof Epicor_CustomerSku == 'undefined') {
    var Epicor_CustomerSku = {};
}
require([
    'jquery',
    'prototype'
], function (jQuery) {
    Epicor_CustomerSku.customerSku = Class.create();
    Epicor_CustomerSku.customerSku.prototype = {
        rows: $H({}),
        form: null,
        formWrapper: null,
        table: null,
        ajaxRequest: false,
        erpAccount: null,
        initialize: function(form, table) {
            if ($(form)) {
                this.form = $(form);
                this.url = this.form.select('input[name=customersku_post_url]')[0].value;
                this.deleteUrl = this.form.select('input[name=customersku_delete_url]')[0].value;
                this.erpAccount = this.form.select('input[name=customer_group_id]')[0].value;
                this.formWrapper = this.form.up();
                this.formWrapper.hide();
            }
            this.table = table;
        },
        save: function() {
            var data = {};
            this.form.select('input').forEach(function(input) {
                data[input.name] = input.value;
            });
            data['customer_group_id'] = this.erpAccount;
            if (this.ajaxRequest) {
                alert('Form already submitted');
            } else if(this.validate()){
                this.ajaxRequest = true;
                new Ajax.Request(this.url, {
                    method: 'post',
                    parameters: data,
                    requestHeaders: {Accept: 'application/json'},
                    onComplete: function(request) {
                        this.ajaxRequest = false;
                    }.bind(this),
                    onSuccess: function(request) {
                        var json = request.responseText.evalJSON(true);
                        this.insertMessage(json.message,json.type);
                        this.close();
                        eval(this.table+'JsObject').reload();
                    }.bind(this),
                    onFailure: function(request) {
                        alert('Error');
                    }.bind(this),
                    onException: function(request, e) {
                        alert(e);
                    }.bind(this)
                });
            }
            return false;
        },
        rowEdit: function(row, id) {
            var rowData = row.up('tr').select('input[name:rowData[]]')[0].value.evalJSON();
            if (rowData) {
                for (var key in rowData) {
                    if (this.form.select('input[name=' + key + ']').length > 0) {
                        this.form.select('input[name=' + key + ']')[0].value = rowData[key];
                    }
                }
                if (this.form.select('span[id=product_id_name]').length > 0 ) {
                    this.form.select('span[id=product_id_name]')[0].innerHTML = rowData['product_sku'];
                }
                $('updateCustomerSkuSubmit').show();
                $('addCustomerSkuSubmit').hide();
                this.formWrapper.show();
            } else {
                alert('Data not found for this row');
            }
        },
        rowDelete: function(row) {
            var data = row.up('tr').select('input[name:rowData[]]')[0].value.evalJSON();
            if (data) {
                this.ajaxRequest = true;
                new Ajax.Request(this.deleteUrl, {
                    method: 'post',
                    parameters: data,
                    requestHeaders: {Accept: 'application/json'},
                    onComplete: function(request) {
                        this.ajaxRequest = false;
                    }.bind(this),
                    onSuccess: function(request) {
                        var json = request.responseText.evalJSON(true);
                        this.insertMessage(json.message,json.type);
                        this.close();
                        eval(this.table+'JsObject').reload();
                    }.bind(this),
                    onFailure: function(request) {
                        alert('Error');
                    }.bind(this),
                    onException: function(request, e) {
                        alert(e);
                    }.bind(this)
                });

            } else {
                alert('Data not found for this row');
            }
        },
        add: function() {

            this.form.select('input').forEach(function(element) {
                if (element.name != 'updateCustomerSkuSubmit' && element.name != 'addCustomerSkuSubmit') {
                    element.value = '';
                }
            });
            productSelector.removeProduct('product_id');
            $('updateCustomerSkuSubmit').hide();
            $('addCustomerSkuSubmit').show();

            this.formWrapper.show();
        },
        close: function(){
            this.formWrapper.hide();
            this.form.select('input').forEach(function(element) {
                if (element.name != 'updateCustomerSkuSubmit' && element.name != 'addCustomerSkuSubmit') {
                    element.value = 'default';
                }
            });
        },
        validate: function(){
            var validation = true;
            this.form.select('input').forEach(function(element) {
                if (!Validation.validate(element)){
                    validation = false;
                }
            });
            return validation;
        },
        insertMessage: function(message,type){
            if($('messages').select('ul').length == 0){
                $('messages').insert('<ul class="messages"></ul>');
            }else{
                $('messages').select('li').forEach(function(element){element.remove()});
            }
            $('messages').select('ul')[0].insert('<li class="' + type + '"><span>' + message + '</span></li>');
        }
    };
});

var customerSku;
function createCustomerSku(form, table){
    customerSku = new Epicor_CustomerSku.customerSku(form,table);
}
