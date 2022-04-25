/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    "jquery",
    'Epicor_OrderApproval/js/budgets/update-end-date'
], function ($, endDate) {
    return function (config) {
        $(document).on('change','#add_budget_form input#start_date', function (e) {
            if(endDate.isSet()){
                endDate.getEndDate();
            }
        });

        $(document).on('change','#add_budget_form input#duration', function (e) {
            if(endDate.isSet()){
                endDate.getEndDate();
            }
        });

        $(document).on('change','#add_budget_form select#budget_type', function (e) {
            if(endDate.isSet()){
                endDate.getEndDate();
            }
        });
    };
});