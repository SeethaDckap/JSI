/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery',
    'rjsResolver'
], function ($, resolver) {
        'use strict';
        var containerId = '#checkout';
        return {
            showErrorMessage: function (showMessage) {
              setTimeout(function(){ alert(showMessage); },1000);
            },
            showLoadedRedirect: function() {
                $(".loading-mask",window.parent.parent.document).show();
            },
            closeElementsIframePopup: function() {
                $( ".action-close", window.parent.document).trigger( "click" );
            }, 
            placeOrderClick: function () {
              // $(this.getSelector('submit')).trigger('click');
               $('#elementsPlaceOrder', window.parent.document).trigger('click');
               this.showLoadedRedirect();
            },            
            getSelector: function (field) {
                return '#elements' + '_' + field;
            },                       
        };
    }
);