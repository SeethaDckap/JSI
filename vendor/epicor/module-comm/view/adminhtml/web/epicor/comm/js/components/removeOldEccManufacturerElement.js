define(
    [
        'underscore',
        // 'uiComponent',
        'Magento_Ui/js/dynamic-rows/dynamic-rows',
        'ko',
        'jquery',
        'mage/translate',
    ],
    function (_, Component, ko, $, $t) {
        'use strict';

        return Component.extend(
            {

                /**
                 * This is here to remove the previous ecc_manufacturers element from the product admin display
                 * it is no longer needed
                 */
                loadJsAfterKoRender: function () {
                    jQuery('[data-index *="ecc_manufacturers"]').first().hide();

                    return this;
                },

            }
        );
    }
);