/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
require(['prototype'],function(){
document.observe('dom:loaded', function() { 
    
    $$('.block-account .block-content').invoke('hide');

    $$('.block-account').invoke('observe', 'mouseover', function(event) {
            this.select('.block-content')[0].show();
    });	

    $$('.block-account').invoke('observe', 'mouseout', function(event) {
            this.select('.block-content')[0].hide();
    });	
});
});