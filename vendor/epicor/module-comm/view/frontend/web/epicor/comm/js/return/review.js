/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery',
    'Epicor_Comm/epicor/comm/js/return/tabmaster',
    'prototype'
], function (jQuery, TabMaster) {
    

var Review = Class.create();
Review.prototype = new TabMaster();
Review.prototype.tab = 'review';
Review.prototype.addBeforeSaveFunction('review', function () {
    if ($('review-submit')) {
        $('review-submit').hide();
    }
});

Review.prototype.addBeforeNextStepFunction('review', function () {
    if ($('review-submit')) {
        $('review-submit').show();
    }
});
  return Review;
  
});