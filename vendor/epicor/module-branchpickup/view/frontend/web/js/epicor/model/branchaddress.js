/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
/*jshint browser:true jquery:true*/
/*global alert*/
define([], function() {
    /**
     * @param addressData
     * Returns new address object
     */
    return function (addressData) {
        return {
            customerAddressId: addressData.locationid,
            locationid: addressData.locationid,
            locationname: addressData.locationname,
            locationcode: addressData.code,
            email: addressData.email,
            countryId: addressData.country_id,
            regionId: addressData.region_id,
            regionCode: addressData.region,
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
            error: addressData.error,
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
                return this.locationcode;
            },
            getLocationId: function() {
                return this.locationid;
            },  
            getCode: function() {
                return this.locationcode;
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
    }
});
