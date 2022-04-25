/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

require([
    "jquery",
    "mage/calendar"
], function ($) {
    $(".datepicker").calendar({
        showsTime: false,
        dateFormat: "yy-M-dd",
        buttonText: "Select Date",
        changeMonth: true,
        changeYear: true,
        showOn: "both"
    });
});