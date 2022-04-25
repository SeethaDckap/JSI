/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

function customerSelectAll()
{
    $$('#customersGrid_table input[type=checkbox]').each(function (elem) {
        if (elem.checked === false) {
            elem.click();
        }
    });
}


function customerUnselectAll()
{
    $$('#customersGrid_table input[type=checkbox]').each(function (elem) {
        if (elem.checked) {
            elem.click();
        }
    });
}
