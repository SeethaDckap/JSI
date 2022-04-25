/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*tab notes*/
var tabaddress = 'Use this tab to setup an address restriction for a specific address.';
var tabcountry = 'Use this tab to setup an address restriction for a specific country.';
var tabcounty = 'Use this tab to setup an address restriction for a specific state.';
var tabzipcode ='Use this tab to setup an address restriction for a zipcode.';

function openRestriction(evt, restrictionName) {
    
    var i, tabcontent, tablinks;
    tabcontent = document.getElementsByClassName("tabcontent");
    $('tabnote').update(eval(restrictionName));
    for (i = 0; i < tabcontent.length; i++) {
        tabcontent[i].style.display = "none";
    }
    tablinks = document.getElementsByClassName("tablinks");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace(" active", "");
    }
    document.getElementById(restrictionName).style.display = "block";
    evt.currentTarget.className += " active";
    closeRestrictionForm();
    if ($('restrictedaddressGrid') != null) {
        $('restrictedaddressGrid').remove();
    }
}


var listRestrictionAddress;
function initListRestrictionAddress() {
    listRestrictionAddress = new Epicor_Comm.childrenGrid;
    listRestrictionAddress.parentIdField = 'list_id';
    listRestrictionAddress.fieldsMap = {
        id: 'address_id',
        name: 'address_name'
    };
    listRestrictionAddress.initialize('restrictions_form', 'restrictedaddressGrid', 'nohide');
}
function closeRestrictionForm() {
    if (typeof $$('.tabcontent .entry-edit')[0] != 'undefined')
    {
        $$('.tabcontent .entry-edit')[0].remove();
    }
}
function saveRestrictionAddress(restriction_type)
{
    initListRestrictionAddress();
    var valid = listRestrictionAddress.validate();
    if (valid !== false) {
        listRestrictionAddress.save(getMultiSelectCountries());
        closeRestrictionForm();
    }
    //addTabGrid(restriction_type);
}

function getMultiSelectCountries()
{
    let countryMultiSelect = getCountryMultiSelect();
    if (typeof countryMultiSelect === 'object') {
        let countryOptions = {};
        let multiSelectedOptions = countryMultiSelect.select('select');
        countryOptions.multiSelectOptions = multiSelectedOptions.val();

        return countryOptions;
    }
}

function getCountryMultiSelect()
{
    let countryMultiSelect = jQuery('body').find('select#country.multiselect');
    if (countryMultiSelect.length === 1) {
        return countryMultiSelect;
    }

    return false;
}

function deleteRestrictedAddress(address_id, restriction_type) {
    var url = document.getElementById('delete_url').value;
    this.ajaxRequest = new Ajax.Request(url, {
        method: 'post',
        parameters: {'id': address_id},
        onComplete: function (request) {
            this.ajaxRequest = false;
        }.bind(this),
        onSuccess: function (data) {
            eval('restrictedaddressGridJsObject').reload();
           // addTabGrid(restriction_type);
        }.bind(this),
        onFailure: function (request) {
            alert('Error occured in Ajax Call');
        }.bind(this),
        onException: function (request, e) {
            alert(e);
        }.bind(this)
    });

}

function openRestrictionForm(address_id, list_id, buttonType, restrictionTypeValue)
{
    closeRestrictionForm();
    if ($('restrictions_form') == null) {
        var url = document.getElementById('form_url').value;
        this.ajaxRequest = new Ajax.Request(url, {
            method: 'post',
            parameters: {'address_id': address_id, 'list_id': list_id, 'buttonType': buttonType, 'restrictionTypeValue': restrictionTypeValue},
            onComplete: function (request) {
                this.ajaxRequest = false;
            }.bind(this),
            onSuccess: function (data) {
                // if(restrictionTypeValue=='A')
                $('restrictedaddressGrid').insert({before: data.responseText});
            }.bind(this),
            onFailure: function (request) {
                alert('Error occured in Ajax Call');
            }.bind(this),
            onException: function (request, e) {
                alert(e);
            }.bind(this)
        });
    }
}


function addTabGrid(typevalue) {
    var ajax_url = document.getElementById('addgrid_url').value;
    var list_id = document.getElementById('list_id').value;
    var url = decodeURIComponent(ajax_url);
    this.ajaxRequest = new Ajax.Request(url, {
        method: 'post',
        parameters: {'linkTypeValue': typevalue, 'list_id': list_id},
        onComplete: function (request) {
            this.ajaxRequest = false;
        }.bind(this),
        onSuccess: function (data) {
            if (typevalue == 'A')
                $('tabaddress').update(data.responseText);
            if (typevalue == 'C')
                $('tabcountry').update(data.responseText);
            if (typevalue == 'S')
                $('tabcounty').update(data.responseText);
            if (typevalue == 'Z')
                $('tabzipcode').update(data.responseText);

        }.bind(this),
        onFailure: function (request) {
            alert('Error occured in Ajax Call');
        }.bind(this),
        onException: function (request, e) {
            alert(e);
        }.bind(this)
    });
}
function loadRestrictionsGrid(typevalue) {
    var ajax_url = document.getElementById('ajax_url').value;
    //var linkTypeValue = $('restriction_type').value;
    var linkTypeValue = typevalue;
    //var selectedRestriction = document.getElementsByName("links[restricted_addresses]")[0].value;
    var url = decodeURIComponent(ajax_url);
    this.ajaxRequest = new Ajax.Request(url, {
        method: 'post',
        parameters: {'linkTypeValue': linkTypeValue},
        onComplete: function (request) {
            this.ajaxRequest = false;
        }.bind(this),
        onSuccess: function (data) {
            addTabGrid(typevalue);
        }.bind(this),
        onFailure: function (request) {
            alert('Error occured in Ajax Call');
        }.bind(this),
        onException: function (request, e) {
            alert(e);
        }.bind(this)
    });
}