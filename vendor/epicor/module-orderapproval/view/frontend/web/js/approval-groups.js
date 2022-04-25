/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define([
    'jquery'
], function ($) {
    'use strict';
    $(document).on('click', '#submit-group', function(){
        let mainForm = $('#approval_group_form');
        if(mainForm.length > 0){
            if (mainForm.validation() && mainForm.validation('isValid')) {
                mainForm.submit();
            }
        }
    });
});
