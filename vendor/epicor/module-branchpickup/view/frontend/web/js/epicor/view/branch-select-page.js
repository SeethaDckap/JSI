/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/checkout-data',
    'Magento_Ui/js/modal/modal',
    'Magento_Checkout/js/model/full-screen-loader',
    'mage/url',
    'jquery/ui'
], function($, ko, Component,  checkoutData, modal, fullScreenLoader, url) {
    'use strict';

    return {
        selectBranchPickupAddress: function(LocationCode) {
            var checkAddress = this;
            var locationCode = LocationCode;
            fullScreenLoader.startLoader();
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
                    checkAddress.itemNotExistsInLocation(getItemValues, locationCode)
                } else {
                    checkAddress.updateQuoteBranchPickupAddress(LocationCode);
                    checkoutData.setSelectedShippingAddress('customer-address'+locationCode);
                    
                }
            });
        },
        removeBranchPickupAddress: function () {
          var checkAddress = this
          var locationCode = null
          fullScreenLoader.startLoader()
          $.ajax({
            showLoader: true,
            data: {
              locationcode: locationCode
            },
            url: url.build('branchpickup/pickup/changepickuplocation'),
            type: 'POST',
          }).done(function (data) {
            var getType = data.type
            var getItemValues = data.values
            if ((getType == 'success') && (Object.keys(getItemValues).length != 0)) {
              checkAddress.itemNotExistsInLocation(getItemValues, locationCode)
            } else {
             checkAddress.updateQuoteAfterBranchRemove(locationCode)
             checkoutData.setSelectedShippingAddress('customer-address' + locationCode)
            }
          })
        },
        itemNotExistsInLocation: function(getItemValues, locationCode) {
            fullScreenLoader.stopLoader();
            if ($('#branchpickup-iframe-popup-modal').length) {
                if($("#selectgrid").length) {
                    $('#branchpickup-iframe-popup-modal').remove();
                }
                $('#show-branch-cartpopup').remove();            
            }
            $.ajax({
                showLoader: true,
                data: {
                    locationcode: locationCode,
                    removeval: getItemValues,
                    branch: locationCode
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
                if($("#selectgrid").length) {
                    $("#selectgrid").append("<div id='branchpickup-iframe-popup-modal'></div>");
                }
                $("#branchpickup-iframe-popup-modal").append("<div id='show-branch-cartpopup'></div>");
                var popup = modal(options, $('#branchpickup-iframe-popup-modal'));
                $('#branchpickup-iframe-popup-modal').modal('openModal');
                $('#show-branch-cartpopup').append(data);
                $('.modal-footer').hide();
            });
        },
        showEditLocationPopup: function(locationCode) {
            fullScreenLoader.stopLoader();
            if ($('#branchpickup-edit-popup-modal').length) {
                $('#branchpickup-edit-popup-modal').remove();
                $('#show-branch-editpopup').remove();            
            }            
            $.ajax({
                showLoader: true,
                data: {
                    locationcode: locationCode
                },
                url: url.build('branchpickup/pickup/location'),
                type: "POST",
                //dataType:'json',
            }).done(function(data) {
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    title: 'Please Fill the Mandatory values'
                };
                $("#selectgrid").append("<div id='branchpickup-edit-popup-modal'></div>");
                $("#branchpickup-edit-popup-modal").append("<div id='show-branch-editpopup'></div>");
                var popup = modal(options, $('#branchpickup-edit-popup-modal'));
                $('#branchpickup-edit-popup-modal').modal('openModal');
                $('#show-branch-editpopup').append(data);
                $('.modal-footer').hide();
            });            
        },        
        updateQuoteBranchPickupAddress: function(LocationCode) {
            var checkAddress = this;
            var locationCode = LocationCode;
            $.ajax({
                showLoader: true,
                data: {
                    locationcode: locationCode
                },
                url: url.build('branchpickup/pickup/SaveLocationQuote'),
                type: "POST",
                //dataType:'json',
            }).done(function(data) {
                checkoutData.setSelectedShippingAddress('customer-address'+locationCode);
                window.location.reload();
            });
        },
        updateQuoteAfterBranchRemove: function(LocationCode) {
            var checkAddress = this;
            var locationCode = LocationCode;
            $.ajax({
                showLoader: true,
                data: {
                    locationcode: locationCode
                },
                url: url.build('branchpickup/pickup/removebranchpickup'),
                type: "POST",
                //dataType:'json',
            }).done(function(data) {
                checkoutData.setSelectedShippingAddress('customer-address'+locationCode);
                window.location.reload();
            });
        },
        saveEditlocation: function(serializeVals) {
            var checkAddress = this;
            $.ajax({
                showLoader: true,
                data: serializeVals,
                url: url.build('branchpickup/pickup/savelocation'),
                type: "POST",
                //dataType:'json',
            }).done(function(data) {
               $(".action-close").click(); // Close pop modal
               if (data.type == "success") {
                    var datas = data.data;
                    var locationCode = datas.locationcode;
                    $("input#branchfieldsmissing_"+datas.locationid).val(false);
                    if ($('#errorBranchlink_'+datas.locationid).length > 0) {
                        $('#errorBranchlink_'+datas.locationid).remove();
                    }
                    checkAddress.selectBranchPickupAddress(locationCode);
                } else {
                    window.location.reload();
                }
            });            
        }
    };
});