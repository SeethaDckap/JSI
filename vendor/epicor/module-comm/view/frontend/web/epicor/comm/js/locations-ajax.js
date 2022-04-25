/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define([
    "jquery",
    "mage/translate",
    "Magento_Ui/js/modal/modal"
], function ($, $tr, modal) {
    var locationApp = function (config, el) {

        /**
         * Initiate events.
         *
         * @return void
         */
        var events = function () {
            $('#' + el.id).click( function (e) {
                openModal('locations_list_' + config.modalId + '_block');
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
                title: 'Finding locations...',
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
                '/comm/product/locations',
                {
                    productId: config.productId,
                    productCategory: config.productCategory,
                    addToCartReturnUrl: window.location.href
                },
                function (data, status) {
                    $('.modal-title').text('Available locations');
                    $('#' + modalId).append(data);
                }
            )
        }
        
        events(); // initiate the location application
    }
    return locationApp;
})