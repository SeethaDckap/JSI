/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
  'underscore',
  'uiRegistry',
  'Magento_Ui/js/form/element/select',
  'Magento_Ui/js/modal/modal'
], function (_, uiRegistry, select, modal) {
  'use strict'
  return select.extend({

    /**
     * On value change handler.
     *
     * @param {String} value
     */
    onUpdate: function (value) {

      accountSelector.resetCustomer(this);
      return this._super();
    },
  })
})
