/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'underscore'
], function ($, Component, customerData, _) {
    'use strict';
    var config = {
        'url':''
    };
    return Component.extend({

        /** @inheritdoc */
        initialize: function () {
            this._super();
            this.customerLists = customerData.get('customer-lists');
            return this;
        },


        /**
         * Check if items is_saleable and change add to cart button visibility.
         */
        autosearch: function () {
            $.ajax({
                method: "POST",
                dataType: "json",
                url: this.url,
                data: {list_q: $("#listssearch").val(),form_key: $.cookie('form_key')},
                complete: function (request) {
                    $("#sidebar-lists").html(request.responseText);
                }.bind(this),
            });
        },

        /** @inheritdoc */
        initObservable: function () {
            this._super();
            return this;
        },
    });
});
