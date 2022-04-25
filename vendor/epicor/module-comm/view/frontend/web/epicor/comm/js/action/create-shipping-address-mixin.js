/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*global define*/
define(
        [
            'Magento_Customer/js/model/address-list',
            'Magento_Checkout/js/model/address-converter'
        ],
        function (addressList, addressConverter) {
            "use strict";
            return function (target) {
                return function (addressData) {
                    var address = addressConverter.formAddressDataToQuoteAddress(addressData);
                    if (!addressData.locationcode) {
                        var isAddressUpdated = addressList().some(function (currentAddress, index, addresses) {
                            if (currentAddress.getKey() == address.getKey()) {
                                addresses[index] = address;
                                return true;
                            }
                            return false;
                        });
                        if (!isAddressUpdated) {
                            addressList.push(address);
                        } else {
                            addressList.valueHasMutated();
                        }
                    }

                    return address;
                };
            };
        }
);
