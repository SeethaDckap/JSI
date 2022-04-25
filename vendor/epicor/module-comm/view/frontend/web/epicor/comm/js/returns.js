/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

var Returns = Class.create();
Returns.prototype = {
    initialize: function (accordion, urls) {
        this.accordion = accordion;
        this.method = '';
        this.payment = '';
        this.loadWaiting = false;
        this.steps = ['login', 'return', 'products', 'attachments', 'notes', 'review'];
        this.currentStep = 'return';
        this.saveUrl = urls.saveMethod;
        this.failureUrl = urls.failure;

        this.accordion.sections.each(function (section) {
            Event.observe($(section).down('.step-title'), 'click', this._onSectionClick.bindAsEventListener(this));
        }.bind(this));

        this.accordion.disallowAccessToNextSections = true;
    },
    /**
     * Section header click handler
     *
     * @param event
     */
    _onSectionClick: function (event) {
        var section = $(Event.element(event).up().up());
        if (section.hasClassName('allow')) {
            Event.stop(event);
            this.gotoSection(section.readAttribute('id').replace('returns-', ''), false);
            return false;
        }
    },
    ajaxFailure: function () {
        location.href = this.failureUrl;
    },
    _disableEnableAll: function (element, isDisabled) {
        var descendants = element.descendants();
        for (var k in descendants) {
            descendants[k].disabled = isDisabled;
        }
        element.disabled = isDisabled;
    },
    setLoadWaiting: function (step, keepDisabled) {
        if (step) {
            if (this.loadWaiting) {
                this.setLoadWaiting(false);
            }
            var container = $(step + '-buttons-container');
            container.addClassName('disabled');
            container.setStyle({opacity: .5});
            this._disableEnableAll(container, true);
            Element.show(step + '-please-wait');
        } else {
            if (this.loadWaiting) {
                var container = $(this.loadWaiting + '-buttons-container');
                var isDisabled = (keepDisabled ? true : false);
                if (!isDisabled) {
                    container.removeClassName('disabled');
                    container.setStyle({opacity: 1});
                }
                this._disableEnableAll(container, isDisabled);
                Element.hide(this.loadWaiting + '-please-wait');
            }
        }
        this.loadWaiting = step;
    },
    gotoSection: function (section) {

        this.currentStep = section;
        var sectionElement = $('returns-' + section);
        sectionElement.addClassName('allow');
        this.accordion.openSection('returns-' + section);
    },
    changeSection: function (section) {
        var changeStep = section.replace('returns-', '');
        this.gotoSection(changeStep, false);
    },
    removeSection: function (section) {
        if ($('returns-' + section)) {
            number = parseInt($('returns-' + section).down('.step-title span.number').innerHTML);
            $('returns-' + section).remove();
            $$('.opc .section').each(function (e) {
                elNumber = parseInt(e.down('.step-title span.number').innerHTML);
                if (number < elNumber) {
                    elNumber = elNumber - 1;
                    e.down('.step-title span.number').update(elNumber);
                }
            });
        }
    },
    back: function () {
        if (this.loadWaiting)
            return;
        //Navigate back to the previous available step
        var stepIndex = this.steps.indexOf(this.currentStep);
        var section = this.steps[--stepIndex];
        var sectionElement = $('returns-' + section);

        //Traverse back to find the available section. Ex Virtual product does not have shipping section
        while (sectionElement === null && stepIndex > 0) {
            --stepIndex;
            section = this.steps[stepIndex];
            sectionElement = $('returns-' + section);
        }
        this.changeSection('returns-' + section);
    },
    setStepResponse: function (response) {

        if (response.errors) {
            errorMessage = '';
            join = '';
            for (var i = 0; i < response.errors.length; i++) {
                errorMessage += join + response.errors[i];
                join = '\n';
            }
            alert(errorMessage);
            //remove the load waiting sign and reinstate the submit button if required
            tab = response.tab;
            if(tab){
                this.loadWaiting = tab; 
                this.setLoadWaiting(false);  
                if ($(tab + '-submit')) {
                    $(tab + '-submit').show();
                }                
            }else{
                return false;                
            }
            
        }


        if (response.refresh_section) {
            $('returns-step-' + response.refresh_section.name).update(response.refresh_section.html);
        }

        if (response.update_section) {
            $('returns-step-' + response.update_section.name).update(response.update_section.html);
        }

        if (response.allow_sections) {
            response.allow_sections.each(function (e) {
                $('returns-' + e).addClassName('allow');
            });
        }

        if (response.remove_section) {
            this.removeSection(response.remove_section);
        }

        if (response.goto_section) {
            this.gotoSection(response.goto_section, true);
            return true;
        }

        if (response.redirect) {
            location.href = response.redirect;
            return true;
        }
        return false;
    }
}

var TabMaster = Class.create();
TabMaster.prototype = {
    tab: '',
    beforeInitFunc: $H({}),
    afterInitFunc: $H({}),
    beforeValidateFunc: $H({}),
    afterValidateFunc: $H({}),
    beforeSaveFunc: $H({}),
    beforeNextStepFunc: $H({}),
    addBeforeInitFunction: function (code, func) {
        this.beforeInitFunc.set(code, func);
    },
    beforeInit: function () {
        (this.beforeInitFunc).each(function (init) {
            (init.value)();
            ;
        });
    },
    init: function () {
        this.beforeInit();
        var elements = Form.getElements(this.form);
        if ($(this.form)) {
            $(this.form).observe('submit', function (event) {
                this.save();
                Event.stop(event);
            }.bind(this));
        }
        for (var i = 0; i < elements.length; i++) {
            elements[i].setAttribute('autocomplete', 'off');
        }
        this.afterInit();
    },
    initialize: function (form) {
        this.form = form;
        if (form != undefined) {
            this.saveUrl = $(form).readAttribute('action');
        }
        this.onSave = this.nextStep.bindAsEventListener(this);
        this.onComplete = this.resetLoadWaiting.bindAsEventListener(this)

    },
    addAfterInitFunction: function (code, func) {
        this.afterInitFunc.set(code, func);
    },
    afterInit: function () {
        (this.afterInitFunc).each(function (init) {
            (init.value)();
        });
    },
    addBeforeValidateFunction: function (code, func) {
        this.beforeValidateFunc.set(code, func);
    },
    beforeValidate: function () {
        var validateResult = true;
        var hasValidation = false;
        (this.beforeValidateFunc).each(function (validate) {
            hasValidation = true;
            if ((validate.value)() == false) {
                validateResult = false;
            }
        }.bind(this));
        if (!hasValidation) {
            validateResult = false;
        }
        return validateResult;
    },
    validate: function () {
        return true;
    },
    addAfterValidateFunction: function (code, func) {
        this.afterValidateFunc.set(code, func);
    },
    afterValidate: function () {
        var validateResult = true;
        var hasValidation = false;
        (this.afterValidateFunc).each(function (validate) {
            hasValidation = true;
            if ((validate.value)() == false) {
                validateResult = false;
            }
        }.bind(this));
        if (!hasValidation) {
            validateResult = false;
        }
        return validateResult;
    },
    addBeforeSaveFunction: function (code, func) {
        this.beforeSaveFunc.set(code, func);
    },
    beforeSave: function () {
        (this.beforeSaveFunc).each(function (bSave) {
            (bSave.value)();
        });
    },
    save: function () {

        if (returns.loadWaiting != false) {
            return;
        }

        var validator = new Validation(this.form);
        if (this.validate() && validator.validate()) {
            this.beforeSave();
            returns.setLoadWaiting(this.tab);

            if (this.tab != 'lines') {
                var request = new Ajax.Request(
                        this.saveUrl,
                        {
                            method: 'post',
                            onComplete: this.onComplete,
                            onSuccess: this.onSave,
                            onFailure: returns.ajaxFailure.bind(returns),
                            parameters: Form.serialize(this.form)
                        }
                );
            }
        }
    },
    resetLoadWaiting: function () {
        returns.setLoadWaiting(false);
    },
    addBeforeNextStepFunction: function (code, func) {
        this.beforeNextStepFunc.set(code, func);
    },
    beforeNextStep: function (response) {
        (this.beforeNextStepFunc).each(function (bNext) {
            (bNext.value)(response);
        });
    },
    nextStep: function (transport) {
        if (transport && transport.responseText) {
            try {
                response = eval('(' + transport.responseText + ')');
            }
            catch (e) {
                response = {};
            }
        }

        this.beforeNextStep(response);

        /*
         * if there is an error in payment, need to show error message
         */
        if (response.errors) {
            errorMessage = '';
            join = '';
            for (var i = 0; i < response.errors.length; i++) {
                errorMessage += join + response.errors[i];
                join = '\n';
            }
            alert(errorMessage);
            return;
        }

        returns.setStepResponse(response);
    }
}

// TAB : Login as guest / customer

var LoginGuest = Class.create();
LoginGuest.prototype = new TabMaster();
LoginGuest.prototype.addBeforeSaveFunction('loginguest', function () {
    if ($('login-guest-submit')) {
        $('login-guest-submit').hide();
    }
    if ($('login-submit')) {
        $('login-submit').hide();
    }
});
LoginGuest.prototype.tab = '';

LoginGuest.prototype.addBeforeNextStepFunction('loginguest', function () {
    if ($('login-guest-submit')) {
        $('login-guest-submit').show();
    }
    if ($('login-submit')) {
        $('login-submit').show();
    }
});

// TAB: Create / Find Return

var CreateFindReturn = Class.create();
CreateFindReturn.prototype = new TabMaster();
CreateFindReturn.prototype.tab = 'return';
CreateFindReturn.prototype.addBeforeSaveFunction('createfind', function () {
    if ($('create-submit')) {
        $('create-submit').hide();
    }
    if ($('find-submit')) {
        $('find-submit').hide();
    }
});

CreateFindReturn.prototype.addBeforeNextStepFunction('createfind', function () {
    if ($('create-submit')) {
        $('create-submit').show();
    }
    if ($('find-submit')) {
        $('find-submit').show();
    }
});

var UpdateReturn = Class.create();
UpdateReturn.prototype = new TabMaster();
UpdateReturn.prototype.tab = 'return';
UpdateReturn.prototype.addBeforeSaveFunction('return_update', function () {
    if ($('update-submit')) {
        $('update-submit').hide();
    }
});

UpdateReturn.prototype.addBeforeNextStepFunction('return_update', function () {
    if ($('update-submit')) {
        $('update-submit').show();
    }
});

// TAB : Return Products

var AddProduct = Class.create();
AddProduct.prototype = new TabMaster();
AddProduct.prototype.tab = 'products';

AddProduct.prototype.addBeforeSaveFunction('addproduct', function () {
    if ($('add-product-submit')) {
        $('add-product-submit').hide();
    }
    if ($('find-product-submit')) {
        $('find-product-submit').hide();
    }
});

AddProduct.prototype.addBeforeNextStepFunction('addproduct', function () {
    if ($('add-product-submit')) {
        $('add-product-submit').show();
    }
    if ($('find-product-submit')) {
        $('find-product-submit').show();
    }
});

AddProduct.prototype.nextStep = function (transport) {

    if (transport && transport.responseText) {
        try {
            response = eval('(' + transport.responseText + ')');
        }
        catch (e) {
            response = {};
        }
    }

    this.beforeNextStep(response);

    /*
     * if there is an error in payment, need to show error message
     */

    if (response.errors) {
        errorMessage = '';
        join = '';
        for (var i = 0; i < response.errors.length; i++) {
            errorMessage += join + response.errors[i];
            join = '\n';
        }

        alert(errorMessage);
        return;
    }

    if (!response.lines) {
        returns.setStepResponse(response);
    } else {
        for (index = 0; index < response.lines.length; index++) {
            product = response.lines[index];
            addLineRow(product);
        }

        $('sku').value = '';
        $('sku_super_group').value = '';
        $('sku_uom').value = '';
        $('qty').value = '';
        $('packsize_field').hide();

        if (response.hide_add_sku) {
            if ($('add-product-form-holder')) {
                $('add-product-form-holder').hide();
            }

            if ($('lines-adder')) {
                $('lines-adder').removeClassName('col2-set');
            }
        }

        if (response.hide_find_by) {
            if ($('find-product-form-holder')) {
                $('find-product-form-holder').hide();
            }

            if ($('lines-adder')) {
                $('lines-adder').removeClassName('col2-set');
            }
        }

        if (response.restrict_type) {
            if ($('search_type')) {
                $('search_type').select('option').each(function (i) {
                    if (i.value != response.restrict_type) {
                        i.remove();
                    }
                });
                $('search_type').hide();
                $('search_type_label').hide();
                $('search_value_label_text').innerHTML = 'Search By ' + response.restrict_type.capitalizeFirstLetter() + ' Number';
            }
        }
    }
}

var FindProduct = Class.create();
FindProduct.prototype = new AddProduct();
FindProduct.prototype.tab = 'products';

FindProduct.prototype.validate = function () {
    valid = true;
    error = '';
    if ($('mixed-returns-allowed')) {
        if ($('mixed-returns-allowed').value == 'no') {
            var box = $('search_type');
            if (box.tagName == 'SELECT') {
                var search_type = box.selectedIndex >= 0 ? box.options[box.selectedIndex].value : undefined;
            } else {
                var search_type = box.value;
            }

            var search_value = $('search_value').value;

            if (search_type != '' && search_value != '') {
                $$('#return_lines_table tbody tr:not(.attachment)').each(function (e) {
                    if (valid) {
                        if (e.down('.return_line_source_type')) {
                            if (e.down('.return_line_source_type').value != search_type) {
                                valid = false;
                                error = 'type';
                                error_type = e.down('.return_line_source_type').value;
                                error_value = e.down('.return_line_source_value').value;
                            } else if (e.down('.return_line_source_value').value != search_value) {
                                valid = false;
                                error = 'value';
                                error_type = e.down('.return_line_source_type').value;
                                error_value = e.down('.return_line_source_value').value;
                            }
                        }
                    }
                });
            }
        }
    }

    if (!valid) {
        error_type = error_type.capitalizeFirstLetter();
        alert('You cannot add lines of this type to the return, only ' + error_type + ' #' + error_value + ' lines can be added');
    }

    return valid;
};


line_count = 0;

function addLineRow(product) {

    $$('#return_lines_table tbody tr:not(.lines_row)').each(function (e) {
        if (typeof e.up('.lines_row') === 'undefined') {
            e.remove();
        }
    });

    var row = $('return_lines_row_template').clone(true);
    row.addClassName('new');

    row.setAttribute('id', 'lines_' + line_count);

    row = resetInputs(row);

    if (row.down('.plus-minus')) {
        row.down('.plus-minus').writeAttribute('id', 'return_line_attachments_' + line_count);
    }

    row.down('.return_line_number').update(next_line_number);
    next_line_number++;

    var sku_display = product.sku;

    if (product.type_id == 'configurable' || product.type_id == 'grouped') {
        sku_display = sku_display + '<br /><a href="javascript:fireConfigurableProduct(\'' + product.type_id + '\',\'' + line_count + '\',\'' + product.entity_id + '\')">' + 'Configure' + '</a>';
        row.down('.return_line_configured').writeAttribute('value', 'TBC');
        alert('This line requires configuration, please click the "Configure" link\n');
    }

    if (!product.uom) {
        product.uom = '';
    }
    row.down('.return_sku').update(sku_display);
    row.down('.return_uom').update(product.uom);
    row.down('.return_line_sku').writeAttribute('name', 'lines[' + line_count + '][sku]').writeAttribute('value', product.sku);
    row.down('.return_line_uom').writeAttribute('name', 'lines[' + line_count + '][uom]').writeAttribute('value', product.uom);
    row.down('.return_line_returncode').writeAttribute('name', 'lines[' + line_count + '][return_code]').addClassName('validate-select');
    if($$('.return_line_notes').first() != null && $$('.return_line_notes').first() !== 'undefined'){        
        row.down('.return_line_notes').writeAttribute('name', 'lines[' + line_count + '][note_text]');
    }
    row.down('.return_line_source_type').writeAttribute('name', 'lines[' + line_count + '][source]').writeAttribute('value', product.source);
    source_type = product.source.capitalizeFirstLetter();
    source_value = product.source_label.replace(source_type + ' #', '');
    row.down('.return_line_source_value').writeAttribute('name', 'lines[' + line_count + '][source]').writeAttribute('value', source_value);
    row.down('.return_line_source').writeAttribute('name', 'lines[' + line_count + '][source]').writeAttribute('value', product.source);
    row.down('.return_line_source_data').writeAttribute('name', 'lines[' + line_count + '][source_data]').writeAttribute('value', product.source_data);
    row.down('.return_line_delete').writeAttribute('name', 'lines[' + line_count + '][delete]');
    row.down('.return_line_quantity_returned').writeAttribute('name', 'lines[' + line_count + '][quantity_returned]').writeAttribute('value', product.qty_returned);
    row.down('.return_line_quantity_ordered').writeAttribute('name', 'lines[' + line_count + '][quantity_ordered]').writeAttribute('value', product.qty_ordered);

    if (product.qty_ordered != undefined) {
        row.down('.return_line_quantity_ordered_label').update(' / ' + product.qty_ordered);
    }

    row.down('.return_line_source_label').update(product.source_label);
    $('return_lines_table').down('tbody').insert({bottom: row});

    var row = $('return_line_attachments_row_template').clone(true);

    row.addClassName('new');
    row.setAttribute('id', 'row_return_line_attachments_' + line_count);
    row.down('.return_line_attachment_add').writeAttribute('id', 'add_return_line_attachments_' + line_count);
    row.down('#return_line_attachments_').writeAttribute('id', 'return_line_attachments_' + line_count);
    row.down('#return_line_attachments__table').writeAttribute('id', 'return_line_attachments_' + line_count + '_table');
    row.down('#return_line_attachments__attachment_row_template').writeAttribute('id', 'return_line_attachments_' + line_count + '_attachment_row_template');

    $('return_lines_table').down('tbody').insert({bottom: row});

    colorRows('return_lines_table', ':not(.attachment)');
    resetLineNumbers();
    line_count += 1;
}

next_line_number = 1;

function resetLineNumbers() {
    next_line_number = 1;
    $$('#return_lines_table .return_line_number').each(function (el) {
        el.update(next_line_number);
        next_line_number += 1;
    });
}

var Attachments = Class.create();
Attachments.prototype = new TabMaster();
Attachments.prototype.tab = 'attachments';
Attachments.prototype.addBeforeSaveFunction('attachments', function () {
    if ($('attachments-submit')) {
        $('attachments-submit').hide();
    }
});
Attachments.prototype.addBeforeNextStepFunction('attachments', function () {
    if ($('attachments-submit')) {
        $('attachments-submit').show();
    }
});
Attachments.addMethods({
    attachments_count: 0,
    add: function (table_id, fieldName) {

        // customer_returns_attachment_lines_table
        $$('#' + table_id + '_table tbody tr:not(.attachments_row)').each(function (e) {
            e.remove();
        });
        this.attachment_count = $$('#' + table_id + '_table tbody tr.attachments_row').length;

        var row = $(table_id + '_attachment_row_template').clone(true);
        row.addClassName('new');
        row.setAttribute('id', table_id + 'attachments_' + this.attachments_count);

        resetInputs(row);

        row.down('.attachments_delete').writeAttribute('name', fieldName + '[' + this.attachments_count + '][delete]');
        row.down('.attachments_description').writeAttribute('name', fieldName + '[' + this.attachments_count + '][description]');
        row.down('.attachments_filename').writeAttribute('name', fieldName + '[' + this.attachments_count + '][filename]');

        $(table_id + '_table').down('tbody').insert({bottom: row});
        colorRows(table_id, '');
        this.attachments_count += 1;
    }
});

document.observe('dom:loaded', function (element) {

    Event.live('.return_line_delete', 'click', function (el, event) {
        var attRowId = el.up('tr').readAttribute('id').replace('lines_', 'row_return_line_attachments_');
        deleteElement(el, 'return_lines');
        if (el.parentNode.parentNode.hasClassName('new')) {
            if ($(attRowId)) {
                $(attRowId).remove();
            }
        }
        checkCount('return_lines', 'lines_row', 10);
        colorRows('return_lines_table', ':not(.attachment)');
        resetLineNumbers();
    });

    Event.live('.attachments_delete', 'click', function (el, event) {

        var table = el.up('table').readAttribute('id').replace('_table', '');
        deleteElement(el, table);
        checkCount(table, 'attachments_row', 3);
    });


    Event.live('.attachments_add', 'click', function (el, event) {
        var table = el.readAttribute('id').replace('add_', '');
        var fieldId = el.readAttribute('id').replace('add_return_line_attachments_', '').replace('add_return_attachments_', '');
        Attachments.prototype.add(table, 'lineattachments[new][' + fieldId + ']');
        event.stop();
    });

    Event.live('.return_attachments_add', 'click', function (el, event) {
        var table = el.readAttribute('id').replace('add_', '');
        Attachments.prototype.add(table, 'attachments[new]');
        event.stop();
    });

    Event.live('.expand-row', 'mouseover', function (element) {
        element.style.cursor = "pointer";
    });

    Event.live('.expand-row', 'click', function (element) {
        id = element.down(".plus-minus").readAttribute('id');
        if ($('row_' + id)) {
            $('row_' + id).toggle();
            element.down(".plus-minus").innerHTML == '-' ? element.down(".plus-minus").innerHTML = '+' : element.down(".plus-minus").innerHTML = '-';
        }
    });

    Event.live('#lines-submit', 'click', function (el, event) {
        linesform = $('lines-form');
        var validator = new Validation(linesform);

        configuratorError = false;
        $$('.return_line_configured').each(function (e) {
            if (e.value == 'TBC') {
                configuratorError = true;
            }
        });

        if (!validator.validate() || configuratorError) {
            if (configuratorError) {
                jQuery('body').loader('hide');
                alert('One or more lines require configuration, please see lines with a "Configure" link\n');
            }

            event.stop();
        } else {
            el.hide();
            $('lines-please-wait').show();
        }
    });

    Event.live('#attachments-submit', 'click', function (el, event) {
        el.hide();
        $('attachments-please-wait').show();
    });

});

var SubmitLines = Class.create();
SubmitLines.prototype = new TabMaster();
SubmitLines.prototype.tab = 'lines';

SubmitLines.prototype.addBeforeSaveFunction('lines', function () {
    if ($('lines-submit')) {
        $('lines-submit').hide();
    }
});

SubmitLines.prototype.addBeforeNextStepFunction('lines', function () {
    if ($('lines-submit')) {
        $('lines-submit').show();
    }
});


// TAB : Notes
var Notes = Class.create();
Notes.prototype = new TabMaster();
Notes.prototype.tab = 'notes';
Notes.prototype.addBeforeSaveFunction('notes', function () {
    if ($('notes-submit')) {
        $('notes-submit').hide();
    }
});

Notes.prototype.addBeforeNextStepFunction('notes', function () {
    if ($('notes-submit')) {
        $('notes-submit').show();
    }
});

// TAB: Review

var Review = Class.create();
Review.prototype = new TabMaster();
Review.prototype.tab = 'review';
Review.prototype.addBeforeSaveFunction('review', function () {
    if ($('review-submit')) {
        $('review-submit').hide();
    }
});

Review.prototype.addBeforeNextStepFunction('review', function () {
    if ($('review-submit')) {
        $('review-submit').show();
    }
});

String.prototype.capitalizeFirstLetter = function () {
    return this.charAt(0).toUpperCase() + this.slice(1);
}

var configurator_line = '';
var configure_id = '';

function fireConfigurableProduct(type_id, line_id, product_id, child_id)
{
    var url = $('configurable_url').value;
    var row = $('lines_' + line_id);
    var sku = row.down('.return_line_sku').value;
    var qty = row.down('.return_line_quantity_ordered').value;

    $('loading-mask').show();

    if (type_id == 'grouped') {
        var form_data = {'productid': product_id, 'child': child_id};
    } else {
        var form_data = {'productid': product_id, 'child': child_id, 'qty': qty};
    }
    configurator_line = line_id;
    configure_id = product_id;

    performAjax(url, 'post', form_data, function (data) {
        var json = data.responseText.evalJSON();
        if (!json.error || json.error == '') {
            optionsPrice = new Product.OptionsPrice(json.jsonconfig);
            $('loading-mask').hide();
            showConfigureOverlay(json.html);
        } else {
            alert(json.error);
            if (json.error) {
                showMessage(json.error, 'error');
            }
        }

        $('loading-mask').hide();
    });
}

function showConfigureOverlay(htmlToShow) {
    var body = document.body, html = document.documentElement;
    var width = Math.max(body.scrollWidth, body.offsetWidth, html.clientWidth, html.scrollWidth, html.offsetWidth);
    var height = Math.max(body.scrollHeight, body.offsetHeight, html.clientHeight, html.scrollHeight, html.offsetHeight);
    $('window-overlay-content').update(htmlToShow);
    $('window-overlay').setStyle({width: width + 'px', height: height + 'px'});
    $('window-overlay').show();

    var elementHeight = $('window-overlay-content').getHeight();
    var elementWidth = $('window-overlay-content').getWidth();
    var formwidth = ((html.clientWidth - elementWidth) / 2);
    var viewport = document.viewport.getDimensions();   // Gets the viewport as an object literal
    var visibleheight = viewport.height;                       // Usable window height
    var topoffset = (visibleheight - elementHeight - 50) / 2;
    $('window-overlay-content').setStyle({'top': topoffset + 'px', 'left': formwidth + 'px'});
    $('window-overlay-content').show();
}

function submitConfigurableProduct() {
    $('window-overlay').hide();
    $('loading-mask').show();
    var url = $('configure_product_form').readAttribute('action');
    var form_data = $('configure_product_form').serialize(true);
    
    performAjax(url, 'post', form_data, function (data) {
        var json = data.responseText.evalJSON();

        if (json.grouped) {
            var row = $('lines_' + configurator_line);
            row.remove();

            for (index = 0; index < json.grouped.length; index++) {
                product = json.grouped[index];

                if (product.sku.search(escapeRegExp($('uom_separator').value)) != -1) {
                    product.sku = product.sku.replace($('uom_separator').value + product.uom, '');
                }

                product.qty_returned = product.qty;
                product.source = 'SKU';
                product.source_label = 'SKU';
                product.source_data = '';

                addLineRow(product);
            }
            $('window-overlay-content').update('');
        } else if (json[configure_id]) {
            var product = json[configure_id];
            var row = $('lines_' + configurator_line);

            if (!product.uom) {
                product.uom = '';
            }
            row.down('.return_sku').update(product.sku);
            row.down('.return_uom').update(product.uom);
            row.down('.return_line_sku').writeAttribute('value', product.sku);
            row.down('.return_line_uom').writeAttribute('value', product.uom);
            row.down('.return_line_returncode').addClassName('validate-select');
            row.down('.return_line_configured').writeAttribute('value', '');
            $('window-overlay-content').update('');
        } else {
            if (json.error) {
                alert(json.error);
                $('window-overlay').show();
            }
        }
        $('loading-mask').hide();
    });

}


document.observe('dom:loaded', function () {
    Event.live('#configure_product', 'click', function (el, event) {
        $('loading-mask').show();
        submitConfigurableProduct();
        event.stop();
    });

    Event.live('#cancel_configure_product', 'click', function (el, event) {
        $('window-overlay').hide();
        event.stop();
    });
});

function escapeRegExp(str) {
    return str.replace(/[\-\[\]\/\{\}\(\)\*\+\?\.\\\^\$\|]/g, "\\$&");
}