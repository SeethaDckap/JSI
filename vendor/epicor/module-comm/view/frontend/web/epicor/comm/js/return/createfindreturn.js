/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery',
    'Epicor_Comm/epicor/comm/js/return/tabmaster',
    'prototype'
], function (jQuery, TabMaster) {
    
var CreateFindReturn = Class.create();
CreateFindReturn.prototype = new TabMaster();
CreateFindReturn.prototype.tab = 'return';
CreateFindReturn.prototype.addBeforeSaveFunction('createfind', function () {
    if ($('create-submit')) {
        $('create-submit').hide();
    }
    if ($('find-submit')) {
        $('find-submit').hide();
    }
});

CreateFindReturn.prototype.addBeforeNextStepFunction('createfind', function () {
    if ($('create-submit')) {
        $('create-submit').show();
    }
    if ($('find-submit')) {
        $('find-submit').show();
    }
});

  return CreateFindReturn;
  
});