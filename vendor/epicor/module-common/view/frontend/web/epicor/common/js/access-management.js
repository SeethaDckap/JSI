/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


document.observe('dom:loaded', function() {
    if ($('group_edit_save')) {
        $('group_edit_save').observe('click', function() {
            $('group_edit_form').submit();
        });
    }
});