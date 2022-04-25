define([
    'uiComponent',
    'underscore',
    'Magento_Customer/js/customer-data',
    'ko',
    "Magento_Checkout/js/model/full-screen-loader",
    'jquery'
], function (Component, _, customer, ko, fullScreenLoader, $) {
    return Component.extend({
        defaults: {
            template: 'Epicor_Comm/product/type/configurable/update-options'
        },
        customerData: {},
        itemIdSelector: '#product_addtocart_form [name="item"]',
        selectedOptionSelector: 'form#product_addtocart_form input[name="selected_configurable_option"]',
        currentItem: {},
        currentProductOptions: {},
        spConfig: {},
        configIndex: {},
        selectedConfigurableOption: '',
        initialize: function (config) {
            this.configIndex = config.spconfig.index;
            this.spconfig = config.spconfig;
            this._super();
            this.setCustomerData();
            this.setCurrentItem();
            this.setCurrentProductOptions();
            this.setSelectedConfigurableOption();
        },
        setCustomerData: function() {
            let customerData = customer.get('cart');
            this.customerData = customerData();
        },
        setCurrentProductOptions: function() {
            _.each(this.getOptionsData(), function(option, index){
                let id = option.option_id;
                this.currentProductOptions[id] = option.option_value;
            },this);
        },
        setCurrentItem: function() {
            let itemId = $(this.itemIdSelector).val();
            let items = this.customerData.items;
            _.each(items, function(itemData, index){
                if(itemData.item_id === itemId){
                    this.currentItem = itemData;
                }
            },this)
        },
        /**
         * Sets the current item product id
         */
        setSelectedConfigurableOption: function() {
            _.each(this.configIndex, function(configOption, index){
                if(_.isMatch(configOption, this.currentProductOptions)){
                    this.selectedConfigurableOption =  index;
                }
            },this);
        },
        /**
         * Gets the current product options for the cart item ie attribute and
         * option values used to set labels in knockout template
         * @returns {*}
         */
        getOptionsData: function(){
            let options = this.currentItem.options;
            if(options.length > 0 ){
                return options;
            }
        },
        /**
         * called in knockout template using (afterRender) to add stock update
         */
        renderStockData: function(){
            $(this.selectedOptionSelector).val(this.selectedConfigurableOption);
            $.ajax({
                context: this,
                showLoader: true,
                data: $('#product_addtocart_form').serialize(true),
                url:    $('#product-stock-wrapper-url').val(),
                type: "POST",
                dataType: 'json'
            }).done(function(jsons) {
                fullScreenLoader.stopLoader();
                if(jsons.html) {
                    if ($('#product-stock-wapper') && jsons.location_status === 0) {
                        $('#product-stock-wapper').html(jsons.html);
                    }
                } else {
                    if (jsons.error) {
                        console.log(jsons.error);
                    }
                }
            });
        }
    });
});