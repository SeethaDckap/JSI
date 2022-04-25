/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery'
], function ($) {
        'use strict';
        return {
            formatAddressBranch: function(jsonData) {
                var addressDatas = jsonData;
                var addressData = $.parseJSON(addressDatas);
                var returnObjs = {
                    customerAddressId: addressData.entity_id,
                    email: addressData.email,
                    countryId: addressData.country_id,
                    regionId: addressData.region_id,
                    regionCode: addressData.region_id,
                    region: addressData.region,
                    customerId: addressData.customer_id,
                    street: addressData.street,
                    company: addressData.company,
                    telephone: addressData.telephone,
                    fax: addressData.fax,
                    postcode: addressData.postcode,
                    city: addressData.city,
                    firstname: addressData.firstname,
                    lastname: addressData.lastname,
                    middlename: addressData.middlename,
                    prefix: addressData.prefix,
                    suffix: addressData.suffix,
                    vatId: addressData.vat_id,
                    sameAsBilling: addressData.same_as_billing,
                    saveInAddressBook: addressData.save_in_address_book,
                    customAttributes: addressData.custom_attributes,
                    isDefaultShipping: function() {
                        return addressData.default_shipping;
                    },
                    isDefaultBilling: function() {
                        return addressData.default_billing;
                    },
                    getAddressInline: function() {
                        return addressData.inline;
                    },
                    getType: function() {
                        return 'customer-address'
                    },
                    getKey: function() {
                        return this.getType() + this.customerAddressId;
                    },
                    getCacheKey: function() {
                        return this.getKey();
                    },
                    isEditable: function() {
                        return false;
                    },
                    canUseForBilling: function() {
                        return true;
                    }
                }
                return returnObjs;
            }          
        };
    }
);