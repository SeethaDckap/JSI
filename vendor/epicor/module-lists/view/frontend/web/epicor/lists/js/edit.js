/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

var product_opened = 0;
var customer_opened = 0;

require([
    'jquery',
    'mage/mage',
    'prototype',
    'jquery/validate',
    'mage/adminhtml/form'
], function ($,mage) {
// document.observe('dom:loaded', function () {
$(document).ready(function(){
    //var listForm = new VarienForm('list_form', true);
    var listForm = $('#list_form');
    listForm.mage('validation', {});

    if ($("#update_list").length){
            $('#update_list').bind("click", function (e) {
                if ($('#list_form').validation() && $('#list_form').validation('isValid'))
                {
                    saveProductQty();
                    e.target.form.submit();

                }
                else {
                    return;
                }
            });
    }
    
    if ($("#create_list").length){

        $('#create_list').bind("click", function (e) {
            if($('#list_form').validation() && $('#list_form').validation('isValid')){
                saveProductQty();
                if(window.saveCartAsList){
                    //add to form
                    jQuery('#list_form').append($("<input>").attr('type', 'hidden').attr('name', '' +
                        'saveNewCartToList').val(window.saveCartAsListCodedJson));
                }

                e.target.form.submit();
            } else {
                primary_details('primary_details');
                return;
            }
        });
    }
    
    if ($("#type").length){
        checkSettingTypes();
        $('#type').bind("change", function (e) {
            checkSettingTypes()
        });
    }
    function saveProductQty(cart){
        var productQty = [];
        $('#list_products_table tbody .col-qty input').each(function( index ) {
            var checked = ($(this).parent().parent().find('input[name="links[]"]:checked').length > 0);
            if(checked){

                var sku = $(this).parent().parent().find(".col-sku").html().trim();
                var uom = $(this).parent().parent().find(".col-uom").html().trim();
                var loc = $(this).parent().parent().find(".col-loc").html().trim();
                productQty.push({sku:sku, val: $( this ).val(), uom:uom, location_code:loc});
            }
        });
        var productQtyJson = JSON.stringify(productQty);
        //add to form
        $('#list_form').append($("<input>").attr('type', 'hidden').attr('name', '' +
            'productsQty').val(productQtyJson));
    }
    //check if the list is to be created from saveCartToList - ie is the referenceCode prepopulated
    var parms = window.location.href.split('parms/');
    if(parms.length > 1){
        window.saveCartAsList = true;
        window.saveCartAsListCodedJson = parms[1];
        var decodedSaveCartAsListJson = atob(parms[1]);
        var referenceCode = JSON.parse(decodedSaveCartAsListJson).listCode;
        $('#primary_detail_content #erp_code').val(referenceCode).prop('disabled', 'disabled');
    }
});



});
function primary_details(lid) {
    if (lid)
        setCurrent(lid);
    $('primary_detail_content').show();
    $('product_grid').hide();
    $('customer_grid').hide();
}

function setCurrent(lid) {
    tablinks = document.getElementsByClassName("current");
    for (i = 0; i < tablinks.length; i++) {
        tablinks[i].className = tablinks[i].className.replace("current", "");
    }
    var d = document.getElementById(lid);
    d.className += " current";
}

function startLoading() {
    $('please-wait').show();
}
function list_product(lid) {
    if (lid)
        setCurrent(lid);
    $('primary_detail_content').hide();
    $('customer_grid').hide();
    if (product_opened != 1) {
        product_opened = 1;
        new Ajax.Request(product_url, {
            method: 'POST',
            onLoading: startLoading,
            onComplete: function (transport) {
                $('please-wait').hide();
                $('product_grid').show();
                div = $('product_grid');
                div.update(transport.responseText);

   //           don't display qty col if list isn't Fa
                if(jQuery('#type').val() != 'Fa'){
                    jQuery('#product_grid').find('.col-qty').each(function(){
                          $(this).hide();
                      });
                    jQuery('#product_grid').find('.col-loc').each(function(){
                        $(this).hide();
                    });
                 }
                // if page changes, check again before displaying col
                 jQuery('body').on('mouseover', function (){
                    if(jQuery('#type').val() != 'Fa'){
                        jQuery('#product_grid').find('.col-qty').each(function(){
                            $(this).hide();
                        });
                        jQuery('#product_grid').find('.col-loc').each(function(){
                            $(this).hide();
                        });
                    }
                 })

            }
        });
    }
    else {
        $('product_grid').show();
    }
}
function list_customer(lid) {
    if (lid)
        setCurrent(lid);
    $('primary_detail_content').hide();
    $('product_grid').hide();
    if (customer_opened != 1) {
        customer_opened = 1;
        new Ajax.Request(customer_url, {
            method: 'POST',
            onLoading: startLoading,
            onComplete: function (transport) {
                $('please-wait').hide();
                $('customer_grid').show();
                div = $('customer_grid');
                div.update(transport.responseText);

            }

        });
    }
    else {
        $('customer_grid').show();
    }
}

function checkSettingTypes() {
    var chosenType = $('type').value;
    if ($('supported_settings_' + chosenType) == undefined) {
        return;
    }
    var supportedSettings = $('supported_settings_' + chosenType).value.split('');
    var allSettings = $('supported_settings_all').value.split('');
    for (i = 0; i < allSettings.length; i++) {
        if(supportedSettings.indexOf(allSettings[i]) == -1) {
            $('settings_' + allSettings[i]).checked = false;
            $('settings_' + allSettings[i]).parentNode.hide();
        } else {
            $('settings_' + allSettings[i]).parentNode.show();
        }
    }
}
