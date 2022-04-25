/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    "jquery",
    'jquery/ui',
], function ($) {
    return {
        isEmpty: function(value){
            return value === '' || value == null || value.length === 0 || /^\s+$/.test(value)
        }
    }
});