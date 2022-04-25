define([
    'underscore',
    'jquery',
], function (_, $) {
    'use strict';
    $(document).on('click', 'button.default-order-button', function(){
        window.location.href = "/quickorderpad/form";
    });
});