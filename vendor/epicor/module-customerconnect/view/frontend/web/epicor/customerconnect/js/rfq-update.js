/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

require([
    'jquery',
    'prototype'
], function (jQuery) {
    jQuery(function () {

        window.parent.$('loading-mask').hide();
        window.parent.$('rfq_update').update($('rfq_update').innerHTML);
        window.parent.showMessage('RFQ update request sent successfully', 'success');
        jQuery('.loading-mask', window.parent.document).css('display', 'none');
        window.parent.resetDeliveryAddress();
        if (typeof window.parent.salesrepPricing !== "undefined" && typeof window.parent.salesrepPricing.resetSalesRepPricing === "function") {
            window.parent.salesrepPricing.resetSalesRepPricing();
        }
        if (typeof window.parent.dealerPricing !== "undefined" && typeof window.parent.dealerPricing.resetDealerPricing === "function") {
            window.parent.dealerPricing.resetDealerPricing();
        }
        window.parent.$('rfq-form-iframe').writeAttribute('src','');
    });
});