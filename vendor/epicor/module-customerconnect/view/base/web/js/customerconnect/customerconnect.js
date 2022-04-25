/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

var overlayOpen = false;

require([
    'jquery',
    'prototype'
], function (jQuery) {
    document.observe('dom:loaded', function () {
        // display hand on hover
        if ($('customer_account_contacts_list')) {

            $('customer_account_contacts_list').select('.action-select').invoke('observe', 'change', function () {
                var action_value = this[this.selectedIndex].text;
                var trValue = this.up('tr');
                var details = trValue.select('input.details')[0].value;

                if (action_value == 'Delete') {
                    var url = '/customerconnect/account/deleteContact/';
                    hideOverlayForms()
                    if($('window-overlay')){
                        $('window-overlay').show();
                    }
                    $('loading-mask').show();
                    formSubmitAction(details, url);

                } else if (action_value == 'Sync Contact') {
                    detial_values = JSON.parse(details);
                    if (detial_values.source == 1) {
                        var url = '/customerconnect/account/syncContact/';
                        hideOverlayForms();
                        if($('window-overlay')){
                            $('window-overlay').show();
                        }
                        $('loading-mask').show();
                        formSubmitAction(details, url);
                    } else {
                        alert("Sync option is not available");
                    }
                } else if (action_value == 'Edit') {
                    editContactAction(trValue);
                }
                this.selectedIndex = false;
            });
        }
        
        if ($('customer_account_shippingaddress_list')) {
            $('customer_account_shippingaddress_list').select('.admin__control-select').invoke('observe', 'change', function () {
                var action_value = this[this.selectedIndex].text;
                var trValue = this.up('tr');
                var details = trValue.select('input.details')[0].value;

                if (action_value == 'Delete') {
                    var url = '/customerconnect/account/deleteShippingAddress/';
                    hideOverlayForms();
                    if($('window-overlay')){
                        $('window-overlay').show();
                    }
                    $('loading-mask').show();
                    formSubmitAction(details, url);
                } else if (action_value == 'Edit') {
                    editShippingAddressAction(trValue);
                }
                this.selectedIndex = false;

            });
        }

        if ($('contact_web_enabled')) {
            $('contact_web_enabled').observe('click', function () {
                var vForm = jQuery('#update-contact-form');
                clearFormError(vForm);

                if (this.checked) {
                    $('contact_email_address_label').addClassName('required');
                    $('contact_email_address_label').innerHTML = '* Email Address';
                    $('contact_email_address').addClassName('required-entry');
                    $('ecc_contact_hide_prices') != undefined ? $('ecc_contact_hide_prices').show() : "";
                    if (jQuery('#loggedin_customer_master_shopper').val() == 1) {
                        jQuery('#ecc_contact_access_roles') != undefined ? jQuery('#ecc_contact_access_roles').show() : "";
                        jQuery('#ecc_contact_access_roles_options') != undefined ? jQuery('#ecc_contact_access_roles_options').show() : "";
                    } else {
                        jQuery('#ecc_contact_access_roles') != undefined ? jQuery('#ecc_contact_access_roles').hide() : "";
                        jQuery('#ecc_contact_access_roles_options')!= undefined ? jQuery('#ecc_contact_access_roles_options').hide() : "";
                    }
                    if(jQuery('#contact_ecc_access_role').val() == 0){
                        jQuery('#ecc_contact_access_roles_disabled')!= undefined ? jQuery('#ecc_contact_access_roles_disabled').show() : "";
                        jQuery('#ecc_contact_access_roles')!= undefined ? jQuery('#ecc_contact_access_roles').hide() : "";
                        jQuery('#ecc_contact_access_roles_options')!= undefined ? jQuery('#ecc_contact_access_roles_options').hide() : "";
                    }
                } else {
                    $('contact_email_address_label').removeClassName('required');
                    $('contact_email_address_label').innerHTML = 'Email Address';
                    $('contact_email_address').removeClassName('required-entry');
                    $('ecc_contact_hide_prices')!= undefined ? $('ecc_contact_hide_prices').hide() : "";
                    jQuery('#ecc_contact_access_roles')!= undefined ? jQuery('#ecc_contact_access_roles').hide() : "";
                    jQuery('#ecc_contact_access_roles_options') != undefined ? jQuery('#ecc_contact_access_roles_options').hide() : "";jQuery('#ecc_contact_access_roles_disabled')!= undefined ? jQuery('#ecc_contact_access_roles_disabled').show() : "";
                    jQuery('#ecc_contact_access_roles_disabled')!= undefined ? jQuery('#ecc_contact_access_roles_disabled').hide() : "";
                }
            });
        }

        // below displays contract codes if
        $$('[id ^= "contract_code_heading_"]').each(function (ccshow) {

            $(ccshow).up('td').observe('mouseover', function (a) {

                var invoice = ccshow.id.split('heading_');

                $(ccshow).hide();
                $('contract_codes_' + invoice[1]).show();

            })
        })
        $$('[id ^= "contract_codes_"]').each(function (cchide) {

            $(cchide).up('td').observe('mouseout', function (a) {
                invoice = cchide.id.split('contract_codes_');
                cchide.hide();
                $('contract_code_heading_' + invoice[1]).show();
            })

        })
    });
});

require([
    'jquery',
    'mage/validation'
], function (jQuery) {

    window.controllerRedirect = function (url, ajax) {

        if (!ajax) {
            window.location.replace(url);

        } else {
            url = url + (url.match(new RegExp('\\?')) ? '&isAjax=true' : '?isAjax=true');

            jQuery.ajax({
                url: url,
                //      type: 'POST',
                async: true,
                dataType: "json",
                success: function (data) {
                    if (data.message) {
                        showMessage(data.message, data.type);
                    }
                }
            });
        }
    }

    window.formSubmit = function (form_data_id, url, serialized) {
        var editform = jQuery("#" + form_data_id);
        editform.validation();

        if (!editform.validation('isValid')) {
            return false;
            //$('messages').update('<ul class="messages"><li class="error-msg">Errors Found in Form</li></messages>');
        } else {
            $('loading-mask').show();
            url = $(form_data_id).readAttribute('action');
            url = url + (url.match(new RegExp('\\?')) ? '&isAjax=true' : '?isAjax=true');
            if (!serialized) {
                var form_data = Object.toJSON($(form_data_id).serialize(true));
            } else {
                var form_data = form_data_id;
            }
            formSubmitAction(form_data, url);
            return true;
        }
    }

    window.formSubmitAction = function (form_data, url) {
        this.ajaxRequest = new Ajax.Request(url, {
            method: 'post',
            parameters: {'json_form_data': form_data},
            onComplete: function (request) {
                this.ajaxRequest = false;
            }.bind(this),
            onSuccess: function (data) {

                var json = data.responseText.evalJSON();
                if (json.type == 'success') {
                    if (json.redirect) {
                        controllerRedirect(json.redirect);
                    }
                } else {
                    json = jQuery.parseJSON(data.responseJSON);
                    location.href = json.redirect;
                }
            }.bind(this),
            onFailure: function (request) {
                console.log(request.responseText);
                alert('Error occured in Ajax Call');
            }.bind(this),
            onException: function (request, e) {
                alert(e);
            }.bind(this)
        });
    }
    window.showMessage = function (txt, type, position) {
        var html = '<ul class="messages"><li class="' + type + '-msg"><ul><li>' + txt + '</li></ul></li></ul>';
        if (position == false) {
            jQuery('.page.messages').empty();
        }
        jQuery('.page.messages').append(html);


    }

    window.commonShippingAddressDetails = function (objectFromJson) {
        var arrayFromJson = objectFromJson.evalJSON();
        // populate popup fields
        var vForm = jQuery('#update-shipping-address-form');
        clearFormError(vForm);
        $('shipping_address_code').value = (arrayFromJson.address_code) ? arrayFromJson.address_code : '';
        $('shipping_address_code').disable();
        $('shipping_name').value = (arrayFromJson.name !== null) ? arrayFromJson.name : '';
        $('shipping_address1').value = (arrayFromJson.address1 !== null) ? arrayFromJson.address1 : '';
        if ($('shipping_address2') != null) {
            $('shipping_address2').value = (arrayFromJson.address2 !== null) ? arrayFromJson.address2 : '';
        }
        if ($('shipping_address3') != null) {
            $('shipping_address3').value = (arrayFromJson.address3 !== null) ? arrayFromJson.address3 : '';
        }
        if ($('shipping_address4') != null) {
            $('shipping_address4').value = (arrayFromJson.address4 !== null) ? arrayFromJson.address4 : '';
        }
        $('shipping_city').value = (arrayFromJson.city !== null) ? arrayFromJson.city : '';
        $('shipping_county_id').setAttribute('defaultValue', ((arrayFromJson.county_id !== null) ? arrayFromJson.county_id : ''));
        $('shipping_county_id').value = (arrayFromJson.county_id !== null) ? arrayFromJson.county_id : '';
        ;
        $('shipping_county').value = (arrayFromJson.county !== null) ? arrayFromJson.county : '';
        $('shipping_country').value = (arrayFromJson.country_code !== null) ? arrayFromJson.country_code : '';
        $('shipping_postcode').value = (arrayFromJson.postcode !== null) ? arrayFromJson.postcode : '';
        if ($('shipping_email') != null) {
            $('shipping_email').value = (arrayFromJson.email !== null) ? arrayFromJson.email : '';
        }
        if ($('shipping_telephone') != null) {
            $('shipping_telephone').value = (arrayFromJson.telephone !== null && arrayFromJson.telephone != 'undefined') ? arrayFromJson.telephone : '';
        }
        if ($('shipping_mobile_number')) {
            $('shipping_mobile_number').value = (arrayFromJson.mobile_number !== null && arrayFromJson.mobile_number != 'undefined') ? arrayFromJson.mobile_number : '';
        }
        if ($('shipping_fax') != null) {
            $('shipping_fax').value = (arrayFromJson.fax !== null) ? arrayFromJson.fax : '';
        }
        $('shipping_old_data').value = (objectFromJson !== null) ? objectFromJson : '';
        if (typeof (limitcheck) != 'undefined') {
            limitcheck.setData(limitcheck.name, limitcheck.address, limitcheck.telephone, limitcheck.instructions);
        }
//    $('update-shipping-address').show();
        if ("createEvent" in document) {
            var evt = document.createEvent("HTMLEvents");
            evt.initEvent("change", false, true);
            $('shipping_country').dispatchEvent(evt);
        } else {
            $('shipping_country').fireEvent("onchange");
        }

    }

    window.resetShippingAddressForm = function () {
        var vForm = jQuery('#update-shipping-address');
        clearFormError(vForm);

        $('shipping_address_code').value = '';
        $('shipping_address_code').enable();
        $('shipping_name').value = '';
        $('shipping_address1').value = '';
        if ($('shipping_address2') != null) {
            $('shipping_address2').value = '';
        }
        if ($('shipping_address3') != null) {
            $('shipping_address3').value = '';
        }
        if ($('shipping_address4') != null) {
            $('shipping_address4').value = '';
        }
//    if(typeof($('shipping_address2')) != null){}{
//        $('shipping_address2').value = '';
//    }
//    if(typeof($('shipping_address3')) != null){}{
//        $('shipping_address3').value = '';
//    }
//    if(typeof($('shipping_address4')) != 'undefined'){}{
//        $('shipping_address4').value = '';
//    }
        $('shipping_city').value = '';
        $('shipping_county_id').setAttribute('defaultValue', '');
        $('shipping_county').value = '';
        $('shipping_country').value = ($('shipping_default_country')) ? $('shipping_default_country').value : '';
        if ("createEvent" in document) {
            var evt = document.createEvent("HTMLEvents");
            evt.initEvent("change", false, true);
            $('shipping_country').dispatchEvent(evt);
        } else {
            $('shipping_country').fireEvent("onchange");
        }
        $('shipping_postcode').value = '';
        if ($('shipping_email') != null) {
            $('shipping_email').value = '';
        }
        if ($('shipping_telephone') != null) {
            $('shipping_telephone').value = '';
        }
        if ($('shipping_mobile_number') != null) {
            $('shipping_mobile_number').value = '';
        }
        if ($('shipping_fax') != null) {
            $('shipping_fax').value = '';
        }
        $('shipping_old_data').value = '';
    }


    window.commonContactDetails = function (objectFromJson) {
        var arrayFromJson = objectFromJson.evalJSON();

        var vForm = jQuery('#update-contact-form');
        clearFormError(vForm);

        $('contact_firstname').value = (arrayFromJson.firstname !== null) ? arrayFromJson.firstname : '';
        $('contact_lastname').value = (arrayFromJson.lastname !== null) ? arrayFromJson.lastname : '';
        $('contact_function').value = (arrayFromJson.function !== null) ? arrayFromJson.function : '';
        $('contact_telephone_number').value = (arrayFromJson.telephone_number !== null) ? arrayFromJson.telephone_number : '';
        $('contact_fax_number').value = (arrayFromJson.fax_number !== null) ? arrayFromJson.fax_number : '';
        $('contact_email_address').value = (arrayFromJson.email_address !== null) ? arrayFromJson.email_address : '';
        $('contact_login_id').value = (arrayFromJson.login_id !== null) ? arrayFromJson.login_id : '';
        $('contact_ecc_master_shopper').value = (arrayFromJson.master_shopper !== null) ? arrayFromJson.master_shopper : '';
        
        var accessRoles = arrayFromJson.ecc_access_roles;
        var select = "#update-contact-form select#customer_access_roles";
        jQuery(select).empty().append('<option value="0"> Account Default </option>');
           jQuery.each(accessRoles, function (index, value) {
            if (value.autoAssign != 1 && value.by_erp_account != 1) {
                var selecthtml = '';
                if (value.by_customer == 1) {
                    selecthtml = 'selected="selected"';
                }
                jQuery(select).append('<option value="' + value.role_id + '" '+selecthtml+'>' + value.label + '</option>');
            }
        });
              
        
        /* Delaer change  start*/
              
        if($('login_mode_dealer')!= undefined) {
            if(arrayFromJson.login_mode_type=='dealer'){ 
                 $('login_mode_dealer').checked = true;
            }else  if(arrayFromJson.login_mode_type=='shopper'){ 
                $('login_mode_shopper').checked = true;
            }
            if(arrayFromJson.is_toggle_allowed == 1){
                $('is_toggle_yes').checked = true;
            }else if(arrayFromJson.is_toggle_allowed == 0){
                $('is_toggle_no').checked = true;
            }
        }
       
        /* Dealer change end */ 
        if (arrayFromJson.is_ecc_customer == 0) {
            $('master_shopper_li').hide();
        }
        if (arrayFromJson.is_ecc_customer == 1) {
            $('master_shopper_li').show();
        }

        if (arrayFromJson.login_id) {
            $('contact_web_enabled').checked = true;
            $('ecc_contact_hide_prices')!= undefined ? $('ecc_contact_hide_prices').show() : "";
        } else {
            $('contact_web_enabled').checked = false;
            $('ecc_contact_hide_prices')!= undefined ? $('ecc_contact_hide_prices').hide() : "";
        }
               
        if (arrayFromJson.login_id) {
            jQuery('#ecc_contact_access_roles') != undefined ? jQuery('#ecc_contact_access_roles').show() : "";
            jQuery('#ecc_contact_access_roles_options') != undefined ? jQuery('#ecc_contact_access_roles_options').show() : "";
                if ($('ecc_contact_access_roles') != undefined) {
                    if (arrayFromJson.ecc_access_rights == "1" || arrayFromJson.ecc_access_rights == "2") {
                        jQuery('#ecc_contact_access_roles_options') != undefined ? jQuery('#ecc_contact_access_roles_options').show() : "";
                        if(arrayFromJson.ecc_access_rights == "2") {
                            jQuery('#customer_access_roles').val(0);
                        }
                    }else{
                        jQuery('#ecc_contact_access_roles')!= undefined ? jQuery('#ecc_contact_access_roles').hide() : "";
                        jQuery('#ecc_contact_access_roles_options')!= undefined ? jQuery('#ecc_contact_access_roles_options').hide() : "";

                    }
                }

        }else{
            jQuery('#customer_access_roles').val(0);
            jQuery('#ecc_contact_access_roles')!= undefined ? jQuery('#ecc_contact_access_roles').hide() : "";
            jQuery('#ecc_contact_access_roles_options')!= undefined ? jQuery('#ecc_contact_access_roles_options').hide() : "";
            jQuery('#ecc_contact_access_roles_disabled')!= undefined ? jQuery('#ecc_contact_access_roles_disabled').hide() : "";
        }
                
        if(arrayFromJson.ecc_web_enabled == 1 && arrayFromJson.login_id == null && arrayFromJson.contact_code == null){
            $('contact_web_enabled').checked = true;
            $('contact_web_enabled').disabled = true;
            jQuery('#ecc_contact_access_roles') != undefined ? jQuery('#ecc_contact_access_roles').show() : "";
            jQuery('#ecc_contact_access_roles_options') != undefined ? jQuery('#ecc_contact_access_roles_options').show() : "";
                if ($('ecc_contact_access_roles') != undefined) {
                    if (arrayFromJson.ecc_access_rights == "1" || arrayFromJson.ecc_access_rights == "2") {
                       jQuery('#ecc_contact_access_roles_options') != undefined ? jQuery('#ecc_contact_access_roles_options').show() : "";
                        if(arrayFromJson.ecc_access_rights == "2") {
                            jQuery('#customer_access_roles').val(0);
                        }
                    }else{
                        jQuery('#ecc_contact_access_roles')!= undefined ? jQuery('#ecc_contact_access_roles').hide() : "";
                        jQuery('#ecc_contact_access_roles_options')!= undefined ? jQuery('#ecc_contact_access_roles_options').hide() : "";
                    }
                    if (arrayFromJson.ecc_access_rights == "0"){
                        jQuery('#ecc_contact_access_roles_disabled')!= undefined ? jQuery('#ecc_contact_access_roles_disabled').show() : "";
                    }
                }

        }

        if (arrayFromJson.master_shopper == 'y') {
            $('contact_master_shopper').checked = true;
            if ($('ecc_master_shopper_no') != undefined && $('ecc_master_shopper_yes') != undefined) {
                $('ecc_master_shopper_no').hide();
                $('ecc_master_shopper_yes').show();
            }
        } else {
            $('contact_master_shopper').checked = false;
            if ($('ecc_master_shopper_no') != undefined && $('ecc_master_shopper_yes') != undefined) {
                $('ecc_master_shopper_yes').hide();
                $('ecc_master_shopper_no').show();
            }
        }
        if ($('ecc_contact_hide_prices') != undefined) {
            if (arrayFromJson.ecc_hide_prices == "0") {
                $('contact_hide_prices_no').checked = true;
            } else if (arrayFromJson.ecc_hide_prices == "1") {
                $('contact_hide_prices_yes').checked = true;
            } else if (arrayFromJson.ecc_hide_prices == "2") {
                $('contact_hide_prices_show_default').checked = true;
            } else if (arrayFromJson.ecc_hide_prices == "3") {
                $('contact_hide_prices_yes_checkout').checked = true;
            }  
        }

        if ($('loggedin_customer_master_shopper').value == '1') {
            if ($('contact_master_shopper').hasAttribute('disabled')) {
                $('contact_master_shopper').removeAttribute('disabled');
            }
            $('contact_master_shopper').enabled = true;
            
        } else {
            if ($('contact_master_shopper').hasAttribute('enabled')) {
                $('contact_master_shopper').removeAttribute('enabled');
            }
            $('contact_master_shopper').disabled = true;
        }
        if ($('contact_email_address').value == $('loggedin_customer_email').value) {
            if ($('contact_master_shopper').hasAttribute('enabled')) {
                $('contact_master_shopper').removeAttribute('enabled');
            }
            $('contact_master_shopper').disabled = true;
        }

        $('contact_old_data').value = (objectFromJson !== null) ? objectFromJson : '';
    }

    window.update_value = function () {
        if ($('contact_master_shopper').checked) {
            $('contact_ecc_master_shopper').value = 'y';
        } else {
            $('contact_ecc_master_shopper').value = 0;
        }
    }

    window.resetContactForm = function () {
        var vForm = jQuery('#update-contact-form');
        clearFormError(vForm);
        $('contact_firstname').value = '';
        $('contact_lastname').value = '';
        $('contact_function').value = '';
        $('contact_telephone_number').value = '';
        $('contact_fax_number').value = '';
        $('contact_email_address').value = '';
        $('contact_login_id').value = '';
        $('contact_web_enabled').checked = false;
        if($('ecc_contact_hide_prices')!= undefined) {
            $('contact_hide_prices_no').checked = false;
            $('contact_hide_prices_yes').checked = false;
            $('contact_hide_prices_show_default').checked = false;
            $('contact_hide_prices_yes_checkout').checked = false;
        }
        if($('ecc_contact_access_roles')!= undefined) {
            var select = "#update-contact-form select#customer_access_roles";
            jQuery(select).empty().append('<option value="0"> Account Default </option>');
        }
        $('contact_old_data').value = '';
        if($('login_mode_dealer')!= undefined) {
            $('login_mode_shopper').checked = false;
            $('login_mode_dealer').checked =false;
            $('is_toggle_yes').checked = false;
            $('is_toggle_no').checked = false;
        }
    }

    window.clearFormError = function(formElement) {
        formElement.validation();
        formElement.validation('clearError');
        formElement.find('.required-entry').removeClass('mage-error');
    }

});

require([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'domReady!'
], function ($, modal) {

    window.openCustomModal = function (contentId, title, buttonLabel, clickFunction) {
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: title,
            buttons: [{
                    text: buttonLabel,
                    class: '',
                    click: clickFunction
                    },                    
            ]
        };
        var popup = modal(options, $(contentId));
        $(contentId).modal("openModal");
    }
    
    $('#billing_address_update').click(function (event) {

        openCustomModal(
            '#update-billing-address',
            $.mage.__('Update Billing Address'),
            $.mage.__('Save Address'),
            function () {
                /* some stuff */
                if (formSubmit('update-billing-address-form')) {
                    this.closeModal();
                }
            }
        );
        let scope = $(document.body);
        scope.find('input[name=address_code]').attr("disabled", true);
    });
    
    
    window.displayShippingForm = function(title, objectFromJson) {
        objectFromJson = (objectFromJson === undefined) ? false : objectFromJson;
        resetShippingAddressForm();
        
        if (objectFromJson) {
            commonShippingAddressDetails(objectFromJson);
        }
        
        openCustomModal(
            '#update-shipping-address',
            title,
            $.mage.__('Save Address'),
            function () {
                /* some stuff */
                if (formSubmit('update-shipping-address-form')) {
                    this.closeModal();
                }
            }
        );
    }
    
    $('#add-shipping-address').click(function (event) {
        displayShippingForm($.mage.__('Add Shipping Address'));
    });
    
    window.editShippingAddress = function (row, event) {
        if (event.element().tagName !== 'SELECT' && (event.element().tagName !== 'OPTION')) {
            var trElement = event.findElement('tr');
            var x = trElement.select('.action-select');
            var objectFromJson = trElement.select('input[name=details]')[0].value;
            displayShippingForm($.mage.__('Update Shipping Address'), objectFromJson);
        }
        return false;
    }

    window.editShippingAddressAction = function (e) {
        // this runs when the edit action button is clicked
        var objectFromJson = e.select('input[name=details]')[0].value;
        displayShippingForm($.mage.__('Update Shipping Address'), objectFromJson);
    }
    
    window.displayContactForm = function(title, objectFromJson) {
        objectFromJson = (objectFromJson === undefined) ? false : objectFromJson;
        resetContactForm();
        
        if (objectFromJson) {
            commonContactDetails(objectFromJson);
        }

        openCustomModal(
            '#update-contact',
            title,
            $.mage.__('Save Contact'),
            function () {
                /* some stuff */
                if (formSubmit('update-contact-form')) {
                    this.closeModal();
                }
            }
        );
        jQuery('#ecc_contact_hide_prices')!= undefined ? jQuery('#ecc_contact_hide_prices').hide() : "";
        jQuery('#ecc_contact_access_roles')!= undefined ? jQuery('#ecc_contact_access_roles').hide() : "";
        jQuery('#ecc_contact_access_roles_options')!= undefined ? jQuery('#ecc_contact_access_roles_options').hide() : "";
        jQuery('#ecc_contact_access_roles_disabled')!= undefined ? jQuery('#ecc_contact_access_roles_disabled').hide() : "";
        if (jQuery('#contact_web_enabled').is(':checked')) {
            jQuery('#ecc_contact_hide_prices')!= undefined ? jQuery('#ecc_contact_hide_prices').show() : "";
            if (jQuery('#loggedin_customer_master_shopper').val() == 1) {
                jQuery('#ecc_contact_access_roles') != undefined ? jQuery('#ecc_contact_access_roles').show() : "";
                jQuery('#ecc_contact_access_roles_options') != undefined ? jQuery('#ecc_contact_access_roles_options').show() : "";
            }

        }
        if (objectFromJson) {
            var arrayFromJson = objectFromJson.evalJSON();
            jQuery('#contact_ecc_access_role').val(arrayFromJson.ecc_access_rights);
            if(!arrayFromJson.ecc_web_enabled){
                jQuery('#contact_ecc_access_role').val(1);
            }
            if (arrayFromJson.ecc_access_rights == "0") {
                jQuery('#ecc_contact_access_roles_disabled') != undefined ? jQuery('#ecc_contact_access_roles_disabled').show() : "";
                jQuery('#ecc_contact_access_roles') != undefined ? jQuery('#ecc_contact_access_roles').hide() : "";
                jQuery('#ecc_contact_access_roles_options') != undefined ? jQuery('#ecc_contact_access_roles_options').hide() : "";
            }
        }
    }
    
    $('#add-contact').click(function (event) {
        displayContactForm($.mage.__('Add Contact'));

        var objectFromJson = jQuery('#ecc_newcontact_info').val();
        var arrayFromJson = objectFromJson.evalJSON();
        var accessRoles = arrayFromJson.ecc_access_roles;
        var select = "#update-contact-form select#customer_access_roles";
        jQuery(select).empty().append('<option value="0" selected="selected" > Account Default </option>');
        jQuery.each(accessRoles, function (index, value) {
            if (value.autoAssign != 1 && value.by_erp_account != 1) {
                var selecthtml = '';
                jQuery(select).append('<option value="' + value.role_id + '" '+selecthtml+'>' + value.label + '</option>');
            }
        });
        jQuery('#contact_ecc_access_role').val(arrayFromJson.ecc_access_rights);
    });
    
    window.editContact = function (row, event) {
        if (event.element().tagName !== 'SELECT' && event.element().tagName !== 'OPTION') {
            var trElement = Event.findElement(event, 'tr');
            var objectFromJson = $(trElement).find('input[name=details]')[0].value;
            displayContactForm($.mage.__('Update Contact'), objectFromJson);
        }
    }

    window.editContactAction = function (e) {
        // this runs when the edit action button is clicked
        var objectFromJson = $(e).find('input[name=details]')[0].value;
        displayContactForm($.mage.__('Update Contact'), objectFromJson);
    }
    
    window.hideOverlayForms = function () {
        $('window-overlay').select('.box-account').each(function (e) {
            e.hide()
        });
    }      
});