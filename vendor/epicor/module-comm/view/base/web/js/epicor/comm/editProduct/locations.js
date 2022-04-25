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
var productLocations = Class.create();
productLocations.prototype = {
    rows: $H({}),
    form: null,
    formWrapper: null,
    tableId: null,
    ajaxRequest: false,
    availableLocations: null,
    addButton: null,
    action: null,
    grid: null,
    deleteMessage: null,
    initialize: function(form, table, grid) {
  
        if ($(form)) {
            this.form = $(form);
            this.url = this.form.select('input[name=locationPostUrl]')[0].value;
            this.deleteMessage = this.form.select('input[name=deleteMessage]')[0].value;
            this.availableLocations = this.form.select('input[name=available_locations]')[0].value;
            this.availableLocations = JSON.parse(this.availableLocations);        

            this.formWrapper = this.form.up();
            this.formWrapper.hide();
        }
        this.grid = grid;
        this.tableId = table;
        if (this.formWrapper != null) {
            jQuery("#save-button").attr("onclick", "if(productLocations.formWrapper.visible()){productLocations.rowUpdate();}");
            jQuery("#save_and_new").attr("onclick", "if(productLocations.formWrapper.visible()){productLocations.rowUpdate();}");
            jQuery("#save_and_duplicate").attr("onclick", "if(productLocations.formWrapper.visible()){productLocations.rowUpdate();}");
            jQuery("#save_and_close").attr("onclick", "if(productLocations.formWrapper.visible()){productLocations.rowUpdate();}");
        }
    },
    table: function() {
        return $(this.tableId);
    },
    rowInit: function(grid, row) {
        
        $(this.grid).select('button').forEach(function(element) {
            if (element.title == 'Add') {
                this.addButton = element;
            }
        }.bind(this));
        if (this.addButton) {
            if (this.availableLocations.length == 0) {
                this.addButton.hide();
            } else {
                this.addButton.show();
            }
        }
            var data = row.select('input.rowdata')[0].value.evalJSON();
            this.rows.set(data.id, data);
            row.select('a').forEach(function(element) {
                if (element.innerHTML == 'Delete') {
                    element.observe('click', this.rowDelete.bind(this));
                } else if (element.innerHTML == 'Edit') {
                    element.observe('click', this.rowEdit.bind(this));
                }
            }.bind(this));
        this.formWrapper.hide();
    },
    rowDelete: function(event) {
        if (this.ajaxRequest) {
            alert('Form already submitted');
        } else if(confirm(this.deleteMessage)) {
            this.ajaxRequest = true;
            var url = event.element().href;
            new Ajax.Request(url, {
                method: 'post',
                onComplete: function(request) {
                    this.ajaxRequest = false;
                }.bind(this),
                onSuccess: function(request) {
                    this.formWrapper.hide();
                    this.alterOptions('delete', request.responseText);
                    locationslistGridJsObject.doFilter();
                }.bind(this),
                onFailure: function(request) {
                    alert('Error occured');
                }.bind(this),
                onException: function(request, e) {
                    alert(e);
                }.bind(this)
            });
        }
        event.stop();
    },
    rowUpdate: function() {
        var data = {};
        this.form.select('input').concat(this.form.select('select')).forEach(function(input) {
            data[input.name] = input.value;
        });
        if (this.ajaxRequest) {
            alert('Form already submitted');
        } else {
            this.ajaxRequest = true;
            new Ajax.Request(this.url, {
                method: 'post',
                parameters: data,
                onComplete: function(request) {
                    this.ajaxRequest = false;
                }.bind(this),
                onSuccess: function(request) {
                    this.formWrapper.hide();
                    this.alterOptions(this.action);
                    locationslistGridJsObject.doFilter();
                }.bind(this),
                onFailure: function(request) {
                    alert('Error occured');
                }.bind(this),
                onException: function(request, e) {
                    alert(e);
                }.bind(this)
            });
        }
        return false;
    },
    alterOptions: function(action, location_code, index) {
        if (index == undefined && action != 'delete') {
            var chosenOption = this.form.select('select[name=location_code]')[0].value;
            index = this.availableLocations.indexOf(chosenOption)
        }
        if (location_code == undefined && action != 'add') {
            location_code = this.rows.get(this.form.select('input[name=id]')[0].value)['location_code'];
        }
        switch (action) {
            case 'add':
                this.availableLocations.splice(index, 1);
                break;
            case 'update':
                this.availableLocations[index] = location_code;
                break;
            case 'delete':
                this.availableLocations.push(location_code);
                break;
        }
        this.action = null;
    },
    rowEdit: function(event, id) {
        if (typeof (id) == 'undefined') {
            id = event.element().up().up().select('input')[1].readAttribute('rel');
        }
        var rowData = null;
        if (id) {
            rowData = this.rows.get(id);
        }
        if (rowData) {
            this.action = 'update';
            for (var key in rowData) {
                if (key == 'manufacturers') {
                    // Blank out the tbody
                    // the add button adds new tbody rows so these need removing
                    $('manufacturers').select('tbody').forEach(function(element, index) {
                        if (index == 0) {
                            element.innerHTML = '';
                        } else {
                            element.remove();
                        }
                    });
                    // get data and add manufacturers rows
                    var manData = rowData[key].evalJSON();
                    if (manData) {
                        manData.forEach(function(element, index) {
                            var newRow = manufacturers_template.split('#{id}').join(index);
                            $('manufacturers').select('tbody')[0].insert(newRow);

                            $('manufacturers_row_' + index).select('input[name:manufacturers[' + index + ']]')[0].value = element.name;
                            $('manufacturers_row_' + index).select('input[name:manufacturers[' + index + ']]')[1].value = element.product_code;
                        
                        });
                    }
                } else {
                    if (this.form.select('input[name=' + key + ']').length > 0) {
                        this.form.select('input[name=' + key + ']')[0].value = rowData[key];
                    } else if (this.form.select('select[name=' + key + ']').length > 0) {
                        this.form.select('select[name=' + key + ']')[0].setValue(rowData[key]);
                        //this.form.select('select[name=' + key + ']')[0].disable();
                    }
                }
            }
            this.revealLocations(rowData['location_code']);
            this.switchOptions = true;
            $('updateLocationSubmit').show();
            $('addLocationSubmit').hide();
            this.formWrapper.show();
        } else {
            alert('Data not found for this row');
        }
        event.stop();
    },
    add: function() {
        this.action = 'add';
        this.revealLocations();
        // Blank out the tbody
        // the add button adds new tbody rows so these need removing
        $('manufacturers').select('tbody').forEach(function(element, index) {
            if (index == 0) {
                element.innerHTML = '';
            } else {
                element.remove();
            }
        });
        this.form.select('input').forEach(function(element) {
            if (element.name != 'currency_code' && element.name != 'updateLocationSubmit' && element.name != 'addLocationSubmit') {
                element.value = '';
            }
        });
        this.form.select('select').forEach(function(element) {
            var options = element.select('option');
            //select the first visible option
            for (var i = 0; i < options.length; i++) {
                if (options[i].visible()) {
                    element.setValue(options[i].value);
                    break;
                }
            }
        });
        this.form.select('select[name=location_code]')[0].enable();
        $('updateLocationSubmit').hide();
        $('addLocationSubmit').show();
        this.switchOptions = false;
        this.formWrapper.show();
    },
        revealLocations: function (currentLocation) {
            this.form.select('select[name=location_code] option').forEach(function (element) {
                var availableLocation =  this.availableLocations.toString();
                if (availableLocation.indexOf(element.value) != -1 || element.value == currentLocation) {
                    element.show();
                } else {
                    element.hide();
                }
            }.bind(this));
        }
};

  return productLocations;
  
});

