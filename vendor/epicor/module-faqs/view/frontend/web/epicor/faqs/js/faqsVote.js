/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

require([
    'jquery',
    'jquery/ui'
], function (jQuery) { 

    jQuery.expr[':'].contains = function(a, i, m) {
        return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
    };
    //jQuery(function() {
    jQuery('document').ready( function(){ 
        var accordionTabs = jQuery('#accordion h3').size();
        var match_found_title = false;
        jQuery('#search_faq').keyup(function() {
            
            jQuery("#accordion h3.faq_question, #accordion div.faq_answer").each(function() {
                var $this = jQuery(this);
                if ($this.is(":contains('" + jQuery('#search_faq').val() + "')")) {
                    match_found_title = $this.is('h3');
                    jQuery('.faqitem-' + $this.attr('rel')).show();
                }
                else {
                    if (!match_found_title) {
                        jQuery('.faqitem-' + $this.attr('rel')).hide();
                    }
                    else {
                        match_found_title = false;
                    }
                }
            });
//            if(jQuery('#search_faq').val() == ''){
//                //can't get accordion collapsible to work to close tabs, so need to do it individually
//                var activeTab = jQuery('#accordion').accordion("option", "active");
//                var i = 0;
//                for (i = 0; i < accordionTabs; i++) {
//                    jQuery('#accordion').accordion("option", "active", i);
//                    jQuery('#accordion').accordion("option", "active", false);
//                }
//                jQuery('#accordion').accordion("option", "active", activeTab);
//            }
        });
        jQuery(document).on('click', '.faq_vote', function() {
            var faq_box = jQuery(this).parent().parent();
            var url_target = jQuery(this).attr('href');
            var vote_value = jQuery(this).hasClass('faq_useful') ? 1 : -1;
            var faqId = faq_box.find('input.faqId').val();
            faq_box.append('<span class="loading">Loading</span>');

            jQuery.post(url_target, {faqId: faqId, vote: vote_value},
            function(data) {
                faq_box.html(data);
            })
                    .fail(function(xhr, textStatus, errorThrown) {
                        alert(xhr.responseText);
                    })
            return false;
        });
    });
});    