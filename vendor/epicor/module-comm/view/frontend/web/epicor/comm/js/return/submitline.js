/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery',
    'Epicor_Comm/epicor/comm/js/return/tabmaster',
    'prototype'
], function (jQuery, TabMaster) {
    

var SubmitLines = Class.create();
SubmitLines.prototype = new TabMaster();
SubmitLines.prototype.tab = 'lines';

SubmitLines.prototype.addBeforeSaveFunction('lines', function () {
    if ($('lines-submit')) {
        $('lines-submit').hide();
    }
});

SubmitLines.prototype.addBeforeNextStepFunction('lines', function () {
    if ($('lines-submit')) {
        $('lines-submit').show();
    }
});

SubmitLines.prototype.colorRows = function(table_id, table_extra) {
    var cssClass = 'even';
    $$('#' + table_id + ' tbody tr' + table_extra).findAll(function (el) {
        return el.visible();
    }).each(function (e) {
        if (e.visible()) {
            e.removeClassName('even');
            e.removeClassName('odd');
            e.addClassName(cssClass);

            if (cssClass == 'even') {
                cssClass = 'odd';
            } else {
                cssClass = 'even';
            }
        }
    });
}
  return SubmitLines;
  
});