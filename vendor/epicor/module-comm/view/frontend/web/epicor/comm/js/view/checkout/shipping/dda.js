/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define([
    'jquery',
    'ko',
    'uiComponent'

], function ($, ko, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Epicor_Comm/checkout/shipping/dda-block'
        },
        initialize: function (config) {
            this.offices = ko.observableArray();
            this.selectedOffice = ko.observable();
            this._super();
        },
    });
});
