/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

require([
    'prototype'
], function () {
    document.observe('dom:loaded', function () {
        if ($('type')) {
            Event.observe($('type'), "change", function () {
                checkTypes();
            });
            checkTypes();
        }
    });

    function checkTypes() {
        var chosenType = $('type').value;
        if ($('supported_settings_' + chosenType)) {
            var supportedSettings = $('supported_settings_' + chosenType).value.split('');
            var allSettings = $('supported_settings_all').value.split('');
            for (i = 0; i < allSettings.length; i++) {
                if (supportedSettings.indexOf(allSettings[i]) == -1) {
                    $('settings_' + allSettings[i]).checked = false;
                    $('settings_' + allSettings[i]).parentNode.hide();
                } else {
                    $('settings_' + allSettings[i]).parentNode.show();
                }
            }
        }
    }
});
