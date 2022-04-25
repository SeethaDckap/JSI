define([
    'jquery'
], function ($) {
    'use strict';

    return function (widget) {

        $.widget('mage.configurable', widget, {

            _fillSelect: function (element) {
                //display the configurable price difference or not? if yes, goto parent method
                if (window.checkout.displayConfigurablePriceDiff  == "1") {
                    this._super(element);
                }else
                {
                    var attributeId = element.id.replace(/[a-z]*/, ''),
                        options = this._getAttributeOptions(attributeId),
                        prevConfig,
                        index = 1,
                        allowedProducts,
                        i,
                        j;

                    this._clearSelect(element);
                    element.options[0] = new Option('', '');
                    element.options[0].innerHTML = this.options.spConfig.chooseText;
                    prevConfig = false;

                    if (element.prevSetting) {
                        prevConfig = element.prevSetting.options[element.prevSetting.selectedIndex];
                    }

                    if (options) {
                        for (i = 0; i < options.length; i++) {
                            allowedProducts = [];

                            /* eslint-disable max-depth */
                            if (prevConfig) {
                                for (j = 0; j < options[i].products.length; j++) {
                                    // prevConfig.config can be undefined
                                    if (prevConfig.config &&
                                        prevConfig.config.allowedProducts &&
                                        prevConfig.config.allowedProducts.indexOf(options[i].products[j]) > -1) {
                                        allowedProducts.push(options[i].products[j]);
                                    }
                                }
                            } else {
                                allowedProducts = options[i].products.slice(0);
                            }

                            if (allowedProducts.length > 0) {
                                options[i].allowedProducts = allowedProducts;
                                element.options[index] = new Option(this._getOptionLabel(options[i]), options[i].id);

                                if (typeof options[i].price !== 'undefined') {
                                    element.options[index].setAttribute('price', options[i].prices);
                                }

                                element.options[index].config = options[i];
                                index++;
                            }

                            /* eslint-enable max-depth */
                        }
                    }
                }
            }

        });

        return $.mage.configurable;
    }
});