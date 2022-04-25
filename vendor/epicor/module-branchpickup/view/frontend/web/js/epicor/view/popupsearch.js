/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery','Magento_Checkout/js/model/full-screen-loader','Magento_Ui/js/modal/modal','mage/url','jquery/ui'
], function ($,fullScreenLoader,modal,url) {
        'use strict';
        return {
            checkItemsExists: function(locationCode,locationId,errorCode) {
                fullScreenLoader.startLoader();
                var checkAddress = this;
                    $.ajax({
                        showLoader: true,
                        data: {
                            locationcode: locationCode
                        },
                        url: url.build('branchpickup/pickup/changepickuplocation'),
                        type: "POST",
                        //dataType:'json',
                    }).done(function(data) {
                        var getType = data.type;
                        var getItemValues = data.values;
                        if ((getType == 'success') && (Object.keys(getItemValues).length != 0)) {
                            checkAddress.itemNotExistsInLocation(getItemValues, locationCode,locationId);
                            return false;
                        } else {
                            fullScreenLoader.startLoader();
                            $("#jsonBranchfiltervals", window.parent.document).val(locationCode);
                            $("#jsonBranchfilteraddress", window.parent.document).val(locationId);
                            $("#jsonBranchfiltererror", window.parent.document).val(errorCode);
                            $( ".action-close", window.parent.document).trigger( "click" );
                            $("#selectSearchBranchAddress_"+locationId, window.parent.document).trigger("click");                                    
                            fullScreenLoader.stopLoader();   
                        }
                    });          
            },
            itemNotExistsInLocation: function(getItemValues, locationCode,locationId) {
                fullScreenLoader.stopLoader();
                $.ajax({
                    showLoader: false,
                    data: {
                        locationcode: locationCode,
                        removeval: getItemValues,
                        branch: locationCode,
                        searchpopup: true
                    },
                    url: url.build('branchpickup/pickup/cartpopup'),
                    type: "POST",
                    //dataType:'json',
                }).done(function(data) {
                    var options = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        title: 'The following items are not available from your selected pickup branch'
                    };
                    $("#show-branch-search-cartpopup_"+locationId).empty();
                    var popup = modal(options, $('#branchpickup-search-iframe-popup-modal_'+locationId));
                    $('#branchpickup-search-iframe-popup-modal_'+locationId).modal('openModal');
                    $('#show-branch-search-cartpopup_'+locationId).append(data);
                    $('.modal-footer').hide();
                  
                });
            }            
        };
    }
);