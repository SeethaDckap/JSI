/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

// phrases grid update code 
/*if (typeof Epicor_SalesRep_Pricing == 'undefined') {
var Epicor_SalesRep_Pricing = {};
}*/
define([
    'jquery',
    'prototype'
], function (jQuery) {
var checkLengthLimits = Class.create();
    checkLengthLimits.prototype = {
        initialize : function(name,address, telephone, instructions, postcode, lastname = name, company = name, email = '')
        {
            this.setData(name, address, telephone, instructions, postcode, lastname, company, email);
            this.name = name;
            this.lastname = lastname;
            this.email = email;
            this.company = company;
            this.address = address;
            this.telephone = telephone;
            this.instructions = instructions;
            this.postcode = postcode;
        },
        setData: function(name,address, telephone, instructions, postcode, lastname, company, email) {

            var limitarray = {
                '_name'             : 'company',                   // key contained within id of input field : store config value to be applied
                'firstname'         : 'name',
                'lastname'          : 'lastname',
                'email'             : 'email',
                'company'           : 'company',
                '_address'          : 'address',
                'street'            : 'address',
                'telephone'         : 'telephone',
                '_phone'            : 'telephone',
                'mobile'            : 'telephone',
                'fax'               : 'telephone',
                'instructions'      : 'instructions',
                'zip'               : 'postcode',
                'postcode'          : 'postcode'
            };
            var limitValues = {
                'name'          :   name,
                'lastname'      :   lastname,
                'email'         :   email,
                'company'       :   company,
                'address'       :   address,
                'telephone'     :   telephone,
                'instructions'  :   instructions,
                'postcode'      :   postcode
            }
            var excludeValues = [
                'rfq_address_details'
                ,'delivery_address_code'
                ,'billing_address_code'
                ,'shipping_address_code'
                ,'b2b_companyreg'
            ]
            Object.keys(limitarray).forEach(function (key) {
                $$('form input[id *="'+ key +'"]', 'form textarea[id *="'+ key +'"]','div input[id *="'+ key +'"]', 'div textarea[id *="'+ key +'"]').each(function(o){

                    if(excludeValues.indexOf(o.id) == -1){            // don't process if field is in the excludeValues array
                        o.maxLength = limitValues[ limitarray[key] ];
                        o.addClassName('maximum-length-' + limitValues[ limitarray[key] ]);
                        if(o.value.length > limitValues[ limitarray[key] ] && limitValues[limitarray[key]] != 10234){                      // this bit limits existing fields to the config length if not unlimited(10234)
                            o.value = o.value.substring(0, limitValues[ limitarray[key] ]);
                        }
                        if(limitValues[limitarray[key]] != 10234){
                            if(!$('truncated_message_'+o.id)){
                                if(o.type !='hidden' &&  o.type !='checkbox'){                   // don't apply if input field not displayed
                                    var message = 'max '+limitValues[limitarray[key]]+' chars';
                                    o.insert({after:'<div id="truncated_message_'+o.id+'">' + message + '</div>'});
                                }
                            }
                        }
                    }
                })
            });

        }
    };

  return checkLengthLimits;
  
});
