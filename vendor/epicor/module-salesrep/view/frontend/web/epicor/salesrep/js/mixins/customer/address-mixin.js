/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/**
 * mixin to close the Avaliable locations
 * pop up after product is added to cart.
 */
define([
    'jquery',
], function ($) {
    'use strict';
    return function (target) {
             /**
     * @param addressData
     * Returns new address object
     */
        return function (addressData) { //console.log(addressData);
            return {
                customerAddressId: addressData.id,
                email: addressData.email,
                countryId: addressData.country_id,
                regionId: addressData.region_id,
                regionCode: (addressData.region !== null) ? addressData.region.region_code : null,
                region: (addressData.region !== null) ? addressData.region.region : null,
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
                    if(this.customerAddressId ==null){ 
                            if(addressData.id!=null && addressData.id.indexOf("erpaddress_") != -1){
                                    return this.getType() + addressData.id;
                            }
                    } 
                    
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
                },
                getErpCustomerAddressId: function() {
                    return addressData.id;
                }
            }
        }
    };
});
