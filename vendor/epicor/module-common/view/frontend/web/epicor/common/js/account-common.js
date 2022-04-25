/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
require(['jquery', 'domReady'], function(jQuery, domReady) {
    domReady(function() {
        jQuery(document).on('mouseover', '.expand-row', function() {
            jQuery(this).css('cursor', 'pointer');
        });
        jQuery(document).on('click', '.expand-row', function() {
            var id = jQuery(this).find('.plus-minus').prop('id');
            var unique = jQuery(this).find('.plus-minus').attr('uni');
            var type = jQuery(this).find('.plus-minus').attr('type');
            //For RFQS/BOM Page
            if (jQuery('#form-' + id).length > 0 && jQuery('#form-' + id).css('display') == "block") {
                jQuery('#form-' + id).toggle();
                jQuery(this).find(".plus-minus").html() == '-' ? jQuery(this).find(".plus-minus").html('+') : jQuery(this).find(".plus-minus").html('-');
            } else if (jQuery('#row-' + id).length > 0 || (type === 'orders' && jQuery('#row-misc-' + unique).length > 0)) {
                jQuery('#row-' + id).toggle();
                if(type === 'orders' || type === 'quotes'){
                    jQuery('#row-misc-' + unique).toggle();
                }
                if (type === 'po') {
                    var attachmentId = id.replace("releases", "attachments")
                    jQuery('#row-' + attachmentId).toggle();
                }
                jQuery(this).find(".plus-minus").html() == '-' ? jQuery(this).find(".plus-minus").html('+') : jQuery(this).find(".plus-minus").html('-');
            }
            //For Returns Page
            if (jQuery('#return-' + id).length > 0) {
                jQuery('#return-' + id).toggle();
                jQuery(this).find(".plus-minus").html() == '-' ? jQuery(this).find(".plus-minus").html('+') : jQuery(this).find(".plus-minus").html('-');
            }
        });
        jQuery(document).on('click', '.dropdown-content a', function() {
            var id = jQuery(this).attr('stat');
            var expand = jQuery('#'+id);
            if (jQuery('#form-' + id).length > 0) {
                jQuery('#form-' + id).toggle();
                if(jQuery('#row-' + id).css('display') == "block") {
                    jQuery('#row-' + id).toggle();
                    jQuery('#'+id).html('-');
                }else{
                    (jQuery('#'+id).html() == '-') ? jQuery('#'+id).html('+') : jQuery('#'+id).html('-');
                }
            }
        });
        if (jQuery('#customerconnect_rph').length > 0) {
            jQuery('#customerconnect_rph th:has(a[name="last_ordered_date"])').each(function() {
                jQuery(this).prop('style', 'width: 160px');
            });
            jQuery('#customerconnect_rph div.range-line:has(input[name="last_ordered_date[from]"])').each(function() {
                jQuery(this).prop('style', 'width: 160px');
            });
            jQuery('#customerconnect_rph div.range-line:has(input[name="last_ordered_date[to]"])').each(function() {
                jQuery(this).prop('style', 'width: 160px');
            });

            jQuery('input[name="last_ordered_date[from]"]').each(function() {
                jQuery(this).prop('style', 'width: 100px !important');
            });
            jQuery('input[name="last_ordered_date[to]"]').each(function() {
                jQuery(this).prop('style', 'width: 100px !important');
            });
        }
    });
});