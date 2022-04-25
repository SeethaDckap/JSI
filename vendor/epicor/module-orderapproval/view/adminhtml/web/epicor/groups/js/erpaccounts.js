/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
var erpAccountsReloadParams = {erp_accounts: ''};

require([
    'jquery',
    'prototype',
    'EpicorCommon'
], function ($) {
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

    $(document).ready(function(){
        $(document).ajaxStop(function () {
            $("#tab_customers_tab").mouseover(function(event){
                var el = Event.element(event);
                el = el.up('a');
                var notloaded = document.getElementById("customersGrid");
                if (notloaded == undefined) {
                    if(typeof erpaccountsGridJsObject !== 'undefined'){
                        var erp_accounts = erpaccountsGridJsObject.reloadParams['erpaccounts[]'];
                        erpAccountsReloadParams['erp_accounts[]'] = erp_accounts;
                    }
                }
            });
        });
    });
});