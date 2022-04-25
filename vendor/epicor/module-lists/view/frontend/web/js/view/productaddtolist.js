/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define([
    "jquery",
    "mage/translate",
    "Magento_Ui/js/modal/modal",
    'Magento_Ui/js/modal/alert',
    'mage/mage'
], function ($, $tr, modal,alert) {

    $(document).ready(function() {
        $(".product-item-actions .box-addtolists").parent().parent().addClass("hasaddtolist");
    });
    var listsApp = function (config, el) {

        /**
         * Initiate events.
         *
         * @return void
         */
        var events = function () {
            $('#' + el.id).click( function (e) {
                if(config.page == 'detailedpage') {
                    var dataForm = $('#product_addtocart_form');
                    dataForm.mage('validation', {});
                    if(!dataForm.validation('isValid')){
                        return false;
                    }
                    config.productinfo.product = $('input[name="product"]').val();
                    config.productinfo.qty = $('input[name="qty"]').val();
                    config.productinfo.location_code = $('input[name="location_code"]').val();
                    config.productinfo.selected_configurable_option = $('input[name="selected_configurable_option"]').val();
                    config.productinfo.products = {};
                    config.productinfo.super_group_locations={};
                    $("input[name^='super_group_locations']")
                        .map(function(){
                            var name=$(this).attr('name');
                            config.productinfo.super_group_locations[name] = $(this).val();
                        }).get();
                    $("input[name^='super_group']")
                        .map(function(){
                            var name=$(this).attr('name');
                            config.productinfo.super_group_locations[name] = $(this).val();
                        }).get();
                    $("input[name^='products']")
                        .map(function(){
                            var name=$(this).attr('name');
                            config.productinfo.products[name] = $(this).val();
                        }).get();

                }
                openModal('addtolist-popup-modal-' + config.productId);
            });
        }

        /**
         * Opens the locations display modal.
         *
         * @param id
         * @return void
         */
        var openModal = function (id) {
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: 'Add To List',
                buttons: []
            };
            var popup = modal(options, $('#' + id));
            $('#' + id).empty();
            $('#' + id).modal("openModal");
            getLocations(id);
        }

        /**
         * Fetches locations from the back-end.
         *
         * @param int productId
         * @return void
         */
        var getLocations = function (modalId) {
            $.post(
                '/lists/lists/saveCartAsList',
                {
                    productId: config.productId,
                    productinfo:config.productinfo,
                    page:config.page,
                    addToCartReturnUrl: window.location.href,
                    selectedOption: 4
                },
                function (data, status) {
                    if(data.status == 'error'){
                        alert({
                            title: $.mage.__('Error'),
                            content: data.errormessage
                        });
                        $('#' + modalId).modal('closeModal');
                        return;
                    }

                    $('.modal-title').text('Add To List');
                    $('#' + modalId).append(data.listGrid);
                }
            )
        }
        
        events(); // initiate the location application
    }
    return listsApp;
})