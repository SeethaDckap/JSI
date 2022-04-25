/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * Created by lguerra on 10/10/14.
 */
function changeChartType(){
    var $select_message_type = jQuery("#messaging_report_message_type");
    if(jQuery('#messaging_report_chart_type').val() == 'minmaxavg'){
        $select_message_type.removeAttr('multiple');
    }
    else{
        $select_message_type.attr('multiple', 'multiple');
    }
}
function loadData(chart_id){
    var labels_index = [],
        labels_index_matrix = [],
        labels_values = [],
        data_labels = {},
        data = [],
        colors = [],
        legends = [];
    jQuery("#" + chart_id + " tfoot th").each(function (i) {
        var index = jQuery(this).index();
        labels_index.push(index);
        labels_values[index] = jQuery(this).html();
        data_labels[index] = [];
    });
    jQuery("#" + chart_id + " thead th").each(function (i) {
        var index = jQuery(this).index();
        legends[index] = jQuery(this).html();
    });
    jQuery("#" + chart_id + " tbody tr").each(function (i) {
        var index = jQuery(this).index();
        labels_index_matrix[index] = labels_index;
        data[index] = [];
        colors.push("hsl(" + [Math.random(), .5, .5] + ")");
        jQuery(this).find('td').each(function(){
            var sub_index = jQuery(this).index();
            var data_value = parseInt(jQuery(this).find('.chart_data').html());
            data_labels[sub_index][data_value] = jQuery(this).find('.chart_label').html();
            data[index][sub_index] = data_value;
        });
    });
    jQuery("#" + chart_id).hide();

    return {
        labels_index: labels_index,
        labels_index_matrix: labels_index_matrix,
        labels_values: labels_values,
        data_labels : data_labels,
        data : data,
        colors: colors,
        legends: legends,
        switched: jQuery("#" + chart_id).hasClass('switched')
    };
}