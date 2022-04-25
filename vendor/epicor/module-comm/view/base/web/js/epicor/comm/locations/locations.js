/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */



// phrases grid update code 
if (typeof productLocations == 'undefined') {
    var productLocations = null;
}
// phrases grid update code 
if (typeof Epicor_Locations == 'undefined') {
    var Epicor_Locations = {};
}
Epicor_Locations.productLocations = Class.create();
Epicor_Locations.productLocations.prototype = {
    rows: $H({}),
    form: null,
    formWrapper: null,
    tableId: null,
    ajaxRequest: false,
    availableLocations: null,
    addButton: null,
    action: null,
    grid: null,
    initialize: function(form, table, grid) {
        if ($(form)) {
            this.form = $(form);
            this.url = this.form.select('input[name=locationPostUrl]')[0].value;
            this.formWrapper = this.form.up();
            this.formWrapper.hide();
        }
        this.grid = grid;
        this.tableId = table;
    },
    table: function() {
        return $(this.tableId);
    },
    rowInit: function(grid, row) {
        var data = row.select('input[name:rowData[]]')[0].value.evalJSON();
        this.rows.set(data.entity_id, data);
        this.formWrapper.hide();
    },
    rowDelete: function(event) {
        if (this.ajaxRequest) {
            alert('Form already submitted');
        } else {
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
                    product_locationsJsObject.doFilter();
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
                    product_locationsJsObject.doFilter();
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
    rowEdit: function(row, id) {
        var rowData = this.rows.get(id);
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
                    var manData = unserialize(rowData[key]);
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
        //this.form.select('select[name=location_code]')[0].enable();
        $('updateLocationSubmit').hide();
        $('addLocationSubmit').show();
        this.switchOptions = false;
        this.formWrapper.show();
    },
    revealLocations: function(currentLocation) {
        this.form.select('select[name=location_code] option').forEach(function(element) {
            if (inArray(element.value, this.availableLocations) || element.value == currentLocation) {
                element.show();
            } else {
                element.hide();
            }
        }.bind(this));
    }
};

