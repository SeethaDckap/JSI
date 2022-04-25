/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define([
    "jquery",
    "jquery/ui",
    'Magento_Checkout/js/model/error-processor',
    'mage/cookies'
], function ($, ui, errorProcessor) {
    "use strict";
    $.widget('epicor.lazyLoad', {
        options: {
            sendMessageUrl: null,
            productIds: null,
            loaderImageUrl: null,
            viewType: "list",
            dom_append_selector: ".ecc-price-lazy-load-",
            loader_container: '[data-role="ecc-loader-pannel"]',
            bundle_option_container: '.columns .main .product.media',
            isStockUpdate: true
        },
        _create: function () {
            this._bind();
        },
        _bind: function () {
            var self = this;
            if (self.options.viewType === 'bestseller_product' || self.options.viewType === 'featured_product' || self.options.viewType === 'newsale_product' ) {
                self.widgetAppendLoader();
            } else {
                self.appendLoader();
            }

            self.requestMsq();
        },
        requestMsq: function () {
            var self = this;
            var loaderContainer = self.options.loader_container;
            var data = {'productIds': self.options.productIds};
            var view = self.options.viewType;
            data['isStockUpdate'] = self.options.isStockUpdate;
            data['type'] = view;
            data['allow_url'] = 1;

            $.ajax({
                url: self.options.sendMessageUrl,
                data: data,
                type: 'POST',
                dataType: 'json',
                cache: false
            }).done(
                function (response) {
                    if (response.success) {
                        switch (view) {
                            case "related":
                            case "upsell":
                            case "substitute":
                            case "crosssell":
                            case "new":
                            case "list": // ELEMENT_NODE
                            case "compare":
                            case "wishlist":
                                self.renderPriceList(response.productList);
                                break;
                            case "newsale_product":
                            case "bestseller_product":
                            case "featured_product":
                                self.renderWidgetPriceList(response.productList);
                                break;

                            case "view": // COMMENT_NODE
                                self.renderPriceView(response);
                                break;
                        }
                    } else {
                        alert(response.error);
                        $(loaderContainer).trigger('hide.loader');
                    }
                }
            ).fail(
                function (response) {
                    errorProcessor.process(response);
                    $(loaderContainer).trigger('hide.loader');
                }
            );
        },
        renderPriceList: function (priceList) {
            var self = this;
            var view = self.options.viewType;
            var domAppendSelector = self.options.dom_append_selector + view;
            var loaderContainer = self.options.loader_container;
            $.each(priceList, function (index, value) {
                var appendSelector = $(domAppendSelector + '[ecc-data-product-id="' + index + '"]');

                if (appendSelector[0]) {
                    appendSelector.append(value);
                    appendSelector.find(loaderContainer).trigger('hide.loader');
                }
            });
            var eachProduct = $$(".ecc-price-lazy-load-list > [data-role='ecc-loader-pannel'] >.loader");

            eachProduct.each(function(element, index){
                jQuery(element).parent().append("</br>Product Currently Unavailable");
                $(element).hide();
            });
            // trigger Js content loaded by ajax for add to cart button
            $(domAppendSelector).find('[name=form_key]').attr('value', $.mage.cookies.get('form_key'));
            $(domAppendSelector).find('[data-role=tocart-form], .form.map.checkout').attr('data-mage-init', JSON.stringify({'catalogAddToCart': {}}));
            $(domAppendSelector).trigger('contentUpdated');
        },
        renderWidgetPriceList: function (priceList) {
            var self = this;
            var view = self.options.viewType;
            var domAppendSelector = self.options.dom_append_selector + view;
            var loaderContainer = self.options.loader_container;
            $.each(priceList, function (index, value) {
                var appendSelector = $(domAppendSelector + '[ecc-data-product-id="' + index + '"]');
                if (appendSelector[0] && appendSelector.find('.price-box price-final_price').length == 0) {
                    appendSelector.append(value);
                    appendSelector.find(loaderContainer).trigger('hide.loader');
                }
            });

            var eachFeaturedProduct = $$(".home-prod-desc > [data-role='ecc-loader-pannel'] >.loader");
            eachFeaturedProduct.each(function(element, index){
                if(jQuery(element).parent().has('span.feature-un').length == 0) {
                    jQuery(element).parent().append("</br><span class='feature-un'>Product Currently Unavailable</span>");
                }
                $(element).hide();
            });

            // trigger Js content loaded by ajax for add to cart
            $(domAppendSelector).find('[name=form_key]').attr('value', $.mage.cookies.get('form_key'));
            $(domAppendSelector).find('[data-role=tocart-form], .form.map.checkout').attr('data-mage-init', JSON.stringify({'catalogAddToCart': {}}));
            $(domAppendSelector).trigger('contentUpdated');
        },
        renderPriceView: function (html) {
            var self = this;
            var view = self.options.viewType;
            var domAppendSelector = self.options.dom_append_selector + view;
            var loaderContainer = self.options.loader_container;
            var bundleOptionContainer = self.options.bundle_option_container;

            $(domAppendSelector).append(html.page);
            if(html.bundleOptionContainer) {
                $(bundleOptionContainer).after(html.bundleOptionContainer);
            }
            $(domAppendSelector).trigger('contentUpdated');
            $(domAppendSelector).find('[name=form_key]').attr('value', $.mage.cookies.get('form_key'));
            $(domAppendSelector).find(loaderContainer).trigger('hide.loader');
        },
        appendLoader: function () {
            var self = this;
            var view = self.options.viewType;
            var domLoaderAppendSelector = $(self.options.dom_append_selector + view);
            var loaderHtml = '<div data-role="ecc-loader-pannel" >';
            loaderHtml += '<div class="loader">';
            loaderHtml += '<img src="' + self.options.loaderImageUrl + '" alt="Loading..." >';
            loaderHtml += '</div></div>';
            domLoaderAppendSelector.append(loaderHtml);
        },
        widgetAppendLoader: function () {

            var self = this;
            var view = self.options.viewType;
            var domAppendSelector = self.options.dom_append_selector + view;
            var domLoaderAppendSelector;
            var loaderHtml = '<div data-role="ecc-loader-pannel" >';
            loaderHtml    += '<div class="loader">';
            loaderHtml    += '<img src="' + self.options.loaderImageUrl + '" alt="Loading..." >';
            loaderHtml    += '</div></div>';
            $.each(self.options.productIds, function (index, value) {
                domLoaderAppendSelector = $(domAppendSelector + '[ecc-data-product-id="' + value + '"]');
                domLoaderAppendSelector.append(loaderHtml);
            });
        }
    });
    return $.epicor.lazyLoad;
});
