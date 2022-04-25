require([
    "jquery",
], function ($) {
    $(document).ready(function () {
        // list all overwrite on update ids for MSQ
        var locationFields = ['row_epicor_comm_enabled_messages_msq_request_location_stock_status'
            , 'row_epicor_comm_enabled_messages_msq_request_location_free_stock'
            , 'row_epicor_comm_enabled_messages_msq_request_location_minimum_order_qty'
            , 'row_epicor_comm_enabled_messages_msq_request_location_maximum_order_qty'
            , 'row_epicor_comm_enabled_messages_msq_request_location_lead_time_days'
            , 'row_epicor_comm_enabled_messages_msq_request_location_lead_time_text'
            , 'row_epicor_comm_enabled_messages_msq_request_location_pricing'
        ];
        var overwriteOnUpdate = ['row_epicor_comm_enabled_messages_msq_request_field_heading'
            , 'row_epicor_comm_enabled_messages_msq_request_product_manage_stock_update'
            , 'row_epicor_comm_enabled_messages_msq_request_product_min_order_qty_update'
            , 'row_epicor_comm_enabled_messages_msq_request_product_max_order_qty_update'
            , 'row_epicor_comm_enabled_messages_msq_request_lead_time_days_update'
            , 'row_epicor_comm_enabled_messages_msq_request_lead_time_text_update'
            , 'row_epicor_comm_enabled_messages_msq_request_currencies_update'
            , 'row_epicor_comm_enabled_messages_msq_request_free_stock_update'
            , 'row_epicor_comm_enabled_messages_msq_request_locations_update'
            , 'row_epicor_comm_enabled_messages_msq_request_location_stock_status'
            , 'row_epicor_comm_enabled_messages_msq_request_location_free_stock'
            , 'row_epicor_comm_enabled_messages_msq_request_location_minimum_order_qty'
            , 'row_epicor_comm_enabled_messages_msq_request_location_maximum_order_qty'
            , 'row_epicor_comm_enabled_messages_msq_request_location_lead_time_days'
            , 'row_epicor_comm_enabled_messages_msq_request_location_lead_time_text'
            , 'row_epicor_comm_enabled_messages_msq_request_location_pricing'
        ];

        //only display above admin config values if either schedulemsq or msq_after_stk set to y
        msqoverwriteonupdate(overwriteOnUpdate, locationFields);
        msqoverwriteonupdatelocations(locationFields);

        //if schedulemsq or msq after stk change to yes, make sure the msq overwrite on update section is displayed
        //else do not show

        $("#epicor_comm_enabled_messages_msq_request_scheduledmsq, " +
            "#epicor_comm_enabled_messages_msq_request_msq_after_stk").change(function () {
            msqoverwriteonupdate(overwriteOnUpdate, locationFields);
        })

        //if locations update changes to Y, display other location options, else if N hide them
        $("#epicor_comm_enabled_messages_msq_request_locations_update").change(function () {
            msqoverwriteonupdatelocations(locationFields);
        })
    })

    function msqoverwriteonupdate(overwriteOnUpdate, locationFields) {

        if ($('#epicor_comm_enabled_messages_msq_request_scheduledmsq').val() == 1
            || $('#epicor_comm_enabled_messages_msq_request_msq_after_stk').val() == 1
        ) {
            overwriteOnUpdate.forEach(function (element) {
                //if a location value but locations not turned on, don't display
                if (locationFields.includes(element) && $('#epicor_comm_enabled_messages_msq_request_locations_update').val() != 1) {
                    return;
                }
                $("#" + element).show();
            })

        } else {
            overwriteOnUpdate.forEach(function (element) {
                $("#" + element).hide();
            })
        }
    }

    function msqoverwriteonupdatelocations(locationFields) {

        if ($('#epicor_comm_enabled_messages_msq_request_locations_update').val() == 1) {

            //check if either schedulemsq or msq after stk enabled before enabling location options
            if ($('#epicor_comm_enabled_messages_msq_request_scheduledmsq').val() == 1
                || $('#epicor_comm_enabled_messages_msq_request_msq_after_stk').val() == 1
            ) {
                locationFields.forEach(function (element) {
                    $("#" + element).show();
                })
            }
        } else {
            locationFields.forEach(function (element) {
                $("#" + element).hide();
            })
        }

    }
})
