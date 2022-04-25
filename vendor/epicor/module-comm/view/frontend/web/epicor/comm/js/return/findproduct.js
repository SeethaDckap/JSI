/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery',
    'Epicor_Comm/epicor/comm/js/return/addproduct',
    'prototype'
], function (jQuery, AddProduct) {
    
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

  return FindProduct;
  
});