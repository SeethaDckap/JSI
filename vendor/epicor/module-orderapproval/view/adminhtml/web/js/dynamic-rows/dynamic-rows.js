/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define(
    [
        'underscore',
        'Magento_Ui/js/dynamic-rows/dynamic-rows',
        'ko',
        'jquery',
        'mage/translate',
        'Magento_Ui/js/modal/confirm',
        'Magento_Ui/js/modal/alert'

    ],
    function (_, Component, ko, $, $t, confirmation, alert) {
        'use strict';

        return Component.extend(
            {
                /**
                 * Processing pages before addChild
                 *
                 * @param {Object} ctx - element context
                 * @param {Number|String} index - element index
                 * @param {Number|String} prop - additional property to element
                 */
                processingAddChild: function (ctx, index, prop) {
                    if (this.relatedData.length < 4) {
                        this._super(ctx, index, prop);
                    } else {
                        alert({
                            title: $.mage.__(''),
                            content: $.mage.__('A Maximum Of 4 Budget Type Can Be Added.'),
                            actions: {
                                always: function () {
                                }
                            }
                        });
                    }
                },

                /**
                 * Hide Pagination For Dynamic Rows.
                 *
                 * @returns {number}
                 */
                getRecordCount: function () {
                    return 0;
                },

                /**
                 * Processing pages before deleteRecord
                 *
                 * @param {Number|String} index - element index
                 * @param {Number|String} recordId
                 */
                processingDeleteRecord: function (index, recordId) {
                    var _this = this;
                    confirmation({
                        title: $.mage.__('Budget Delete'),
                        content: $.mage.__('Are you Sure You Want To Delete Budget?'),
                        actions: {
                            confirm: function () {
                                _this.deleteRecord(index, recordId);
                            },
                            cancel: function () {
                                // do something when the cancel button is clicked
                            },
                            always: function () {
                                // do something when the modal is closed
                            }
                        },
                        buttons: [{
                            text: $.mage.__('Cancel'),
                            class: 'action-secondary action-dismiss',
                            click: function (event) {
                                this.closeModal(event);
                            }
                        }, {
                            text: $.mage.__('OK'),
                            class: 'action-primary action-accept',
                            click: function (event) {
                                this.closeModal(event, true);
                            }
                        }]
                    });
                },
            }
        );
    }
);