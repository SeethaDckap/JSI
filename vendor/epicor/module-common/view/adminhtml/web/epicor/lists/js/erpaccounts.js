/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


function toggleErpAccountsGrid() {
    if ($('erp_account_link_type').value == 'E') {
        $('erpaccountsGrid').show();
    } else if ($('erp_account_link_type').value == 'B') {
        $('erpaccountsGrid').show();
    } else if ($('erp_account_link_type').value == 'C') {
        $('erpaccountsGrid').show();
    } else if ($('erp_account_link_type').value == 'N') {
        $('erpaccountsGrid').hide();
    } else {
        $('erpaccountsGrid').hide();
    }

    if ($("erp_account_link_type")) {
        if ($("erp_account_link_type").value == "N") {
            $("erp_accounts_exclusion").hide();
            $$('label[for="erp_accounts_exclusion"]').first().hide();
        } else {
            $("erp_accounts_exclusion").show();
            $$('label[for="erp_accounts_exclusion"]').first().show();
        }
    }
}

function loadErpGrid(typevalue) {
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
                $('loading-mask').hide();
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
            elem.simulate('click');
        }
    });
}

function unselectAll()
{
    $$('#erpaccountsGrid_table input[type=checkbox]').each(function (elem) {
        if (elem.checked) {
            elem.simulate('click');
        }
    });
}

var erpAccountsReloadParams = {erp_account_link_type: '', erp_accounts: '', erp_accounts_exclusion: ''};

document.observe('dom:loaded', function () {

    Event.live('#erp_account_link_type', 'change', function (element) {
        toggleErpAccountsGrid();
        loadErpGrid();
        if ($("erp_account_link_type").value == "N") {
            $("erp_accounts_exclusion").hide();
            $$('label[for="erp_accounts_exclusion"]').first().hide();
        } else {
            $("erp_accounts_exclusion").show();
            $$('label[for="erp_accounts_exclusion"]').first().show();
        }
    });

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


