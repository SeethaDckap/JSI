require([
    'Magento_Customer/js/customer-data'
    ], function (customerData) {
    return function () {
        var sections = ['cart'];
        customerData.invalidate(sections);
        customerData.reload(sections, true);
    }
});
