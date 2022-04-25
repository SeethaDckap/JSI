define(
    [
        'jquery',
        'Epicor_BranchPickup/js/epicor/model/branch-common-utils',
        'Magento_Checkout/js/model/shipping-rate-registry',
        'Magento_Checkout/js/model/quote'
    ],
    function($, Branchutils,rateRegistry,quote) {
        'use strict';

        return function(target) {
            var navigateTo = target.navigateTo;
            target.navigateTo = function(code, scrollToElementId) {
                var result = navigateTo.apply(this, arguments);
                if (code == "shipping") {
                    var elem = $("#branch-pickup-container");
                    elem.show();
                } else {
                    $('#branch-pickup-container').hide();
                }
                //after method call
                return result;
            };

            var getActiveItemIndex = target.getActiveItemIndex;
            var steps = target.steps();
            target.getActiveItemIndex = function() {
                var activeIndex = 0;
                steps.sort(this.sortItems).some(function(element, index) {
                    if (element.isVisible()) {
                        if ((element.code != "shipping") && ($('#branchpickupshipping').length > 0)) {
                            Branchutils.hideBranchInformation();
                        }
                        if (element.code == "shipping") {
                            //var checkSelected = Branchutils.checkBranchPickupSelected();
                            if($('#branchpickupshipping').length > 0) {
                                var radioChecked = $('#branchpickupshipping').prop('checked');
                                $('#branch-pickup-container').show();
                                $('#shipping .step-title').hide();
                                if (radioChecked) {
                                    Branchutils.checkedBranchPickupActions();
                                } else {
                                    var address = quote.shippingAddress();
                                    if(address !=null) {
                                        rateRegistry.set(address.getKey(), null);
                                        rateRegistry.set(address.getCacheKey(), null);  
                                    }
                                    Branchutils.checkedNormalShippingActions();
                                }
                                $('#opc-shipping_method').find('input, select').prop('disabled', false);
                            }
                        }
                        activeIndex = index;
                        return true;
                    }
                    return false;
                });
                return activeIndex;
            };

            return target;
        };
    });