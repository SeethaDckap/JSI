/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

if (typeof Epicor_Lists == 'undefined') {
    var Epicor_Lists = {};
}

function populateListsSelect(row, event) {
    var trElement = event.findElement('tr');
    window.parent.updateFieldListValue(trElement.readAttribute('title'));
}
