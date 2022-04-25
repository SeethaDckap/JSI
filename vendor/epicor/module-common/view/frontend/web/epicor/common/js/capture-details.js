require([
    'jquery',
    'Magento_Ui/js/modal/confirm',
    'Magento_Ui/js/modal/alert',
    'mage/validation',
    'mage/storage',
    'mage/cookies',
    'Magento_Ui/js/lib/knockout/template/loader',
    'prototype',
], function ($j,confirmation,alertInfo, validation, storage) {

    $j(document).ready(function () {

        $j(document).on("click", '.confirm_button_no', function () {
          //  $('window-overlay').hide();
            if (path.indexOf('/checkout/') > -1 || path.indexOf('/multishipping/checkout/addresses') > -1) {
                window.location.replace($j('#ecc_cd_cart_url').val());
            }
        });
        window.rfqSubmit = function (event) {
            if (window.checkout.eccNonErpProductsActive == "1") {
                var rfq_skus;
                var skus = [];
                $j('tr.lines_row span.product_code_display').filter(':visible').each(function (index, element) {
                    var skuArray = $j(this).html().split("<br>");
                    skus.push(skuArray[0]);
                });
                if (skus.length > 0) {
                    rfq_skus = JSON.stringify(skus);
                }
				var form_key = $j.cookie('form_key');
                nonErpProductCheck(rfq_skus, form_key);
                return true;
            }
        }
        window.nonErpProductCheck = function (rfq_skus, form_key) {
            var skus;
            window.nonErpProductCheckRun = true;
            var source = 'rfq';
            var path = window.location.pathname;
            if (path.indexOf('/checkout/') > -1 || path.indexOf('/multishipping/checkout/addresses') > -1) {
                source = 'checkout';
            }
            var url = window.location.protocol + '//' + window.location.hostname + '/epicor/sales_order/nonErpProductCheck';
            url = url + (url.match(new RegExp('\\?')) ? '&isAjax=true' : '?isAjax=true');
            var ajaxRequest = $j.ajax({
                url: url,
                type: 'post',
                global: false,
                async: false,
                //          showLoader: true,
                data: {
                    'data': rfq_skus
                    , 'source': source
                    , form_key: form_key
                },
                success: function (data, status, xhr) {
                    var response = xhr.responseText.evalJSON();
                    if (response.nonErpItems == true) {
                        //only show popup if non erp items check is enabled and option is request
                        if (response.nonErpItemsEnabled == true) {
                            window.nonErpProductItemsEnabled = true;
                        }
                        if (response.nonErpItems == true && response.option == 'request') {
                            window.nonErpProductItems = true;
                            if (response.msgText) {
                                window.msgText = response.msgText;
                            } else {
                                window.msgText = "Checkout is not currently available. Would you like us to contact you about this order?";
                            }
                            if ($j('#rfq_save').length > 0) {
                                $j('#rfq_save').off('click');
                                $j('#rfq_update').removeAttr("onsubmit");
                                $j('#rfq_save').on('click', function () {
                                    moreInformationBox(msgText, 200, 150, 'confirm_html', 'RFQ');
                                })
                                window.rfqpages == true;
                            } else {
                                var info_box_id = 'confirm_html';
                                var info_box_width = 325;
                                var info_box_height = 120;
                                if (response.guest == true) {
                                    info_box_id = 'capture_customer_info';
                                    info_box_width = undefined;
                                    info_box_height = undefined;
                                }
                                window.moreInformationBox(msgText, info_box_width, info_box_height, info_box_id, 'Checkout');

                            }
                        }
                    } else {
                        window.nonErpProductItems = false;
                    }
                },
                error: function (xhr, status, errorThrown) {
                    $j('body').trigger('processStop');
                }
            });
        }

        window.moreInformationBox = function (msg, width, height, id, type) {
            $j('body').trigger('processStop');
            //    $j('body').loader('hide');
            //    $('window-overlay').hide();
            if ($j('.nonerpconfirmation').length) {
                $j('.nonerpconfirmation').remove();
            }
            var detailsRequired = false;
            if(id == 'capture_customer_info'){
                window.insertDetailsBlock();
                msg = msg + $j('#capture_customer_info').html();
                detailsRequired = true;

            }
            confirmation({
                content: msg,
                modalClass: 'confirm nonerpconfirmation',
                actions: {
                    confirm: function () {
                        captureDetails(detailsRequired);
                        $j('body').trigger('processStart');
                    },
                    cancel: function () {
                        checkoutRedirect();
                    }
                },
                opened: function () {
                    if(id == 'capture_customer_info') {
                        $j(".modal-footer").hide();
                    }
                }
            });
        }

        window.alertInfoShowMsg = function (titles, msg) {
            $j('body').trigger('processStop');
            //    $j('body').loader('hide');
            if ($j('.alertinfomsg').length) {
                $j('.alertinfomsg').remove();
            }
            alertInfo({
                title: titles,
                content: msg,
                modalClass: 'confirm alertinfomsg',
                actions: {
                     always: function () {
                             checkoutRedirect();
                     }
                }
            });
        }

        window.captureDetails = function (detailsRequired) {
            window.insertDetailsBlock();
            var data;
            if (detailsRequired) {
                if ($j('#modal-content-1 #capturedetails-form').valid()) {
                    data = JSON.stringify($j('#modal-content-1 #capturedetails-form').serializeArray(true));
                }else{
                    return;
                }
            }
            var productSkus;
            if (!data) {
                if ($j('#rfq_lines_table').length) {
                    var skus = [];
                    var rfq_skus;
                    $j('#rfq_lines_table .product_code_display').each(function () {
                        var parent = $j(this).closest('tr').prop('id');
                        var tr = $j("#" + parent);
                        var value = tr.find('.lines_line_value').val();
                        var qty = tr.find('.lines_quantity').val();
                        skus.push({
                            name: this.innerHTML, qty: qty, value: value
                        });
                    })
                    if (skus) {
                        rfq_skus = JSON.stringify(skus);
                    }
                    productSkus = rfq_skus;
                }
            }
            var registerAccount = $j('[name = "capturedetails[register]"]').val();
            var url = $j('#ecc_cd_capture_url').val();
            url = url + (url.match(new RegExp('\\?')) ? '&isAjax=true' : '?isAjax=true');
            var source = 'rfq';
            var path = window.location.pathname;
            if (path.indexOf('/checkout/') > -1 || path.indexOf('/multishipping/checkout/addresses') > -1) {
                source = 'checkout';
            }
            var form_key = $j.cookie('form_key');
            Epicor_Common.common.prototype.performAjax(
                url,
                'post',
                {
                    'data': data,
                    'productSkus': productSkus,
                    'registerAccount': registerAccount,
                    'source': source,
                    'form_key': form_key

                },
                function (request) {
                    var response = request.responseText;
                    if (response.evalJSON().success == true) {
                        var msgs = 'Thank you. Our representative will contact you shortly.';
                        var titles = 'Thank you';
                        alertInfoShowMsg(titles, msgs);
                    }
                })
         };
         window.checkoutRedirect = function () {
             var path = window.location.pathname;
             var source = '';
             if (path.indexOf('/checkout/') > -1 || path.indexOf('/multishipping/checkout/addresses') > -1) {
                 source = 'checkout';
             }
             //if on the cart redirect back to the cart
             if (source == 'checkout' && path.indexOf('/checkout/cart') == -1) {
                 redirectUrl = window.location.protocol + '//' + window.location.hostname + '/checkout/cart/index';
                 window.location.replace(redirectUrl);
             }

         };
        window.insertDetailsBlock = function() {
            if(typeof($j('#capture_customer_info').html()) == 'undefined'){
                var url = location.protocol + "//" + location.hostname +'/epicor/nonerpproduct/FormPopup';
                var form_key = $j.cookie('form_key');
                var ajaxRequest = $j.ajax({
                    url: url,
                    type: 'post',
                    async: false,
                    data: {form_key: form_key},
                    success: function (data, status, xhr) {
                        $j('body').append(data);
                    },
                    error: function (xhr, status, errorThrown) {
                        $j('body').append('<div id= "capture_customer_info" >ERROR: please reload page</div>');
                    }
                });
            }



        };
        if (window.checkout) {
            if (window.checkout.eccNonErpProductsActive != "0") {
                //only run nonerpproductcheck on page load when going to checkout
                var path = window.location.pathname;
                if (path.indexOf('/checkout/') > -1 && path.indexOf('/checkout/cart') == -1 || path.indexOf('/multishipping/checkout/addresses') > -1) {
                    window.nonErpProductCheck();
                }
                //window.rfqSubmit = rfqSubmit;
                //window.captureDetails = captureDetails;
                //window.moreInformationBox = moreInformationBox;
            }
        }else{
            // this is executed when pages that are not checkout or contain the minicart are accessed
            var path = window.location.pathname;
            if (path.indexOf('/multishipping/') > -1 && !window.eccNonErpProductsActive){
                var url = location.protocol + "//" + location.hostname +'/epicor/sales_order/nonErpProductCheckEnabled';
                var form_key = $j.mage.cookies.get('form_key');
                var ajaxRequest = $j.ajax({
                    url: url,
                    type: 'post',
                    async: false,
                    data: {form_key: form_key},
                    success: function (data, status, xhr) {
                        var response = data.evalJSON();
                        window.eccNonErpProductsActive = response.nonErpProductCheckEnabled;
                        if(window.eccNonErpProductsActive){
                            window.nonErpProductCheck();
                        }
                    }                    ,
                    error: function (xhr, status, errorThrown) {
                        $j('body').append('<div id= "capture_customer_info" >ERROR: please reload page</div>');
                    }
                });
            }
        }
    });
});
