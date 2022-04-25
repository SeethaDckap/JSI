/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * @author aakimov
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/storage',
    'mage/url',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/step-navigator',
    'Epicor_Lists/epicor/lists/js/addressselector',
    'Magento_Checkout/js/model/quote'
], function (
        $,
        storage,
        urlBuilder,
        fullScreenLoader,
        stepNavigator,
        addressselector,
        quote
) {
    'use strict';

    /** Override default place order action and add comment to request */
    return function (data,section) { 
        data = (data === undefined) ? false : data;
        var elm = $('#'+section+' [name="ecc_required_date"]');
        if(elm.length) {
            if(data){                
              var params =  'addressdata='+data;
            }else{
              var params =  'addressdata='+JSON.stringify(quote.shippingAddress());
            }
          var urls = 'comm/onepage/getShippingDates' ;
              var url = urlBuilder.build(urls);

              $.ajax({
                  showLoader: true,
                  url: url,
                  data: params,
                  type: "POST",
                  dataType: 'json'
              }).done(function (data) {
                if(data.success){
                    if(jQuery('[name="ecc_required_date"]').attr('type')=="radio"){
                        jQuery('#'+section+' li').remove();
                        var ulElm = jQuery('#'+section);
                        var i = 0;
                    $.each(data.dates, function(key,value) {
                       var ischecked = '';
                       if(i == 0){
                            ischecked = 'checked';
                        }
                        i = i + 1;
                        var lielm = '<li class="control"><input type="radio" name="ecc_required_date" class="admin__control-radio" value="'+key+'" '+ischecked+'/>\n\
                           <label class="admin__field-label" text="label" attr="for: ko.uid">'+value+'<label></li>'
                        ulElm.append(lielm);
                    });
                    }else{
                    elm.empty();
                    $.each(data.dates, function(key,value) {
                        elm.append($("<option></option>")
                           .attr("value", key).text(value));
                    });
                  }
                }
              }); 
          }
    };
});
