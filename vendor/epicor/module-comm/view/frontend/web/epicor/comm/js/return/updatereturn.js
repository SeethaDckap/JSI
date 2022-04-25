/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery',
    'Epicor_Comm/epicor/comm/js/return/tabmaster',
    'prototype'
], function (jQuery, TabMaster) {
    
var UpdateReturn = Class.create();
UpdateReturn.prototype = new TabMaster();
UpdateReturn.prototype.tab = 'return';
UpdateReturn.prototype.addBeforeSaveFunction('return_update', function () {
    if ($('update-submit')) {
        $('update-submit').hide();
    }
});

UpdateReturn.prototype.addBeforeNextStepFunction('return_update', function () {
    if ($('update-submit')) {
        $('update-submit').show();
    }
});

  return UpdateReturn;
  
});