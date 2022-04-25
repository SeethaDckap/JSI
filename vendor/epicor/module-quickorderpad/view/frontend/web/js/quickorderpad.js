/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

require([
    'jquery', 
    'jquery/ui', 
    'mage/validation'
    ], function ($) {
    if ($('#qop-basket')) {
        $('#qop-basket').toggleClass('expanded');
    }

    if ($('#qop-expand')) {
        $('#qop-expand').on('click', function (event) {
            toggleBasket($('#qop-basket'), $('#qop-expand'));
            setHeight();
        });
    }

    if ($('#qop-pin')) {
        $('#qop-pin').on('click', function (event) {
            var current_width = $('#qop-basket').width();

            $('#qop-basket').toggleClass('pinned');

            if ($('#qop-basket').hasClass('pinned')) {
                alert($('#qop-expand'));
                collapseBasket($('#qop-basket'), $('#qop-expand'));
                $('#qop-basket').setStyle({
                    'width': current_width.toString() + 'px'
                });
                $('#qop-pin').writeAttribute('title', 'Unpin');
            } else {
                expandBasket($('#qop-basket'), $('#qop-expand'));
                $('#qop-pin').writeAttribute('title', 'Pin');
            }
            setHeight();
        });
    }
});
/*
require([
    'Magento_Customer/js/customer-data'
], function (customerData) {
    var sections = ['cart'];
    customerData.invalidate(sections);
    customerData.reload(sections, true);
});
*/
function setHeight() {
    if ($('#qop-basket').hasClass('pinned')) {
        var current_height = $('#qop-cart').height();
        if (current_height > 360) {
            $('#qop-cart').setStyle({
                'height': '360px',
                'overflowY': 'scroll'
            });
        }
    } else {
        $('#qop-cart').setStyle({
            'height': 'auto',
            'overflowY': 'auto'
        });
    }
}

function toggleBasket(basket, expand) {
    basket.toggleClass('collapsed');
    basket.toggleClass('expanded');
    if (basket.hasClass('collapsed')) {
        expand.writeAttribute('title', 'Expand');
    } else {
        expand.writeAttribute('title', 'Collapse');
    }
}

function expandBasket(basket, expand) {
    basket.removeClass('collapsed');
    basket.addClass('expanded');
    expand.writeAttribute('title', 'Collapse');
}

function collapseBasket(basket, expand) {
    basket.addClass('collapsed');
    basket.removeClass('expanded');
    expand.writeAttribute('title', 'Expand');
}
