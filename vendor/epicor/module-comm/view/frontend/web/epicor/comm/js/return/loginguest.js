/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery',
    'Epicor_Comm/epicor/comm/js/return/tabmaster'
], function (jQuery, TabMaster) {

    var LoginGuest = Class.create();
    LoginGuest.prototype = new TabMaster();
    LoginGuest.prototype.addBeforeSaveFunction('loginguest', function () {
        if ($('login-guest-submit')) {
            $('login-guest-submit').hide();
        }
        if ($('login-submit')) {
            $('login-submit').hide();
        }
    });
    LoginGuest.prototype.tab = '';

    LoginGuest.prototype.addBeforeNextStepFunction('loginguest', function () {
        if ($('login-guest-submit')) {
            $('login-guest-submit').show();
        }
        if ($('login-submit')) {
            $('login-submit').show();
        }
    });

    return LoginGuest;

});
