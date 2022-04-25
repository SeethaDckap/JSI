/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * @api
 */
define([
    'ko',
    'uiClass'
], function (ko, Class) {
    'use strict';

    return Class.extend({
        /** @inheritdoc */
        initialize: function () {
            this._super()
                .initObservable();

            return this;
        },

        /** @inheritdoc */
        initObservable: function () {
            this.noticeMessages = ko.observableArray([]);

            return this;
        },

        /**
         * Add  message to list.
         * @param {Object} messageObj
         * @param {Object} type
         * @returns {Boolean}
         */
        add: function (messageObj, type) {
            var expr = /([%])\w+/g,
                message;

            if (!messageObj.hasOwnProperty('parameters')) {
                this.clear();
                type.push(messageObj.message);

                return true;
            }
            message = messageObj.message.replace(expr, function (varName) {
                varName = varName.substr(1);

                if (messageObj.parameters.hasOwnProperty(varName)) {
                    return messageObj.parameters[varName];
                }

                return messageObj.parameters.shift();
            });
            this.clear();
            type.push(message);

            return true;
        },

        /**
         * Add notice message.
         *
         * @param {Object} message
         * @return {*|Boolean}
         */
        addNoticeMessage: function (message) {
            return this.add(message, this.noticeMessages);
        },

        /**
         * Get notice messages.
         *
         * @return {Array}
         */
        getNoticeMessages: function () {
            return this.noticeMessages;
        },

        /**
         * Checks if an instance has stored messages.
         *
         * @return {Boolean}
         */
        hasMessages: function () {
            return this.noticeMessages().length > 0;
        },

        /**
         * Removes stored messages.
         */
        clear: function () {
            this.noticeMessages.removeAll();
        }
    });
});
