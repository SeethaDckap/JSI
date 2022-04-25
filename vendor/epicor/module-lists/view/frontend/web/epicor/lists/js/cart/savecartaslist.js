define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'mage/url',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal',
], function ($, ko, Component, customerData, url, confirm, alert, modal) {
    'use strict';
    return Component.extend({
       title: 'Save Cart As List',
       initialize: function (){

           var saveCartAsListOptions = window.checkout.saveCartAsListOptions;
           this._super();
           var enableCartToListAt = window.checkout.enableCartToListAt;
           // if add cart to list not available for minicart,
           if (enableCartToListAt.indexOf('M') == -1) {
               if ($('.block-minicart #save-cart-as-list')) {
                   $('.block-minicart #save-cart-as-list').remove();
               }
           }
           // if customer has not logged in remove the save-cart-as list button
           if(!window.checkout.customerLoggedIn){
              $('#save-cart-as-list').remove();
           }else{
               $('#save-cart-as-list').show();
           }

       },
       saveCartOptionsPopup: function () {
           var optionSelected = 0;
           var optionsAvailableArray = [];
           if(window.checkout.saveCartAsListOptions.indexOf('Q') > -1){
               optionsAvailableArray.push(1);
               optionSelected = 1;
           }
           if(window.checkout.saveCartAsListOptions.indexOf('A') > -1){
               optionsAvailableArray.push(2);
               optionSelected = 2;
           }
           if(window.checkout.saveCartAsListOptions.indexOf('E') > -1){
               optionsAvailableArray.push(3);
               optionSelected = 3;
           }
           //only show options available popup if more than one option available else go directly to saveCartAsList
           if(Object.keys(optionsAvailableArray).length > 1){
               this.showOptionsPopup(optionsAvailableArray);
           }else{
               // pass preselected option if only one available
               this.saveCartAsListAjax(optionSelected);
           }

       },
        showOptionsPopup: function (optionsAvailable) {
            var validOptions =  {"1": "Quick Save", "2": "Advanced Save", "3": "Existing List"};
            var radioButtons = "<form id='optionsAvailable'><h2>Save Cart As:</h2></br>";

            optionsAvailable.forEach(function(value, index){
                if(index == 0){
                    radioButtons += "<input type='radio' id='confirmRadioButton_'"+index+" name='validOptions' value='"+value + "' checked />" + validOptions[value] + "</br>";
                }else{
                    radioButtons += "<input type='radio' id='confirmRadioButton_'"+index+" name='validOptions' value='"+value + "' />" + validOptions[value] + "</br>";

                }
            })
            radioButtons += "</form>";
            var componentThis = this;
            confirm({
                content: radioButtons,
                modalClass: 'confirm savecartaslistoptions',
                actions: {
                    confirm: function () {					//action on confirm button press
                        componentThis.saveCartAsListAjax();
                        return true;

                    },
                    cancel: function () {
                        return false;
                    }


                },
            });
       },
       saveCartAsListAjax: function (optionSelected) {
           var selectedOption = 0;
           if(optionSelected){
               selectedOption = optionSelected;
           }else{
               selectedOption = $("#optionsAvailable input[name='validOptions']:checked").val();
           }
            var webOrderNumber = $('#web_order_number').text();
            var erpOrderNumber = $('#erp_order_number').text();
            var componentThis = this;
            $.ajax({
                showLoader: true,
                data: {
                    selectedOption: selectedOption,
                    callingUrl: window.location.pathname,
                    webOrderNumber: webOrderNumber,
                    erpOrderNumber: erpOrderNumber,
                },
                url: url.build('lists/lists/saveCartAsList'),
                type: "POST",
                dataType:'json',
                success: function(data) {
                    //send alert confirming the cart saved
                    if(data.redirect){
                        var redirectUrl = window.location.protocol + '//' + window.location.hostname + data.redirect + 'parms/' + data.redirect_parms ;
                        window.location.replace(redirectUrl);
                        return;
                    }
                    if(data.listGrid){
                        componentThis.listGridPopup(data.listGrid);
                        return;
                    }
                    alert({
                        title: 'Cart is Saved As List',
                        content: data.list
                    });
                },
                error: function (xhr, status, errorThrown) {
                    console.log('An Error has occurred. Please resubmit');
                }
            }).done(function(data) {
                return true;
            });
        },
        listGridPopup: function (listGrid) {

            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: "Add Cart to Existing List",
                buttons: [
                    {
                        text: $.mage.__('Cancel'),
                        class: 'cancel',
                        click: function () {
                            this.closeModal();
                        }
                    }
                ]
            };
            //remove all parts of list grid that are not relevant for this popup
            var listGridData = $('<div id="list_grid_popup"/>').append(listGrid);
            listGridData.find('#listgrid_massaction-mass-select').remove();
            listGridData.find('#listgrid_massaction-form').remove();
            listGridData.find('td.col-massaction').remove();
            listGridData.find("button.action-default[title ^= 'Add New List']").remove();
            listGridData.find('.data-grid-th.empty').remove();     // make more specific
            //initialize modal with options and content
            modal(options, listGridData);

            //open modal
            listGridData.modal('openModal');
        }
    });

});