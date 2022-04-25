/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

require([
    'jquery',
    'jquery/ui'
], function (jQuery) { 
    jQuery('document').ready( function(){
            jQuery('#accordion').accordion({ 
                active: 0,
                heightStyle: "content",
                collapsible:true
            });
        })
})