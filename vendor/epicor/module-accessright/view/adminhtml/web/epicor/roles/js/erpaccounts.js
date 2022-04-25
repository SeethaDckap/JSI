/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
var erpAccountsReloadParams = {erp_account_link_type: '', erp_accounts: '', erp_accounts_exclusion: ''};

require([
    'prototype',
    'EpicorCommon'
], function () {
    function selectAll() {
        $$('#erpaccountsGrid_table input[type=checkbox]').each(function (elem) {
            if (elem.checked == false) {
                elem.click();
            }
        });
    }

    function unselectAll() {
        $$('#erpaccountsGrid_table input[type=checkbox]').each(function (elem) {
            if (elem.checked) {
                elem.click();
            }
        });
    }

    document.observe('dom:loaded', function () {
        Event.observe('form_tabs_customers', 'mouseover', function (event) {
            var el = Event.element(event);
            el = el.up('a');
            var notloaded = Element.hasClassName(el, 'notloaded');
            if ($('erp_account_link_type') && notloaded) {
                var link_type = $('erp_account_link_type').value;
                var exclusion = $('erp_accounts_exclusion').checked ? 'Y' : 'N';
                var erp_accounts = erpaccountsGridJsObject.reloadParams['erpaccounts[]'];
                if (notloaded) {
                    var href = el.readAttribute('href');
                    var href = href + 'erp_account_link_type/' + link_type
                        + '/erp_accounts_exclusion/' + exclusion
                        + '/erp_accounts/' + erp_accounts;
                    el.writeAttribute('href', href);
                }
                erpAccountsReloadParams.erp_account_link_type = link_type;
                erpAccountsReloadParams.erp_accounts_exclusion = exclusion;
                erpAccountsReloadParams['erp_accounts[]'] = erp_accounts;
            }
        });
    });

});

if (typeof Epicor_Role == 'undefined') {
    var Epicor_Role = {};
}
require([
    'jquery',
    'prototype',
    'Magento_Ui/js/modal/alert',
    'domReady!'
], function (jquery, prototype, alert) {
    Epicor_Role.epraccount = Class.create();
    Epicor_Role.epraccount.prototype = {
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
            _self.toggleErpAccountsGrid();
            _self.checkIsCondition();
        },

        loadErpGrid: function(){
            var ajax_url = document.getElementById('ajax_url').value;
            var linkTypeValue = $('erp_account_link_type').value;
            var selectedErpAccount = document.getElementsByName("links[erpaccounts]")[0].value;
            var url = decodeURIComponent(ajax_url);
            this.ajaxRequest = new Ajax.Request(url, {
                method: 'post',
                parameters: {'id': '4', 'linkTypeValue': linkTypeValue, 'selectedErpAccount': selectedErpAccount},
                onComplete: function (request) {
                    this.ajaxRequest = false;
                }.bind(this),
                onSuccess: function (data) {
                    // var reset = erpaccountsGridJsObject.resetFilter();
                    var filter = erpaccountsGridJsObject.doFilter();
                    if (filter) {
                        $$('.loading-mask').hide();
                    }
                }.bind(this),
                onFailure: function (request) {
                    alert('Error occured in Ajax Call');
                }.bind(this),
                onException: function (request, e) {
                    alert(e);
                }.bind(this)
            });
        },

        toggleErpAccountsGrid: function () {
            var linkType = $('erp_account_link_type').value;
            var conditionFormContainer = $("rule_erp_conditions_fieldset");
            var checkConditionContainer = $("is_erp_account_condition_enabled");

            switch (linkType) {
                case "B":
                case "C":
                case "D":
                case "R":
                case "S":
                case "E":
                    $('erpaccountsGrid').show();
                    break;
                case "N":
                    $('erpaccountsGrid').hide();
                    break;
                default:
                    $('erpaccountsGrid').hide();
            }

            if ($("erp_account_link_type")) {
                if ($("erp_account_link_type").value == "N") {
                    $('erp_import_fields').hide(); //import

                    $("erp_accounts_exclusion").hide(); // account exclude checkbox
                    $$('label[for="erp_accounts_exclusion"]').first().hide(); // account exclude label

                    $("is_erp_account_condition_enabled").hide(); // condition exclude checkbox
                    $$('label[for="is_erp_account_condition_enabled"]').first().hide(); // condition exclude label
                    conditionFormContainer.hide();
                } else {
                    $('erp_import_fields').show(); //import

                    $("erp_accounts_exclusion").show(); // account exclude checkbox
                    $$('label[for="erp_accounts_exclusion"]').first().show(); // account exclude label

                    $("is_erp_account_condition_enabled").show(); // condition exclude checkbox
                    $$('label[for="is_erp_account_condition_enabled"]').first().show();  // condition exclude label


                    if (checkConditionContainer.checked) {
                        checkConditionContainer.value = 1;
                        conditionFormContainer.show();
                    } else {
                        checkConditionContainer.value = 0;
                        conditionFormContainer.hide();
                    }
                }
            }
        },

        checkIsCondition: function () {
            var _self = this;
            var checkConditionContainer = $("is_erp_account_condition_enabled");
            var conditionFormContainer = $("rule_erp_conditions_fieldset");
            checkConditionContainer.observe("change", function () {
                if (this.value == "1") {
                    conditionFormContainer.show();
                } else {
                    conditionFormContainer.hide();
                }
            });
        },

        changeERPType: function(){
            var _self = this;
            _self.toggleErpAccountsGrid();
            _self.loadErpGrid();
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
            if ($('import')) {
                var files = $('import').files;
                var data = new FormData();

                if (files.length > 0) {
                    data.append('import', files[0], files[0].name);
                    data.append('form_key', FORM_KEY);
                    //jQuery('.loading-mask').show();
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
                            epraccount.reloadGrid(response.products);
                            //epraccount.insertMessages(response.errors);
                        },
                        error: function () {
                            alert('Error');
                            jQuery('body').trigger('processStop');
                        }
                    });
                    $('import').value = '';
                } else {
                    alert({
                        title: 'Error',
                        content: this.translate('Please choose a file.'),
                        actions: {
                            always: function () {
                            }
                        }
                    });
                    //alert(this.translate('Please choose a file.'));
                }
            }
            return false;
        },

        reloadGrid: function (ERPAccout) {
            var gridObject = eval(this.table + 'JsObject');
            if (ERPAccout.length) {
                var oldERPAccout = gridObject.reloadParams['erpaccounts[]'];
                if (ERPAccout.length > 0) {
                    for (var i = 0; i < ERPAccout.length; i++) {
                        var id = ERPAccout[i];
                        if (oldERPAccout.indexOf(id) == -1) {
                            oldERPAccout.push(id);
                        }
                    }
                    gridObject.reloadParams['erpaccounts[]'] = oldERPAccout;
                    $$('input[name=links[erpaccounts]]')[0].value = this.serializeProducts(oldERPAccout);
                } else {
                    gridObject.reloadParams['erpaccounts[]'] = ERPAccout;
                    $$('input[name=links[erpaccounts]]')[0].value = this.serializeProducts(ERPAccout);
                }
            }
            //jQuery('body').trigger('processStop');
            gridObject.reload(null, this.afterGridReload(this,ERPAccout));

        },

        afterGridReload: function(event, ERPAccout){
            var _self = this;
            setTimeout(function() {
                var gridObject = eval(event.table + 'JsObject');
                for (var i = 0; i < ERPAccout.length; i++) {
                    var id = ERPAccout[i];
                    var element = $("id_"+id);
                    if(element) {
                        gridObject.checkboxCheckCallback(gridObject,element,true);
                    }
                }

                var oldERPAccout = gridObject.reloadParams['erpaccounts[]'];
                if (ERPAccout.length > 0) {
                    for (var i = 0; i < ERPAccout.length; i++) {
                        var id = ERPAccout[i];
                        if (oldERPAccout.indexOf(id) == -1) {
                            oldERPAccout.push(id);
                        }
                    }
                    gridObject.reloadParams['erpaccounts[]'] = oldERPAccout;
                    $$('input[name=links[erpaccounts]]')[0].value = _self.serializeProducts(oldERPAccout);
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

var epraccount;
function initErpAccount(parameters) {
    epraccount = new Epicor_Role.epraccount;
    epraccount.initialize(parameters);
}