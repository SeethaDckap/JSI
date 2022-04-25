/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

function populateMasqueradeSelectParent(value) {
    var rowId = value.trim();
    $$('select#masquerade_as option').each(function(o) {
         if (o.value == rowId) {
              o.selected = true;
         }
      });
    $('window-overlay').hide();
    event.stop();
    return false;
}