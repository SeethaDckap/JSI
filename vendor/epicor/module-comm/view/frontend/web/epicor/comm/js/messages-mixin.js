/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery'
], function($) {
    'use strict';
    return function(targetModule) {
        return targetModule.extend({
            isVisible: function () {
                if(!this.messageContainer.hasMessages()){
                    var self = this;
                    $(self.selector).hide();
                }
                return this.isHidden(this.messageContainer.hasMessages());
            },
            onHiddenChange: function (isHidden) {
                var self = this;
                var element = $(self.selector);
                this.onErrorFocus(element);
            },
            onErrorFocus: function (errorElement) {
                if (typeof errorElement !== 'undefined') {
                    var windowHeight = $(window).height();
                    $('html, body').animate({
                        scrollTop: errorElement.parent().closest('div').offset().top - windowHeight / 3
                    });
                }
            }
        });
    };

});