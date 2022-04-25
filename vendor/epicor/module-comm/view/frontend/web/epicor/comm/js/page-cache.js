/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * mixin to close the Avaliable locations
 * pop up after product is added to cart.
 */
define([
    'jquery',
    'domReady',
    'jquery/ui',
    'mage/cookies'
], function ($, domReady) {
    'use strict';
    return function (target) {
        $.fn.comments = function () {
            var elements = [];

            /**
             * @param {jQuery} element - Comment holder
             */
            (function lookup(element) {
                $(element).contents().each(function (index, el) {
                    switch (el.nodeType) {
                        case 1: // ELEMENT_NODE
                            lookup(el);
                            break;

                        case 8: // COMMENT_NODE
                            elements.push(el);
                            break;

                        case 9: // DOCUMENT_NODE
                            var hostName = window.location.hostname,
                                    iFrameHostName = $('<a>')
                                    .prop('href', $(element).prop('src'))
                                    .prop('hostname');

                            if (hostName === iFrameHostName) {
                                lookup($(el).find('body'));
                            }
                            break;
                    }
                });
            })(this);

            return elements;
        };
    };
});
