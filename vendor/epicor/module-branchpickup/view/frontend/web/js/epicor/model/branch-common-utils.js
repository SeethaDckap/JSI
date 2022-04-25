define(
   ['jquery', 'Magento_Checkout/js/model/full-screen-loader'],
   function($, fullScreenLoader) {
      return {
        hideBranchInformation: function() {
           $('#branch-pickup-container').hide(); 
           $('#shipping .step-title').hide();   
           $("#branch-pickup-container").nextUntil("#branchnextbutton").hide();
           $('#opc-branch-continue').hide();        
           $('#normalshippingcontainer').hide();     
        },
        checkBranchPickupSelected: function() {
            var shippingAddress = window.checkoutConfig.selectedBranch;
            if(!shippingAddress) {
               this.showShippingAddress();
            }
            return shippingAddress;
        },  
        showShippingAddress: function() {
            $("#normalshipping").prop('checked', true);
            $('#shipping').show();
            $('#shipping .step-title').hide();
            $('#opc-shipping_method').show();
            $('#branch-pickup-grid').hide();
            $("#branch-pickup-container").nextUntil("#branchnextbutton").hide();
            $('#opc-branch-continue').hide();            
            $('#shipping-branchpickup-method-buttons-container').hide();
            return true;
        },   
        showBranchPickupAddress: function() {
            $('#shipping').hide();
            $('#opc-shipping_method').hide();
            $('#shipping .step-title').hide();
            $('#branch-pickup-grid').show();
            $("#branch-pickup-container").nextUntil("#branchnextbutton").show();
            $('#opc-branch-continue').show();
            $('#shipping-branchpickup-method-buttons-container').show();
            return true;
        },
        checkedBranchPickupActions: function() {
            $("#branchpickupshipping").prop("checked", true);
            $('#opc-shipping_method').hide();
            $('#shipping').hide();
            $("#branch-pickup-container").nextUntil("#branchnextbutton").show();
            $('#opc-branch-continue').show();
            $('#branch-pickup-grid').show();
            $('#normalshippingcontainer').show();            
        },
        checkedNormalShippingActions: function() {
            $("#normalshipping").prop("checked", true);
            $("#branch-pickup-container").nextUntil("#branchnextbutton").hide();
            $('#opc-branch-continue').hide();
            $('#branch-pickup-grid').hide();
            $('#normalshippingcontainer').show();            
        },
        proceedtoNextStep: function() {
            $('#branch-pickup-container').hide();
            $("#branch-pickup-container").nextUntil("#branchnextbutton").hide();
            $('#opc-branch-continue').hide();
            $('#branch-pickup-grid').hide();
            $('#normalshippingcontainer').hide();             
        },
        removeBranchPickupSelection: function() {
            $('#branchpickup-addresses .control .branchpickup-shipping-address-items').has('.shipping-address-item').each(function() {
              if ($(this).children('.selected-branchpickup-item').length) {
                   $('.selected-branchpickup-item').removeClass("selected-item selected-branchpickup-item").addClass("not-selected-item not-selected-branchpickup-item");
              } 
            }); 
        }          
      };
   });