/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

function customerSelectAll()
{
    $$('#customersGrid_table input[type=checkbox]').each(function (elem) {
        if (elem.checked === false) {
            elem.click();
        }
    });
}


function customerUnselectAll()
{
    $$('#customersGrid_table input[type=checkbox]').each(function (elem) {
        if (elem.checked) {
            elem.click();
        }
    });
}

if (typeof Epicor_Role == 'undefined') {
    var Epicor_Role = {};
}
require([
    'jquery',
    'prototype',
    'Magento_Ui/js/modal/alert'
], function (jquery, prototype, alert) {
    Epicor_Role.customer = Class.create();
    Epicor_Role.customer.prototype = {
        table: null,
        listId: null,
        nextId: 0,
        url: null,
        importUrl: null,
        csvDowloadUrl: null,
        pricingIsEditable: false,
        translations: {},
        products: {},
        currencies: {},
        initialize: function (parameters) {
            var _self = this;
            for (var index in parameters) {
                this[index] = parameters[index];
            }
            _self.checkIsCondition();
        },
        checkIsCondition: function(){
            var _self = this;
            var checkConditionContainer = $("is_customers_condition_enabled");
            var conditionFormContainer = $("rule_customer_fieldset");
            if(checkConditionContainer.checked){
                checkConditionContainer.value = 1;
                conditionFormContainer.show();
            }else{
                checkConditionContainer.value = 0;
                conditionFormContainer.hide();
            }
            checkConditionContainer.observe("change", function(){
                if(this.value == "1"){
                    conditionFormContainer.show();
                }else{
                    conditionFormContainer.hide();
                }
            });

        },
        insertMessages: function (messages) {
            /* if ($('messages').select('ul').length == 0) {
                 $('messages').insert('<ul class="messages"></ul>');
             } else {
                 $('messages').select('li').forEach(function (element) {
                     element.remove()
                 });
             } */
            // if (jQuery('#anchor-content #messages').length) {
            //     jQuery('#anchor-content #messages').each(function () {
            //         jQuery('#anchor-content #messages').remove();
            //     });
            // }
            // for (var type in messages) { // not reachable code
            //     var msgType = (type == 'errors' ? 'error-msg' : 'warning-msg');
            //     if (messages.hasOwnProperty(type)) {
            //         for (var index in messages[type]) {
            //             if (messages[type].hasOwnProperty(index)) {
            //                 $('messages').select('ul')[0].insert('<li class="' + msgType + '"><span>' + messages[type][index] + '</span></li>');
            //             }
            //         }
            //     }
            // }
        },
        translate: function (toTranslate) {
            return this.translations.hasOwnProperty(toTranslate) ? this.translations[toTranslate] : toTranslate;
        },
        import: function () {
            if ($('import_customer_media')) {
                var files = $('import_customer_media').files;
                var data = new FormData();

                if (files.length > 0) {
                    data.append('import', files[0], files[0].name);
                    data.append('form_key', FORM_KEY);
                    if ($('erp_account_link_type')) { // send ERP tab Data
                        var link_type = $('erp_account_link_type').value;
                        var exclusion = $('erp_accounts_exclusion').checked ? 'Y' : 'N';
                        var erpGridObject = eval(this.tableErp + 'JsObject');
                        var erp_accounts = erpGridObject.reloadParams['erpaccounts[]'];
                        data.append('erp_account_link_type', link_type);
                        data.append('erp_accounts_exclusion', exclusion);
                        data.append('erp_accounts', erp_accounts);
                    }
                    jQuery('body').trigger('processStart');
                    jQuery.ajax({
                        url: this.importUrl + '?isAjax=true', data: data,
                        type: 'post',
                        dataType: 'json',
                        async: false,
                        contentType: false,
                        cache: false,
                        processData: false,
                        //showLoader: true,
                        success: function (response) {
                            customer.reloadGrid(response.products);
                            //epraccount.insertMessages(response.errors);
                        },
                        error: function () {
                            alert('Error');
                            jQuery('body').trigger('processStop');
                        }
                    });
                    $('import_customer_media').value = '';
                } else {
                    alert({
                        title: 'Error',
                        content: this.translate('Please choose a file.'),
                        actions: {
                            always: function () {
                            }
                        }
                    });
                }
            }
            return false;
        },
        reloadGrid: function (Customer) {
            var gridObject = eval(this.table + 'JsObject');
            if (Customer.length) {
                var oldCustomer = gridObject.reloadParams['customers[]'];
                if (Customer.length > 0) {
                    for (var i = 0; i < Customer.length; i++) {
                        var id = Customer[i];
                        if (oldCustomer.indexOf(id) == -1) {
                            oldCustomer.push(id);
                        }
                    }
                    gridObject.reloadParams['customers[]'] = oldCustomer;
                    $$('input[name=links[customers]]')[0].value = this.serializeProducts(oldCustomer);
                } else {
                    gridObject.reloadParams['customers[]'] = Customer;
                    $$('input[name=links[customers]]')[0].value = this.serializeProducts(Customer);
                }
            }
            //jQuery('body').trigger('processStop');
            gridObject.reload(null, this.afterGridReload(this,Customer));

        },
        afterGridReload: function(event, Customer){
            var _self = this;
            setTimeout(function() {
                var gridObject = eval(event.table + 'JsObject');
                for (var i = 0; i < Customer.length; i++) {
                    var id = Customer[i];
                    var element = $("id_"+id);
                    if(element) {
                        gridObject.checkboxCheckCallback(gridObject,element,true);
                    }
                }

                var oldCustomer = gridObject.reloadParams['customers[]'];
                if (Customer.length > 0) {
                    for (var i = 0; i < Customer.length; i++) {
                        var id = Customer[i];
                        if (oldCustomer.indexOf(id) == -1) {
                            oldCustomer.push(id);
                        }
                    }
                    gridObject.reloadParams['customers[]'] = oldCustomer;
                    $$('input[name=links[customers]]')[0].value = _self.serializeProducts(oldCustomer);
                }

                jQuery('body').trigger('processStop');
            }, 1000);
        },
        serializeProducts: function (products) {
            var array = [];
            for (var i = 0; i < products.length; i++) {
                var id = products[i];
                array.push(encodeURIComponent(id) + "=" + encodeURIComponent(btoa('row_id=' + id)));
            }
            return array.join('&');
        },
        dowloadCsv: function () {
            return window.location = this.csvDowloadUrl;
        }
    };
});

var customer;

function initCustomer(parameters) {
    customer = new Epicor_Role.customer;
    customer.initialize(parameters);
}
