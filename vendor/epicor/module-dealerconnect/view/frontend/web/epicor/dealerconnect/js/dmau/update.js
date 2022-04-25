/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

if (typeof Epicor_DmauUpdate == 'undefined') {
    var Epicor_DmauUpdate = {};
}
var DmauUpdate = 'dmauUpdate';

require([
    "jquery",
    "Magento_Checkout/js/model/full-screen-loader",
    "mage/url",
    "Magento_Ui/js/modal/modal",
    "mage/mage"
], function (jQuery, fullScreenLoader, url, modal) {
    Epicor_DmauUpdate.dmauUpdate = Class.create();
    Epicor_DmauUpdate.dmauUpdate.prototype = {
        initialize: function () {
        },
        extraFunction: function () {
        },
        initializeElement: function (element) {

        },

        formSubmit: function (rowId, locNum, idNum, SerNum) {
            var dmauFrm = jQuery("#dmau_form_" + rowId);
            var searchKey = dmauFrm.find("input.search-key");
            var url = dmauFrm.attr('action');
            if (searchKey.val() !== '') {
                if (dmauFrm.find(".part-not-found").length > 0) {
                    dmauFrm.find(".part-not-found").each(function(){
                        jQuery(this).remove();
                    });
                }
                jQuery("#dmau_form_" + rowId + " :disabled").removeAttr('disabled');
                jQuery("body").trigger("processStart");
                var productCode = dmauFrm.find('[name="new[product_code]"]').html();
                var productDesc = dmauFrm.find('[name="new[description]"]').html();
                if (dmauFrm.find('input[name="new[product_code]"]').length > 0 || dmauFrm.find('input[name="new[description]"]').length > 0) {
                    productCode = dmauFrm.find('input[name="new[product_code]"]').val();
                    productDesc = dmauFrm.find('input[name="new[description]"]').val(); 
                    var error = false;
                    if (productCode == '') {
                        error = true;
                        dmauFrm.find('input[name="new[product_code]"]').after("<span class='part-not-found code-not-found'>Please enter Product Code</span>");
                    }
                    if (productDesc == '') {
                        error = true;
                        dmauFrm.find('input[name="new[description]"]').after("<span class='part-not-found desc-not-found'>Please enter Description</span>");
                    }
                    if (error == true) {
                        jQuery("body").trigger("processStop");
                        return false;
                    }
                }
                jQuery.ajax({
                    showLoader: false,
                    data: {
                        productcode: productCode,
                        locNum: locNum,
                        IdNum: idNum,
                        SerNum: SerNum,
                        description: productDesc,
                        materialID: dmauFrm.find('[name="new[material_id]"]').html(),
                        UOM: dmauFrm.find('[name="new[unit_of_measure_code]"]').html(),
                        formdata: dmauFrm.serialize()
                    },
                    url: url,
                    type: "POST",
                }).done(function (dataJson) {
                    jQuery("body").trigger("processStop");
                    location.reload();
                });
            }else{
                searchKey.focus();
                searchKey.css('border-color', '#e02b27');
                searchKey.css('box-shadow', '0 0 3px 1px #e02b27');
                dmauFrm.find("span.dmau-input[name='new[product_code]'],span.dmau-input[name='new[description]'],span.dmau-input[name='new[unit_of_measure_code]']").text("");
            }


        },

        keySearch: function (rowId) {
            var dmauFrm = jQuery("#dmau_form_" + rowId);
            var searchKey = dmauFrm.find("input.search-key");
            var superGrp = jQuery("#qa_super_group_" + rowId).val();
            var proId = jQuery("#qa_product_id_" + rowId).val();
            var proInfo = (superGrp) ? superGrp : proId;
            if (jQuery(".part-not-found") != undefined) {
                jQuery(".part-not-found").remove();
            }
            if (searchKey.val() === '') {
                searchKey.focus();
                searchKey.css('border-color', '#e02b27');
                searchKey.css('box-shadow', '0 0 3px 1px #e02b27');
                dmauFrm.find("span.dmau-input[name='new[product_code]'],span.dmau-input[name='new[description]'],span.dmau-input[name='new[unit_of_measure_code]']").text("");
            } else if (proId.length !== 0) {
                jQuery('body').trigger('processStart');
                jQuery.ajax({
                    showLoader: false,
                    data: {
                        productcode: jQuery("#qa_sku_" + rowId).val(),
                        productid: proInfo
                    },
                    url: url.build('dealerconnect/inventory/getProductDmau'),
                    type: "POST",
                }).done(function (data) {
                    jQuery('body').trigger('processStop');
                    for (var key in data) {
                        dmauFrm.find('[name="new[' + key + ']"]').text(data[key]);
                    }
                    dmauFrm.find('div.info-list p').each(function(){
                        if (jQuery(this).find("span[name*='new']").length > 0 && !jQuery(this).find("span[name*='new']").is(':visible'))
                        {
                            jQuery(this).find("span[name*='new']").show();
                            jQuery(this).find("input[name*='new']").hide();
                        }
                    });
                });
            } else {
                var searchUrl = url.build('dealerconnect/inventory/linesearch?q=') + jQuery("#qa_sku_" + rowId).val();
                jQuery("#qa_product_id_" + rowId).val('');
                if (jQuery("#qa_custompart_" + rowId).length > 0) {
                    searchUrl += "&custom_part=1";
                }
                if (jQuery("#qa_custompart_" + rowId).prop("checked") === true) {
                    jQuery('body').trigger('processStart');
                    dmauFrm.find('div.info-list p').each(function(){
                        if (jQuery(this).find("input[name*='new']").length == 0) {
                            var spanData = jQuery(this).find("span[name*='new']");
                            var inputValue = '';
                            switch(true) {
                                case (spanData.attr("name") == "new[quantity]"):
                                    inputValue = "1";
                                break;
                                case (spanData.attr("name") == "new[product_code]"):
                                    inputValue = searchKey.val();
                                break;
                            }
                            var inputField = jQuery("<input class='dmau-input new-added' type='text' name='" + spanData.attr("name") + "' value='" + inputValue + "' />");
                            spanData.hide();
                            jQuery(this).append(inputField);
                        } else {
                            var spanData = jQuery(this).find("span[name*='new']");
                            switch(true) {
                                case (spanData.attr("name") == "new[quantity]"):
                                    jQuery(this).find("input[name*='new']").val("1");
                                break;
                                case (spanData.attr("name") == "new[product_code]"):
                                    jQuery(this).find("input[name*='new']").val(searchKey.val());
                                break;
                                default:
                                    jQuery(this).find("input[name*='new']").val("");
                                break;
                            }
                            spanData.hide();
                            jQuery(this).find("input[name*='new']").show();
                        }
                    });
                    jQuery('body').trigger('processStop');
                } else {
                    jQuery.ajax({
                        showLoader: true,
                        url: searchUrl,
                        type: "GET",
                    }).done(function (data, status, xhr) {
                        var responseType = xhr.getResponseHeader("content-type");
                        if (responseType == "application/json") {
                            var response = jQuery.parseJSON(data);
                            searchKey.after("<span class='part-not-found'>" + response.result + "</span>");
                        } else {
                            var options = {
                                type: 'popup',
                                responsive: true,
                                innerScroll: true,
                                buttons: []
                            };
                            jQuery("#search_" + rowId).html(data);
                            jQuery("#search_" + rowId).find(".row_id").val(rowId);
                            if (jQuery('#search_' + rowId).length == 0) {
                                var bomLinesearch = "<div class='bom_linesearch' id='search_" + rowId + "'></div>";
                                dmauFrm.find(".fieldset").append(bomLinesearch);
                            }
                            var popup = modal(options, jQuery('#search_' + rowId));
                            jQuery("#search_" + rowId).modal('openModal');
                        }
                    });
                }
                dmauFrm.find("span.dmau-input[name='new[product_code]'],span.dmau-input[name='new[description]'],span.dmau-input[name='new[unit_of_measure_code]']").text("");
            }
        },
        
        selectMaterial: function(el, proInfo, sku) {
            var rowId = jQuery(el).closest("div.qop-productlist").find("input[name='row_id']").val();
            var dmauFrm = jQuery("#dmau_form_" + rowId);
            jQuery('body').trigger('processStart');
                jQuery.ajax({
                    showLoader: false,
                    data: {
                        productcode: sku,
                        productid: proInfo
                    },
                    url: url.build('dealerconnect/inventory/getProductDmau'),
                    type: "POST",
                }).done(function (data) {
                    jQuery('body').trigger('processStop');
                    for (var key in data) {
                        dmauFrm.find('[name="new[' + key + ']"]').text(data[key]);
                    }
                    dmauFrm.find('div.info-list p').each(function(){
                        if (jQuery(this).find("span[name*='new']").length > 0 && !jQuery(this).find("span[name*='new']").is(':visible'))
                        {
                            jQuery(this).find("span[name*='new']").show();
                            jQuery(this).find("input[name*='new']").hide();
                        }
                    });
                    jQuery("#search_" + rowId).modal('closeModal');
                });
        },
        
        addNewLine: function(){
            if (jQuery(".dmau-clone-row").length !== 0) {
                jQuery('.dmau-clone-row').find("div:first-child").show();
                jQuery('html, body').animate({
                    scrollTop: jQuery(".dmau-clone-row").offset().top
                }, 1000);
            } else {
                jQuery('html, body').animate({
                    scrollTop: jQuery(".dmau-clone-row").offset().top
                }, 1000);
            }
        },
        
        addFormClose: function(){
            jQuery('.dmau-clone-row').find("div:first-child").hide();
            jQuery('html, body').animate({
                    scrollTop: jQuery(".dmau-clone-row").offset().top
            }, 1000);
        }

    };
    dmauUpdate = new Epicor_DmauUpdate.dmauUpdate();
    window.dmauUpdate = dmauUpdate;
});