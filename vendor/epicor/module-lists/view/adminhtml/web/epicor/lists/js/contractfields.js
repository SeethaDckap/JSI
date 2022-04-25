/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

require([    
    'prototype'
], function () {
document.observe('dom:loaded', function () {
    initToogleContractFields();
});

function initToogleContractFields() {
    if ($('type')) {
        Event.observe($('type'), "change", function () {
            toggleContractFields();
        });
        toggleContractFields();
    }
}

function toggleContractFields() {
    if ($('type').value == 'Co') {
        $('typespecific').show();
        $$('h4.fieldset-legend').each(function (a) {
            if (a.innerHTML == 'Type Specific Details') {
                a.parentNode.show();
            }
        });
    } else {
        $$('h4.fieldset-legend').each(function (a) {
            if (a.innerHTML == 'Type Specific Details') {
                a.parentNode.hide();
            }
        });
        $('typespecific').hide();
    }
}
});