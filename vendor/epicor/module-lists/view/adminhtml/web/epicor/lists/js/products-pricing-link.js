define([
    "jquery"
], function ($) {
    'use strict';
    return {
        /**
         * When pricing link is available make it not clickable
         * when the row is clicked and deselected
         * @param pricingLink
         */
        togglePricingLink: function (pricingLink) {
            if (this.isValidPricingLink(pricingLink)) {
                if (this.isPricingLinkEnabled(pricingLink)) {
                    pricingLink.replaceWith(this.getPricingLinkDisabledHtml());
                }
                if (this.isPricingLinkDisabled(pricingLink)) {
                    pricingLink.replaceWith(this.getPricingLinkEnabledHtml());
                }
            }
        },
        isValidPricingLink: function (pricingLink) {
            return pricingLink && pricingLink instanceof jQuery && pricingLink.length;
        },
        isPricingLinkEnabled: function (pricingLink) {
            if (this.isValidPricingLink(pricingLink)) {
                return pricingLink.hasClass('enabled-pricing-link')
            }
        },
        isPricingLinkDisabled: function (pricingLink) {
            if (this.isValidPricingLink(pricingLink)) {
                return pricingLink.hasClass('disabled-pricing-link')
            }
        },
        getPricingLinkEnabledHtml: function () {
            return '<a ' + this.getPricingLinkEnabledClass() + ' ' + this.getPricingLinkEnabledOnClick()
                + ' href="javascript:void(0);">Pricing</a>'
        },
        getPricingLinkDisabledHtml: function () {
            return '<span class="disabled-pricing-link pricing-link" style="color:dimgrey">Pricing</span>'
        },
        getPricingLinkEnabledClass: function () {
            return 'class="enabled-pricing-link pricing-link"';
        },
        getPricingLinkEnabledOnClick: function () {
            return 'onClick="return listProduct.pricing(this, event);"';
        }


    };

});
