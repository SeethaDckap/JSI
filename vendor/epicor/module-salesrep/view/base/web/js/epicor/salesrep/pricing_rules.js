/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */



// phrases grid update code 
/*if (typeof Epicor_SalesRep_Pricing == 'undefined') {
 var Epicor_SalesRep_Pricing = {};
 }*/
define([
    'Epicor_SalesRep/js/epicor/salesrep/rules',
    'jquery',
    'prototype'
], function (VarienRulesForm,jQuery) {
    var pricingRules = Class.create();
    pricingRules.prototype = {
        rows: $H({}),
        form: null,
        formWrapper: null,
        tableId: null,
        ajaxRequest: false,
        addButton: null,
        action: null,
        grid: null,
        deleteMessage: null,
        conditions: null,
        initialize: function (form, table, grid) {
            if ($(form)) {
                this.form = $(form);
                this.url = this.form.select('input[name=pricingRulePostUrl]')[0].value;
                this.deleteMessage = this.form.select('input[name=deleteMessage]')[0].value;
                this.formWrapper = this.form.up('.entry-edit');
                this.formWrapper.hide();
            }
            this.grid = grid;
            this.tableId = table;
            //   this.conditions = $('conditions__1__children').innerHTML;
            if ($('rule_conditions_fieldset')) {
                this.conditions = $('rule_conditions_fieldset').innerHTML
            }

        },
        table: function () {
            return $(this.tableId);
        },
        rowInit: function (grid, row) {
            $(this.grid).select('button').forEach(function (element) {
                if (element.title == 'Add') {
                    this.addButton = element;
                }
            }.bind(this));
            
            if(row.select('input.rowdata').length){
                    var data = row.select('input.rowdata')[0].value.evalJSON();
                    this.rows.set(data.id, data);
                    row.select('a').forEach(function (element) {
                        if (element.innerHTML == 'Delete') {
                            element.observe('click', this.rowDelete.bind(this));
                        } else if (element.innerHTML == 'Edit') {
                            element.observe('click', this.rowEdit.bind(this));
                        }
                    }.bind(this));
            }
            this.formWrapper.hide();
        },
        rowDelete: function (event) {
            if (this.ajaxRequest) {
                alert('Form already submitted');
            } else if (confirm(this.deleteMessage)) {
                this.ajaxRequest = true;
                var url = event.element().href;
                new Ajax.Request(url, {
                    method: 'post',
                    onComplete: function (request) {
                        this.ajaxRequest = false;
                    }.bind(this),
                    onSuccess: function (request) {
                        this.formWrapper.hide();
                        pricing_rulesJsObject.doFilter();
                    }.bind(this),
                    onFailure: function (request) {
                        alert('Error occured');
                    }.bind(this),
                    onException: function (request, e) {
                        alert(e);
                    }.bind(this)
                });
            }
            event.stop();
        },
        rowUpdate: function () {
            var data = {};

            //var pricingForm = new varienForm(this.form);
            //if (pricingForm.validate()) {
            this.formWrapper.select('input').concat(this.formWrapper.select('select')).forEach(function (input) {
                data[input.name] = input.value;
            });
            if (this.ajaxRequest) {
                alert('Form already submitted');
            } else {
                this.ajaxRequest = true;
                new Ajax.Request(this.url, {
                    method: 'post',
                    parameters: data,
                    onComplete: function (request) {
                        this.ajaxRequest = false;
                    }.bind(this),
                    onSuccess: function (request) {
                        this.formWrapper.hide();
                        pricing_rulesJsObject.doFilter();
                    }.bind(this),
                    onFailure: function (request) {
                        alert('Error occured');
                    }.bind(this),
                    onException: function (request, e) {
                        alert(e);
                    }.bind(this)
                });
            }
            //}
            return false;
        },
        close: function () {
            this.formWrapper.hide();
            this.form.select('input.required-entry').forEach(function (element) {
                if (element.name != 'pricingRulePostUrl' && element.name != 'updatePricingRuleSubmit' && element.name != 'addPricingRuleSubmit') {
                    element.value = 'default';
                }
            });
        },
        rowEdit: function (event, id) {
            if (typeof (id) == 'undefined') {
                id = event.element().up().up().select('input.rowdata')[0].readAttribute('rel');
            }
            var rowData = null;
            if (id) {
                rowData = this.rows.get(id);
            }
            if (rowData) {
                this.action = 'update';
                for (var key in rowData) {
                    var newValue = rowData[key];
                    if (key.indexOf('_date') >= 0 && newValue) {
                        newValue = newValue.split(' 00:00:00').join('');
                    }
                    if (this.formWrapper.select('input[name=' + key + ']').length > 0) {
                        this.formWrapper.select('input[name=' + key + ']')[0].value = newValue;
                    } else if (this.formWrapper.select('select[name=' + key + ']').length > 0) {
                        this.formWrapper.select('select[name=' + key + ']')[0].setValue(newValue);
                    }
                }
                if ($('conditions_' + id)) {
                    var conditionshtml = $('conditions_' + id).innerHTML;
                    $('rule_conditions_fieldset').update(conditionshtml);
                    rule_conditions_fieldset = new VarienRulesForm('rule_conditions_fieldset', rule_conditions_fieldset.newChildUrl);
                    if (!$('updatePricingRuleSubmit')) {
                        rule_conditions_fieldset.setReadonly(true);
                    }
                }
                this.switchOptions = true;
                if ($('updatePricingRuleSubmit')) {
                    $('updatePricingRuleSubmit').show();
                }
                if ($('addPricingRuleSubmit')) {
                    $('addPricingRuleSubmit').hide();
                }
                this.formWrapper.show();
            } else {
                alert('Data not found for this row');
            }
            event.stop();
        },
        add: function () {
            this.action = 'add';
            this.resetconditions();
            // Blank out the tbody
            // the add button adds new tbody rows so these need removing
            this.formWrapper.select('input').forEach(function (element) {
                if (element.name != 'pricingRulePostUrl' && element.name != 'updatePricingRuleSubmit' && element.name != 'addPricingRuleSubmit') {
                    element.value = '';
                }
            });
            this.formWrapper.select('select').forEach(function (element) {
                var options = element.select('option');

                for (var i = 0; i < options.length; i++) {
                    if (options[i].visible()) {
                        element.setValue(options[i].value);
                        break;
                    }
                }
            });
            //this.form.select('select[name=location_code]')[0].enable();


            if ($('updatePricingRuleSubmit')) {
                $('updatePricingRuleSubmit').hide();
            }
            if ($('addPricingRuleSubmit')) {
                $('addPricingRuleSubmit').show();
            }
            this.switchOptions = false;
            this.formWrapper.show();
        },
        resetconditions: function () {
            var conditionshtml = this.conditions;
            $('rule_conditions_fieldset').update(conditionshtml);
            rule_conditions_fieldset = new VarienRulesForm('rule_conditions_fieldset', rule_conditions_fieldset.newChildUrl);
            if (!$('updatePricingRuleSubmit')) {
                rule_conditions_fieldset.setReadonly(true);
            }
        }
    };

    return pricingRules;

    //document.observe('dom:loaded', function () {
    //window.pricingRules = new Epicor_SalesRep_Pricing.pricingRules('pricing_rule_form','pricing_rules_table','pricing_rules');

    /* $$("button.save").forEach(function (element) {
     element.writeAttribute("onclick", "validatePricingRules(function(){" + element.readAttribute("onclick").replace('"', '\"') + "});");
     });*/
    //});
});


/*
 function validatePricingRules(callbackFunction) {
 if (typeof (pricingRules) != 'undefined' && pricingRules.formWrapper != null && pricingRules.formWrapper.visible()) {
 var pricingForm = new varienForm(pricingRules.form);
 if (pricingForm.validate()) {
 pricingRules.rowUpdate();
 callbackFunction();
 }
 } else {
 callbackFunction();
 }
 }*/
