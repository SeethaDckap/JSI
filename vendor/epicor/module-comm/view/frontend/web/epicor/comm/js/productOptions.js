/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

Validation.add('validate-csns', 'Validating Serial. Please Wait...', function (v, elm) {
    if (lastSerialEntered != v) {
        elementId = elm.readAttribute('id');
        url = $(elementId + '_csnsurl').value;
        sku = $(elementId + '_sku').value;
        mode = $(elementId + '_mode').value;

        var postData = {'sku': sku, 'serial': v, 'mode': mode}
        xhr = performAjax(url, 'post', postData, function (data) {
            var response = data.responseText;
            if (response == 'VALID') {
                elm.addClassName('validate-serial-success');
            } else {
                elm.addClassName('validate-serial-failed');
            }

            productAddToCartForm.submit();
        });
        lastSerialEntered = v;
    } else if (elm.hasClassName('validate-serial-failed') || elm.hasClassName('validate-serial-success')) {
        return true;
    }

    return false;
});

Validation.add('validate-serial-failed', 'Invalid Serial', function (v, elm) {
    return false;
});

lastSerialEntered = '';

Event.live('.super-attribute-select', 'change', function () {
    loadConfigurableProductsPrice();
});

document.observe("dom:loaded", function () {
    setTimeout(loadConfigurableProductsPrice, 1000);
});

function loadConfigurableProductsPrice() {
    if ($('product-stock-wrapper-url')) {
        var url = $('product-stock-wrapper-url').value;
        var completed = true;
        $$('.super-attribute-select.required-entry').each(function (el) {
            if (el.value == '') {
                completed = false;
                return false;
            }
        });

        if (completed) {
            performAjax(url, 'post', $('product_addtocart_form').serialize(true), function (data) {
                var json = data.responseText.evalJSON();
                var error;

                if (json.html) {
                    if ($('product-stock-wapper')) {
                        $('product-stock-wapper').update(json.html);
                    }
                } else {
                    if (json.error) {
                        error = json.error;
                    } else {
                        error = Translator.translate('Error occured in Ajax Call');
                    }
                    if ($('product-stock-wapper')) {
                        $('product-stock-wapper').update();
                    }
                    alert(error);
                }
                if ($('loading-mask')) {
                    $('loading-mask').hide();
                }
            });
        } else {
            if ($('product-stock-wapper')) {
                $('product-stock-wapper').update();
            }
        }
    }
}