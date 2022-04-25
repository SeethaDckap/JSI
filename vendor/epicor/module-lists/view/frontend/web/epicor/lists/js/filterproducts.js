require(
        [
            'jquery',
            'mage/storage',
            'mage/url',
            'Magento_Customer/js/customer-data'
        ],
        function (
                $,
                storage,
                urlBuilder,
                customerData
                ) {
            'use strict';

            $(document).ready(function () {
                if ($.cookie('isListFilterReq') != window.checkout.isListsEnabled && $.cookie('isListFilterReq') != null) {
                    if (window.checkout.isListsEnabled == 1) {
                        storage.post(
                                urlBuilder.build('/lists/lists/Cartcheck'),
                                JSON.stringify({
                                    check: true
                                }),
                                true
                        ).done(
                                function (response) {
                                    var sections = ['cart'];
                                    customerData.invalidate(sections);
                                    customerData.reload(sections, true);
                                }
                        ).fail(
                                function (response) {
                                    console.log(response);
                                }
                        );
                    }
                }
            });
        });

