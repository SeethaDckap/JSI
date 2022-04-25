/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
Event.live = function (s, e, f) {
    Event.observe(document, e, function (event, element) {
        if (element = event.findElement(s)) {
            f(element, event);
        }
    });
};
if (typeof Epicor_Common == 'undefined') {
    var Epicor_Common = {};
}
require([
    'jquery',
    'prototype',
    'mage/translate'
], function (jQuery) {
    Epicor_Common.common = Class.create();
    Epicor_Common.common.prototype = {
        initialize: function () {
        },
        resetInputs: function (row) {
            row.select('input,select,textarea').each(function (e) {
                if (e.readAttribute('type') == 'text' || e.tagName == 'textarea') {
                    e.writeAttribute('value', '');
                } else if (e.readAttribute('type') == 'checkbox') {
                    e.writeAttribute('checked', false);
                }

                e.writeAttribute('disabled', false);
            });
            return row;
        },
        performAjax: function (url, method, data, onSuccessFunction) { 
            if (jQuery('#loading-mask')) {
                jQuery('#loading-mask').hide();
            } 
            this.ajaxRequest = jQuery.ajax({
                url:url,
                type: method,
                data: data,        
                //contentType: 'application/json',
                success: function(data, status, xhr) {
                 return onSuccessFunction(xhr);
                },
                error: function (xhr, status, errorThrown) {
                    if (jQuery('#loading-mask')) {
                        jQuery('#loading-mask').hide();
                    } 
                }                
            });
        },
        inIframe: function () {
            try {
                return window.self !== window.top;
            } catch (e) {
                return true;
            }
        },
        colorRows: function (table_id, table_extra) {
            var cssClass = 'even';
            jQuery('#' + table_id + ' tbody tr' + table_extra).each(function () {
                if (jQuery(this).is(":visible")) {
                    jQuery(this).removeClass('even');
                    jQuery(this).removeClass('odd');
                    jQuery(this).addClass(cssClass);
                    if (cssClass == 'even') {
                       return cssClass = 'odd';
                    } else {
                       return cssClass = 'even';
                    }
                }
            });
        },
        deleteElement: function (el, table_id) {
            var disabled = false;
            var element =jQuery(el);
            if (jQuery(element).prop('checked')) {
                disabled = true;
            }
            if (jQuery(element).parent().parent().hasClass('new')) {
                jQuery(element).parent().parent().remove();
                common.colorRows(table_id, '');
            } else {
                jQuery(element).parent().parent().find('input[type=text],input[type=file],select,textarea').each(function () {
                    jQuery(this).prop('disabled',disabled);
                });
            }
        },
        checkCount: function (table, rowclass, colspan) {
            var rowCount = jQuery('#' + table + '_table tbody tr.' + rowclass).filter(function() {
              return jQuery(this).css('display') !== 'none';
            }).length;    
            if (rowCount == 0) {
                var insertrow = '<tr class="even" style="">'
                        + '<td colspan="' + colspan + '" class="empty-text a-center">' + jQuery.mage.__('No records found.') + '</td>'
                        + '</tr>';
                jQuery('#' + table + '_table tbody').append(insertrow);
            }
        },
        formatNumber: function (el, allowNegatives, allowFloats) {
            var value = el.value, firstChar, nextFirst;
            if (value.length == 0)
                return;
            firstChar = value.charAt(0);
            if (allowFloats) {
                value = value.replace(/[^0-9\.]/g, '');
                nextFirst = value.charAt(0);
            } else {
                value = parseInt(value);
                nextFirst = '';
            }
            if (nextFirst == '.') {
                value = '0' + value;
            }
            if (allowNegatives && firstChar == '-') {
                value = firstChar + value;
            }
            el.value = value;
        }
    };
    var common = 'test';
    document.observe('dom:loaded', function () {
        common = new Epicor_Common.common();
        window.common = common;
    });
    jQuery(document).ready(function(){
        let ele       = jQuery("#search_mini_form #search");
        let submitEle = jQuery("#search_mini_form .action.search");
        // Clearing Search box on load
        ele.val("");
        submitEle.attr("disabled", "disabled");
        //Enable search submit
        Event.live("#search_mini_form #search", 'keyup', function (el, event) {
            if (ele.val() == '') {
                        submitEle.attr("disabled", "disabled");
                    } else {
                        submitEle.removeAttr('disabled');
                    }
        });
        //activate all click events on page after page is loaded
        jQuery('body').css('pointer-events', 'all');
        jQuery(document).on('keypress', '.cart-item-qty', function(event) {
            var elem = jQuery(this);
            var qty = elem.val();
            var pointIndex = qty.indexOf('.');
            var decimalPlace = elem.data('decimal-place');
            if (decimalPlace !== '' && decimalPlace > 0) {
                if (pointIndex >= 0 && pointIndex < qty.length - decimalPlace) {
                    event.stopPropagation();
                    event.preventDefault();
                    return false;
                }
            } else if (decimalPlace !== '' && decimalPlace == 0 && event.keyCode == 46)  {
                event.stopPropagation();
                event.preventDefault();
                return false;
            }
        });

        // invoice and order print issue resolved with locations & without locationa
       var thlength = jQuery(".page-print .order-details-items #my-orders-table thead tr th" );
       var thlengthinv = jQuery(".order-details-items.invoice .table-order-items.invoice thead tr th" );
           var thlengthinva = thlengthinv.length;
           var thlengtha = thlength.length;
           if(thlengtha == 7){
               thlength.addClass("haslocation");
           }
           else {
               thlength.removeClass("haslocation");
           }

            if(thlengthinva == 7){
                thlengthinv.addClass("haslocation");
            }
            else {
                thlengthinv.removeClass("haslocation");
            }
    });
    confirmMessage = function (href) {
        if (!$('window-overlay')) {
            $(document.body).insert('<div id="window-overlay" class="window-overlay" style="display:none;"></div>');
        }
        if (!$('loading-mask')) {
            $(document.body).insert(
                '<div id="loading-mask" style="display:none;"><p class="loader" id="loading_mask_loader">Please wait...</p></div>'
            );
        }
        $('confirm_html').hide();
        $$('#confirm_html .message').first().update('Remove Existing Items From Cart?');
        $('window-overlay').appendChild($('confirm_html').remove())
        $('confirm_html').show();
        $('window-overlay').show();
        positionOverlayElement('confirm_html', 260, 110);
    };    

    window.positionOverlayElement = function (elementId, useWidth, useHeight, noHeight) {
        var availableHeight = $(document.viewport).getHeight();
        var elementHeight = 0;
        if (this.height) {
            if (this.height < availableHeight * .6) {
                elementHeight = availableHeight * .6;
            } else {
                elementHeight = this.height;
            }
        } else {
            if (useHeight) {
                elementHeight = useHeight;
            } else {
                elementHeight = parseInt(availableHeight * .8);
            }
        }
        var elementWidth = $(elementId).getWidth();
        $(elementId).select('.box-account').each(function (z) {
            layout = new Element.Layout(z);
            boxAccountPaddingHeight = layout.get('padding-top');
            boxAccountPaddingBottom = layout.get('padding-bottom');
            elementHeight += boxAccountPaddingHeight;
        });
        if (noHeight == undefined) {
            $(elementId).setStyle({'height': elementHeight + 'px'});
        }
        elementWidth = $(document.viewport).getWidth() * .8;
        if (useWidth !== undefined) {
            elementWidth = useWidth;
        }
        var availableWidth = $(document.viewport).getWidth();
        if ((availableWidth - elementWidth) < 0) {
            var left = 0;
        } else {
            var left = (availableWidth - elementWidth) / 2;
        }
        if ((availableHeight - elementHeight) < 0) {
            var top = 20;
        } else {
            var top = (availableHeight - elementHeight) / 2;
        }
        if ($(elementId)) {
            var height = 22;
            $$('#' + elementId).each(function (item) {
                height += item.getHeight();
            });

            if (height > ($(document.viewport).getHeight() - 40))
                height = $(document.viewport).getHeight() - 40;

            if (height < 35) {
                height = 35;
                top:0;
            }
            if (useHeight !== undefined) {
                height = useHeight;
            }
            if (useWidth !== undefined) {
                elementWidth = useWidth;
            }
            $(elementId).setStyle({
                'width': elementWidth + 'px',
                'marginTop': top + 'px',
                'marginLeft': left + 'px',
            });
            if (noHeight == undefined) {
                $(elementId).setStyle({
                    'height': height + 'px',
                });
            }
        }
    }
});
function checkDecimal(ele) {
    var error = 0;
    ele.each(function () { 
        if(jQuery(this).val() > 0) {
            productForm = jQuery(this).parent();
            var validQty = productForm.validation() && productForm.validation('isValid');
            if (!validQty) {
                error++;
            }
        }
    });
    if (error == 0) {
        return true;
    } else {
        return false;
    }
}
function validateDecimalPlaces(el) {
    el.find('.validation-advice').each(function() {
        jQuery(this).remove();
    });
    var error = 0;
    el.find('.qty').each(function() {
        var value = jQuery(this).val();
        value = jQuery.trim(value);
        var dataValidate = jQuery(this).attr('data-validate');
        if (dataValidate == null) {
            return true;
        }
        objValidate =  jQuery.parseJSON(dataValidate);
        decimalPlaces = objValidate.validatedecimalplace;
        msg = "Decimal Places not Permitted";
        if (decimalPlaces > 0) {
            zero = '';
            for (j = 0; j < decimalPlaces; j++) {
                zero = zero + 'x';
            }
            msg = "Qty must be in the form of xxx." + zero;
        }
        if (decimalPlaces !== '') {
            if (value != '') {
                var numNum = +value;
                if (!isNaN(numNum)) {
                    if (value > 0) {
                        var isdecimal = (value.match(/\./g) || []).length;
                        var decimal = 0;
                        if (isdecimal > 0) {
                            decimal = parseInt(value.toString().split(".")[1].length || 0);
                        }
                        if ((decimalPlaces == 0 && isdecimal > 0) || (decimalPlaces > 0 && isdecimal > 0 && decimal == 0) || (decimalPlaces > 0 && decimal > 0 && decimal > decimalPlaces) || (decimalPlaces == 0 && decimal > 0)) {
                            var html = '<div class="validation-advice">' + msg + '</div>';
                            jQuery(this).after(html);
                            error = error + 1;

                        }
                    }
                } else {
                    var html = '<div class="validation-advice">Enter a Valid Qty</div>';
                    jQuery(this).after(html);
                    error = error + 1;
                }
            }
        }
    });

    if (error == 0)
    {
        return true;
    } else
    {
        return false;
    }
}