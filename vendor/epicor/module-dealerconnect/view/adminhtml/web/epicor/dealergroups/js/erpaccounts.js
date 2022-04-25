/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

var erpAccountsReloadParams = {erp_accounts: '', erp_accounts_exclusion: ''};

require(['prototype', 'EpicorCommon'], function () {


    function loadErpGrid(typevalue) {
        var ajax_url = document.getElementById('ajax_url').value;
        var selectedErpAccount = document.getElementsByName("links[erpaccounts]")[0].value;
        var url = decodeURIComponent(ajax_url);
        this.ajaxRequest = new Ajax.Request(url, {
            method: 'post',
            parameters: {'id': '4', 'linkTypeValue': '', 'selectedErpAccount': selectedErpAccount},
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
    }

    function selectAll()
    {
        $$('#erpaccountsGrid_table input[type=checkbox]').each(function (elem) {
            if (elem.checked == false) {
                elem.click();
            }
        });
    }

    function unselectAll()
    {
        $$('#erpaccountsGrid_table input[type=checkbox]').each(function (elem) {
            if (elem.checked) {
                elem.click();
            }
        });
    }

});
