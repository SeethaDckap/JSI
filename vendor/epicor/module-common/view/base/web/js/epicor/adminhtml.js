/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

require([
    'jquery',
    'domReady!',
    'prototype'
], function (jquery) {
        jquery(document).ready(function()
        {    
            var slElements = jquery('select.rel-to-selected');
            if (parseInt(slElements.length) > 0) {
                jquery('select.rel-to-selected').each(function(){
                  var optText = jquery(this).attr('rel');
                  if (typeof optText !== typeof undefined && optText !== false) {
                    if (optText.length) {
                        jquery(this).find('option[value="'+optText+'"]').prop('selected', true);  
                    }
                  }
                });
            }
        });
});