require([
    "prototype",
    "jquery"
], function () {
    var start_time = $("start_time").up("div").select("select");
    var end_time = $("end_time").up("div").select("select");

    //set default end time to 23:59:59, if required
    if (end_time[0].value == "00" && end_time[1].value == "00" && end_time[2].value == "00") {
        end_time[0].value = "23";
        end_time[1].value = "59";
        end_time[2].value = "59";
    }
    if (start_time[0].value > "00" || start_time[1].value > "00" || start_time[2].value > "00") {
        $("select_start_time").checked = true;
    }
    if (end_time[0].value != "23" || end_time[1].value != "59" || end_time[2].value != "59") {
        $("select_end_time").checked = true;
    }

    // the map functions loops through the array to avoid the need to repeat the code
    ["start", "end"].map(function (item) {
        if ($("select_" + item + "_time").checked) {
            $("select_" + item + "_time").value = 1;
            $$("label[for ^='" + item + "_time']").each(function (a) {
                a.up("div").select("div").each(function (b) {
                    b.show();
                    jQuery("label[for ^='" + item + "_time']").show();
                })
            })
        } else {
            $("select_" + item + "_time").value = 0;
            $$("label[for ^='" + item + "_time']").each(function (a) {
                a.up("div").select("div").each(function (b) {
                    b.hide();
                    jQuery("label[for ^='" + item + "_time']").hide();
                })
            })
        }
        $("select_" + item + "_time").observe("change", function () {
            if (this.checked) {
                $("select_" + item + "_time").value = 1;
                $$("label[for ^='" + item + "_time']").each(function (a) {
                    a.up("div").select("div").each(function (b) {
                        b.show();
                        jQuery("label[for ^='" + item + "_time']").show()
                    })
                    // if start ticked show end also
                    if (item == "start") {
                        $("select_end_time").value = 1;
                        $("select_end_time").checked = 1;
                        $$("label[for ^='end_time']").each(function (a) {
                            a.up("div").select("div").each(function (b) {
                                b.show();
                                jQuery("label[for ^='end_time']").show()
                            })
                        })
                    }
                })
            } else {
                $("select_" + item + "_time").value = 0;
                $$("label[for ^='" + item + "_time']").each(function (a) {
                    a.up("div").select("div").each(function (b) {
                        b.hide();
                        jQuery("label[for ^='" + item + "_time']").hide()
                    })
                    // if start unticked hide end also
                    if (item == "start") {
                        $("select_end_time").value = 0;
                        $("select_end_time").checked = 0;
                        $$("label[for ^='end_time']").each(function (a) {
                            a.up("div").select("div").each(function (b) {
                                b.hide();
                                jQuery("label[for ^='end_time']").hide();
                            })
                        })
                    }
                })
            }
        });
    })
});