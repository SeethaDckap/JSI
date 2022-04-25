/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

define([
    'jquery',
    'ko',
    'uiComponent',    
    'mageUtils',
    'uiLayout',    
    'domReady!'

], function ($, ko, Component, utils, layout) {
    'use strict';
    
    var defaultRendererTemplate = {
        parent: 'checkout.steps.shipping-step.shippingAddress.address-list',
        name: '${ $.$data.name }',
        component: 'Magento_Checkout/js/view/shipping-address/address-renderer/default'
    };

    return Component.extend({
        defaults: {
            template: 'Epicor_SalesRep/checkout/shipping/contact',            
            isContactShow: ko.observable(false),
            errorValidationMessage: ko.observable(false)
        },        
        initialize: function (config) {
            this._super();
            this.config = config;            
            this.isContactShow(this.config.iscontactShow);
        },        
        objectToArray: function (object) {
            var convertedArray = [];
            $.each(object, function (key) {
                return typeof object[key] === 'string' ? convertedArray.push(object[key]) : false;
            });

            return convertedArray.slice(0);
        },
        
        /**
         * Create new component that will render given address in the address list
         *
         * @param address
         * @param index
         */
        createRendererComponent: function (address, index) {

            // rendererTemplates are provided via layout
            var rendererTemplate = (address.getType() != undefined && this.rendererTemplates[address.getType()] != undefined)
                    ? utils.extend({}, defaultRendererTemplate, this.rendererTemplates[address.getType()])
                    : defaultRendererTemplate;
            var templateData = {
                parentName: this.name,
                name: index
            };
            var rendererComponent = utils.template(rendererTemplate, templateData);
            utils.extend(rendererComponent, {address: ko.observable(address)});
            layout([rendererComponent]);
            this.rendererComponents[index] = rendererComponent;

        }
    });
});
