define(
    [
        'jquery',
        'Epicor_BranchPickup/js/epicor/model/branch-common-utils',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/model/quote',
        'Epicor_Comm/epicor/comm/js/model/noticeList'
    ],
    function($, Branchutils,rateRegistry,quote, messageContainer) {
        'use strict';

        return function(target) {
        // modify target
        var navigateTo = target.navigateTo;
        target.navigateTo = function(code, scrollToElementId){ 
             var result = navigateTo.apply(this, arguments);
                messageContainer.clear();
                if (code == "shipping" || this.getActiveItemIndex() == 0) {
                   if(window.checkoutConfig.isSalesRepContactenabled){
                       var elem = $("#contact-block");
                        elem.show();
                    }
                } else {
                    $('#contact-block').hide();
                }
                
            //var result = navigateTo.apply(this, arguments);
            return result; 
        };
        var isProcessed = target.isProcessed;
        target.isProcessed = function(code){ 
            
            var hashString = window.location.hash.replace('#', '');
            if(hashString == 'payment'){
                $('#contact-block').hide();
            }
            var result = isProcessed.apply(this, arguments);   
            return result; 
        };
        
        var next = target.next;
        target.next = function(){ 
            var result = next.apply(this, arguments);   
            var hashString = window.location.hash.replace('#', '');
            if(hashString == 'payment'){
                $('#contact-block').hide();
            }
            return result; 
        };
        return target
    };

});