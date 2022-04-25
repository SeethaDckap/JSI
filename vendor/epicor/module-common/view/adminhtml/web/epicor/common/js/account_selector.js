/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

if (typeof Solarsoft == 'undefined') {
    var Solarsoft = {};
}
var accountTypesHhtml;
require([
    'jquery',
    'prototype'
], function (jQuery) {

    Solarsoft.accountSelector = Class.create();
    Solarsoft.accountSelector.prototype = {
        type: null,
        target: null,
        wrapperId: 'selectErpAccountWrapperWindow',
        initialize: function () {
            if (!$('window-overlay')) {
                $(document.body).insert('<div id="window-overlay" class="window-overlay" style="display:none;"></div>');
            }

        },
        openpopup: function (target) {

            this.target = target;
            if ($(this.wrapperId)) {
                $(this.wrapperId).remove();
            }

            // create Popup Wrapper
            var wrappingDiv = new Element('div');
            wrappingDiv.id = this.wrapperId;
            $(document.body).insert(wrappingDiv);
            $(this.wrapperId).hide();

            var website = 0;
            $$('select#_accountwebsite_id option').each(function (o) {				// id = messages1
                if (o.selected == true) {
                    website = o.value;
                }
            })

            this.type = $(target).value;

            var gridUrl = $(target + '_' + this.type + '_url').value;

            //Added for customer grid only
            var scope_id            = jQuery('select[name="scope_id"]').val();
            var selected_erpaccount = '';
            if ($('selected_identity')) {
                selected_erpaccount = $('selected_identity').value;
            }


            this.ajaxRequest = new Ajax.Request(gridUrl, {
                method: 'post',
                parameters: {field_id: target, website: website, type: this.type, scope_id: scope_id, selected_erpaccount: selected_erpaccount},
                onComplete: function (request) {
                    this.ajaxRequest = false;
                }.bind(this),
                onSuccess: function (request) {
                    //$('loading-mask').hide();
                    $(this.wrapperId).insert(request.responseText);
                    $(this.wrapperId).show();
                    $('window-overlay').show();
                    this.updateWrapper();
                }.bind(this),
                onFailure: function (request) {
                    alert('Error occured loading accounts grid');
                    this.closepopup();
                }.bind(this),
                onException: function (request, e) {
                    alert('Error occured loading accounts grid');
                    this.closepopup();
                }.bind(this)
            });

        },
        closepopup: function () {
            $(this.wrapperId).remove();
            $('window-overlay').hide();
        },
        selectAccount: function (grid, event) {
            if (typeof event != 'undefined') {
                var row = Event.findElement(event, 'tr');
                var visibleField = $(this.target + '_label');
                if (typeof row.select('td.col-erp_code')[0] !== 'undefined') {
                    var erp_code = row.select('td.col-erp_code')[0].innerHTML;
                }

                var account_id   = row.title;
                var account_name = row.select('td.col-name')[0].innerHTML;
                $(this.target + '_account_id_' + this.type).value = account_id;
                if (visibleField.nodeName == 'INPUT') {
                    $(this.target + '_label').value = account_name.trim();
                } else {
                    $(this.target + '_label').innerHTML = account_name;
                }

                $(this.target + '_' + this.type + '_label').value = account_name;

                if ($('selected_identity') && erp_code) {
                    $('selected_identity').value = erp_code.trim();
                }

                jQuery('#connection_shopper').show();
                this.resetCustomer(this);

                var data = row.down('.rowdata').value;
                $(this.target + '_account_id_' + this.type).fire('epicor:account_id_change', {data: data});
                this.closepopup();
            }
        },

        /**
         * Select customer from customer grid.
         * Applicable to Punchout connections edit page.
         *
         * @param grid
         * @param event
         */
        selectCustomer: function (grid, event) {
            if (typeof event != 'undefined') {
                var row            = Event.findElement(event, 'tr');
                var customer_id    = row.title;
                var customer_email = row.select('td.col-email')[0].innerHTML;
                $('selected_shopper').value     = customer_id;
                $(this.target + '_label').value = customer_email.trim();
                $(this.target + '_' + this.type + '_label').value = customer_email;
                this.closepopup();
            }
        },

        /**
         * Reset customer for customer grid.
         * Applicable to Punchout connections edit page.
         *
         * @param grid
         * @param event
         */
        resetCustomer: function (event) {
            if (typeof event != 'undefined') {
                jQuery('#selected_shopper').val("");
                jQuery('#customer_name_label').val("");
                jQuery('#customer_name_customer_label').val("");
            }
        },

        switchType: function (target, type) {
            var typeFound = false;          

            $$('.type_field').each(function (e) {
                if (e.readAttribute('id') == target + '_account_id_' + type) {
                    typeFound = true;
                }
            });

            if (!typeFound) {
                $('ecc_account_selector').hide();
            } else {
                var accountLabel = $(target + '_' + type + '_label').value;

                if (accountLabel == '') {
                    accountLabel = $(target + '_no_account').value;
                }

                $(target + '_label').innerHTML = accountLabel;
                $('ecc_account_selector').show();
            }


        },
        removeAccount: function (target) {
            this.type = $(target).value;
            $(target + '_account_id_' + this.type).value = '';
            $(target + '_label').innerHTML = $(target + '_no_account').value;
            $(target + '_account_id_' + this.type).fire('epicor:account_id_change');
        },
        updateWrapper: function () {
            if ($(this.wrapperId)) {
                var height = 20;

                $$('#' + this.wrapperId + ' > *').each(function (item) {
                    height += item.getHeight();
                });

                if (height > ($(document.viewport).getHeight() - 40))
                    height = $(document.viewport).getHeight() - 40;

                if (height < 35)
                    height = 35;

                $(this.wrapperId).setStyle({
                    'height': height + 'px',
                    'marginTop': '-' + (height / 2) + 'px'
                });
            }
        }
    };
    var accountSelector = 'test';
    jQuery(document).ready(function(){
        accountSelector = new Solarsoft.accountSelector();

        jQuery(window).on('resize', function() {
            accountSelector.updateWrapper();
        });

        window.accountSelector = accountSelector;
    });

    window.onkeypress = function (event) {
        if (event.which == 13 || event.keyCode == 13) {
            return false;
        }
        return true;
    };
});