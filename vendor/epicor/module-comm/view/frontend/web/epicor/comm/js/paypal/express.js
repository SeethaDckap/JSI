/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

document.observe('dom:loaded', function() { 
		
    if(typeof($$('#shipping-address-form ul li')) != 'undefined'){
        $$('#shipping-address-form ul li').invoke('removeClassName', 'wide');
    }
    if(typeof($$('#billing-address-form ul li')) != 'undefined'){
        $$('#billing-address-form ul li').invoke('removeClassName', 'wide');
    }

})