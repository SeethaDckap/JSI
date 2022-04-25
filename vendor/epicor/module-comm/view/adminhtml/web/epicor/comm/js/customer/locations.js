/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

document.observe('dom:loaded', function () {
    if ($('locations_source')) {
        toggleLocationsEdit();

        Event.observe($('locations_source'), "change", function () {
            toggleLocationsEdit()
        });
    }
});

function toggleLocationsEdit() {
    if ($('locations_source').value == 'erp') {
        disableLocationsEdit();
    } else {
        enableLocationsEdit();
    }
}

function disableLocationsEdit() {
    $$('.location_set .checkbox').each(function (e) {
        if (!e.disabled) {
            e.disabled = true;
        } else {
            return false;
        }
    });
}

function enableLocationsEdit() {
    $$('.location_set .checkbox').each(function (e) {
        if (e.disabled) {
            e.disabled = false;
        } else {
            return false;
        }
    });
}