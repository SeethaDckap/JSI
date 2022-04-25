var config = {
    map: {
        '*': {
            "Magento_Checkout/js/model/shipping-service" : "Epicor_BranchPickup/js/epicor/mixin/shipping-service",
            "branchselect": 'Epicor_BranchPickup/js/main',
            "Vertex_AddressValidation/js/shipping-invalidate-mixin":"Epicor_BranchPickup/js/shipping-invalidate-mixin"
        }
    },    
    config: {
        mixins: {
            'Magento_Checkout/js/model/step-navigator': {
                'Epicor_BranchPickup/js/epicor/mixin/shipping-navigate-mixin': true
            }         
        }
    }
};