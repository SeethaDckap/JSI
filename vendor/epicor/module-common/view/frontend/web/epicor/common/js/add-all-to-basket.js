/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


document.observe('dom:loaded', function () {

    if ($('add_all_to_basket')) {
        $('add_all_to_basket').observe('click', function (event) {

            var products = 0;

            $$('.addall_qty').each(function (ele) {
                if (ele.value > 0) {
                    productForm = ele.up();
                    var product = ele.readAttribute('id').replace('qty_', '');
                    products++;
                    inputs = productForm.getInputs();
                    for (i = 0; i < inputs.length; i++) {
                        input = inputs[i].clone();
                        name = input.readAttribute('name');

                        if (name.indexOf('[') != -1) {
                            name = 'products[' + product + '][multiple][' + products + '][' + name.replace('[', '][')
                        } else {
                            name = 'products[' + product + '][multiple][' + products + '][' + name + ']';
                        }

                        input.writeAttribute('name', name);
                        input.writeAttribute('type', 'hidden');

                        $('add_all_to_basket').insert({
                            after: input
                        });
                    }
                }
            });

            if (products == 0) {
                event.stop();
            }
        });
    }
});