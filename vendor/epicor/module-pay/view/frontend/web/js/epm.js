require([
    'jquery'
], function (jQuery) {
    jQuery(document).ready(function(){
        jQuery(document).on('click', '#shipping-method-buttons-container [data-role="opc-continue"]', function () {
            if(jQuery('.payment-method._active input[name="epmpo"]').length && jQuery('[name="ecc_customer_order_ref"]').val() != '') {
                jQuery('#epmpo').prop('disabled', true);
                jQuery('#epmpo').val(jQuery('[name="ecc_customer_order_ref"]').val());
            } else {
                jQuery('#epmpo').prop('disabled', false);
                jQuery('#epmpo').val(jQuery('[name="ecc_customer_order_ref"]').val());
            }
        });
    });
});
