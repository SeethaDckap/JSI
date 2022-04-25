/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


if(typeof Epicor == 'undefined') {
    var Epicor = {};
}
require([
    'jquery',
    'prototype'
], function (jQuery) {
    Epicor.arrayTableHandler = Class.create();
    Epicor.arrayTableHandler.prototype = {
        template: null,
        table_id: null,
        initialize: function(table_id, template){
            this.table_id = table_id;
            this.template = template;
        },
        addRow: function () {
            var d = new Date();
            var row_id = '_' + d.getTime() + '_' + d.getMilliseconds();
            var row_template = this.template.split('#{id}').join(row_id);
            $(this.table_id).insert(row_template);
            $(this.table_id).select('tr').last().select('input[type=text]').first().focus();
        },
        removeRow: function(row_id) {
            if($(this.table_id+'_delete')) {
                var id = row_id.split(this.table_id+'_row_').join('');
                if($(this.table_id+'_delete').value != '')
                    $(this.table_id+'_delete').value += ',';
                $(this.table_id+'_delete').value += id;
            }
            $(row_id).remove();
        }
    };
});
