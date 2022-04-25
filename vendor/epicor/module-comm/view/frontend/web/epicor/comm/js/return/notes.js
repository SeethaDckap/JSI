/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery',
    'Epicor_Comm/epicor/comm/js/return/tabmaster',
    'prototype'
], function (jQuery, TabMaster) {
    
var Notes = Class.create();
Notes.prototype = new TabMaster();
Notes.prototype.tab = 'notes';
Notes.prototype.addBeforeSaveFunction('notes', function () {
    if ($('notes-submit')) {
        $('notes-submit').hide();
    }
});

Notes.prototype.addBeforeNextStepFunction('notes', function () {
    if ($('notes-submit')) {
        $('notes-submit').show();
    }
});
  return Notes;
  
});