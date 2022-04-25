/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery',
    'Magento_Ui/js/form/form',
    'Magento_Ui/js/modal/prompt',
    'Magento_Ui/js/modal/alert'
], function ($, Form, prompt, alert) {
    'use strict';

    return Form.extend({
        setAdditionalData: function (data) {
            _.each(data, function (value, name) {
                this.source.set('data.' + name, value);
            }, this);

            // Block data post.
            var erpAccount = $("input[name='selected_identity']").val();
            var shopper    = $("input[name='selected_shopper']").val();
            var checkbox   = $('#key_regenerate');
            var checked    = checkbox.is(':checked') ? 1 : 0;
            this.source.set('data.' + 'identity', erpAccount);
            this.source.set('data.' + 'default_shopper', shopper);
            this.source.set('data.' + 'key_regenerate', checked);

            return this;
        },
    });
});
