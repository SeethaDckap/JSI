/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * @author aakimov
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery'
], function($) {
    'use strict';
    return function(targetModule) {
        return targetModule.extend({
            initialize: function() {
                var result = this._super();
                $(window).on('beforeunload', function(){
                    $.cookieStorage.set('mage-messages', '');
                });
                return result;
            }
        });
    };

});