/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */


if (typeof Epicor_Lists == 'undefined') {
    var Epicor_Lists = {};
}
Epicor_Lists.listProduct = Class.create();
Epicor_Lists.listProduct.prototype = {
    table: null,
    listId: null,
    nextId: 0,
    url: null,
    importUrl: null,
    csvDowloadUrl: null,
    pricingIsEditable: false,
    translations: {},
    products: {},
    currencies: {},
    initialize: function (parameters) {
        for (var index in parameters) {
            this[index] = parameters[index];
        }
    },
    pricing: function (row, event) {
        event.stopPropagation();

        var productId = row.up('tr').select('input[name=row_id]')[0].value;

        if (this.products.hasOwnProperty(productId)) {
            this.togglePricingGrid(row, productId, this.products[productId]);
            return false;
        }

        var data = {list: this.listId, product: productId};

        new Ajax.Request(this.url, {
            method: 'post',
            parameters: data,
            requestHeaders: {Accept: 'application/json'},
            onComplete: function (request) {
                this.ajaxRequest = false;
            }.bind(this),
            onSuccess: function (request) {
                var json = request.responseText.evalJSON(true);
                if (json.error) {
                    alert(json.error);
                } else {
                    this.products[productId] = json;
                    this.togglePricingGrid(row, productId, this.products[productId]);
                }
            }.bind(this),
            onFailure: function (request) {
                alert('Error');
            }.bind(this),
            onException: function (request, e) {
                alert(e);
            }.bind(this)
        });
    },
    togglePricingGrid: function (row, productId, pricing) {
        var gridId = 'pricing_grid_row_' + productId;
        if ($(gridId)) {
            return $(gridId).toggle();
        }

        var pricingGridRow = new Element('tr', {id: gridId});
        row.up('tr').insert({after: pricingGridRow});

        var tdElement = new Element('td', {colspan: '11'});
        pricingGridRow.insert(new Element('td'))
        pricingGridRow.insert(tdElement);

        if (this.pricingIsEditable) {
            var actionsDiv = new Element('div', {class: 'filter-actions a-right', style: "padding: 6px"});
            tdElement.insert(actionsDiv);
            actionsDiv.insert('<button title="' + this.translate('Add') + '" type="button" class="scalable add" onclick="listProduct.addPricingRow(\'' + productId + '\')"><span>' + this.translate('Add') + '</span></button>');
            actionsDiv.insert('<button title="' + this.translate('Duplicate') + '" type="button" class="scalable add" onclick="listProduct.duplicatePricingRows(\'' + productId + '\')"><span>' + this.translate('Duplicate') + '</span></button>');
            actionsDiv.insert('<button title="' + this.translate('Delete') + '" type="button" class="scalable delete" onclick="listProduct.deletePricingRows(\'' + productId + '\')"><span>' + this.translate('Delete') + '</span></button>');
        }

        var pricingGridTable = new Element('table', {id: 'pricing_grid_row_' + productId, cellspacing: '0', class: 'data'});
        tdElement.insert(pricingGridTable);


        var thead = new Element('thead');
        var headings = new Element('tr', {class: 'headings'});

        headings.insert('<th class="no-link"><span class="nobr">' + this.translate('Currency') + '</span></th>');
        headings.insert('<th class="no-link"><span class="nobr">' + this.translate('Price') + '</span></th>');
        headings.insert('<th class="no-link"><span class="nobr">' + this.translate('Breaks') + '</span></th>');
        headings.insert('<th class="no-link"><span class="nobr">' + this.translate('Value Breaks') + '</span></th>');

        if (this.pricingIsEditable) {
            headings.insert('<th class="no-link"><span class="nobr">' + this.translate('Select') + '</span></th>');
        }

        thead.insert(headings);

        pricingGridTable.insert(thead);

        var tbody = new Element('tbody');

        pricingGridTable.insert(tbody);

        if (Object.keys(pricing).length > 0) {
            for (var index in pricing) {
                if (pricing.hasOwnProperty(index)) {
                    this.addPricingRow(productId, pricing[index]);
                }
            }
        } else {
            tbody.insert('<tr class="even"><td class="empty-text a-center" colspan="6">' + this.translate('No records found.') + '</td></tr>');
        }
    },
    addPricingRow: function (productId, data) {
        var tbody = $('pricing_grid_row_' + productId).select('tbody')[0];

        if (tbody.select('td.empty-text').length > 0) {
            tbody.select('td.empty-text')[0].up('tr').remove();
        }

        if (!data) {
            data = {id: this.getNextId(), price: '', price_breaks: {}, value_breaks: {}, currency: ''};
            this.products[productId][data.id] = data;
        }

        var row = new Element('tr', {id: 'pricing_row_' + data.id});//, {class: rowClass});

        tbody.insert(row);

        var tdCurrency = new Element('td');

        var onBlur = 'listProduct.indexPricingRow(\'' + productId + '\',\'' + data.id + '\')';

        var priceHtml = data.price;
        if (this.pricingIsEditable) {
            priceHtml = '<input name="product_pricing_price" value="' + data.price + '" class="required-entry validate-zero-or-greater input-text" type="text" onBlur="' + onBlur + '">';
        }
        row.insert(tdCurrency);
        row.insert('<td>' + priceHtml + '</td>');
        this.createBreakCell(row, data.id, data.price_breaks, 'price_breaks', productId);
        this.createBreakCell(row, data.id, data.value_breaks, 'value_breaks', productId);
        if (this.pricingIsEditable) {
            row.insert('<td><input name="product[' + productId + '][' + data.id + '][delete]" type="checkbox" value="' + data.id + '" class="checkbox" /></td>');
        }


        if (this.pricingIsEditable) {
            var select = new Element('select', {name: 'product_pricing_currency', class: 'required-entry', onBlur: onBlur});
            tdCurrency.insert(select);
            select.insert('<option value=""></option>');
            for (var value in this.currencies) {
                var option = new Element('option', {value: value});
                option.insert(this.currencies[value]);
                select.insert(option);
            }
            select.value = data.currency;
        } else {
            var currencyText = data.currency;
            for (var value in this.currencies) {
                if (data.currency == value) {
                    currencyText = this.currencies[value];
                }
            }
            tdCurrency.insert(currencyText);
        }

        this.indexPricingRow(productId, data.id);
    },
    createBreakCell: function (row, dataId, breaks, type, productId) {
        var tdElement = new Element('td');
        row.insert(tdElement);

        var table = new Element('table', {id: 'pricing_' + type + '_grid_row_' + dataId, cellspacing: '0', class: 'data'});
        tdElement.insert(table);

        var thead = new Element('thead');
        var headings = new Element('tr', {class: 'headings'});
        headings.insert('<th class="no-link"><span class="nobr">' + this.translate(type == 'price_breaks' ? 'Qty' : 'Value') + '</span></th>');
        headings.insert('<th class="no-link"><span class="nobr">' + this.translate('Price') + '</span></th>');
        headings.insert('<th class="no-link"><span class="nobr">' + this.translate('Description') + '</span></th>');
        if (this.pricingIsEditable) {
            headings.insert('<th class="no-link"><span class="nobr"></span></th>');
        }
        thead.insert(headings);
        table.insert(thead);

        var tbody = new Element('tbody');
        table.insert(tbody);

        var hasBreaks = false;
        for (var index in breaks) {
            if (breaks.hasOwnProperty(index)) {
                hasBreaks = true;
                this.addBreak(type, dataId, productId, breaks[index]);
            }
        }

        if (this.pricingIsEditable) {
            var tfoot = new Element('tfoot');
            table.insert(tfoot);
            tfoot.insert('<tr>\n\
        <td colspan="4" class="a-right">\n\
            <button title="' + this.translate('Add') + '" type="button" class="scalable add" onclick="return listProduct.addBreak(\'' + type + '\',\'' + dataId + '\',\'' + productId + '\')">\n\
                <span>' + this.translate('Add') + '</span>\n\
            </button>\n\
        </td>\n\
    </tr>');
        } else if (!hasBreaks) {
            table.insert('<tr class="even">\n\
        <td colspan="3" class="a-center">\n\
            ' + this.translate('No records found.') + '\n\
        </td>\n\
    </tr>');
        }

        return tdElement;
    },
    deletePricingRows: function (productId) {
        if (this.pricingIsEditable) {
            $('pricing_grid_row_' + productId).select('input:checked').each(function (e) {
                var dataId = e.value;
                e.up('tr').remove();
                listProduct.indexPricingRow(productId, dataId);
            });
            var tbody = $('pricing_grid_row_' + productId).select('tbody')[0];

            if (tbody.select('tr').length == 0) {
                tbody.insert('<tr class="even"><td class="empty-text a-center" colspan="6">' + this.translate('No records found.') + '</td></tr>');
            }
        }
    },
    duplicatePricingRows: function (productId) {
        if (this.pricingIsEditable) {
            var selected = $('pricing_grid_row_' + productId).select('input:checked');
            for (var index in selected) {
                if (selected.hasOwnProperty(index)) {
                    var data = JSON.parse(JSON.stringify(this.products[productId][selected[index].value]));
                    data.id = this.getNextId();
                    this.products[productId][data.id] = data;
                    this.addPricingRow(productId, data);
                }
            }
            this.indexPricingRow(productId, data.id);
        }
    },
    addBreak: function (type, dataId, productId, data) {
        var table = $('pricing_' + type + '_grid_row_' + dataId);
        var tbody = table.select('tbody')[0];
        var tr = new Element('tr');

        if (!data) {
            data = {qty: '', value: '', price: '', description: ''};
        } else {
            data = {
                qty: data.qty == null ? '' : data.qty,
                value: data.value == null ? '' : data.value,
                price: data.price == null ? '' : data.price,
                description: data.description == null ? '' : data.description};
        }

        var onBlur = 'listProduct.indexPricingRow(\'' + productId + '\',\'' + dataId + '\')';

        tbody.insert(tr)
        if (this.pricingIsEditable) {
            if (type == 'price_breaks') {
                tr.insert('<td><input type="text" name="break_qty" value="' + data.qty + '" onBlur="' + onBlur + '" /></td>');
            } else {
                tr.insert('<td><input type="text" name="break_value" value="' + data.value + '" onBlur="' + onBlur + '" /></td>');
            }
            tr.insert('<td><input type="text" name="break_price" value="' + data.price + '" onBlur="' + onBlur + '" /></td>');
            tr.insert('<td><input type="text" name="break_description" value="' + data.description + '" onBlur="' + onBlur + '" /></td>');
            tr.insert('<td class="no-link"><span class="nobr">\n\
                    <button title="Delete" type="button" class="scalable delete icon-btn delete-product-option" onclick="return listProduct.removeBreak(this, \'' + productId + '\',\'' + dataId + '\');">\n\
                <span>' + this.translate('Delete') + '</span></button></span></td>');
        } else {
            tr.insert('<td>' + (type == 'price_breaks' ? data.qty : data.value) + '</td>');
            tr.insert('<td>' + data.price + '</td>');
            tr.insert('<td>' + data.description + '</td>');
        }

    },
    removeBreak: function (el, productId, rowId) {
        if (this.pricingIsEditable) {
            el.up('tr').remove();
            this.indexPricingRow(productId, rowId)
        }
    },
    indexPricingRow: function (productId, rowId) {
        if (this.pricingIsEditable) {
            var row = $('pricing_row_' + rowId);
            if (row) {
                var priceBreaks = [];
                var valueBreaks = [];

                $('pricing_price_breaks_grid_row_' + rowId).select('tbody')[0].select('tr').each(function (e) {
                    var breakRow = {
                        qty: e.select('input[name=break_qty]')[0].value,
                        price: e.select('input[name=break_price]')[0].value,
                        description: e.select('input[name=break_description]')[0].value
                    };
                    priceBreaks.push(breakRow);
                });

                $('pricing_value_breaks_grid_row_' + rowId).select('tbody')[0].select('tr').each(function (e) {
                    var breakRow = {
                        value: e.select('input[name=break_value]')[0].value,
                        price: e.select('input[name=break_price]')[0].value,
                        description: e.select('input[name=break_description]')[0].value
                    };
                    valueBreaks.push(breakRow);
                });

                var data = {
                    id: rowId,
                    currency: row.select('select[name=product_pricing_currency]')[0].value,
                    price: row.select('input[name=product_pricing_price]')[0].value,
                    price_breaks: priceBreaks,
                    value_breaks: valueBreaks
                };

                this.products[productId][rowId] = data;
            } else {
                delete this.products[productId][rowId];
            }

            $(this.jsonPricing).value = JSON.stringify(this.products);
        }

    },
    getNextId: function () {
        var nextId = 'new_' + this.nextId;

        this.nextId++;

        return nextId;
    },
    insertMessages: function (messages) {
        if ($('messages').select('ul').length == 0) {
            $('messages').insert('<ul class="messages"></ul>');
        } else {
            $('messages').select('li').forEach(function (element) {
                element.remove()
            });
        }
        for (var type in messages) {
            var msgType = (type == 'errors' ? 'error-msg' : 'warning-msg');
            if (messages.hasOwnProperty(type)) {
                for (var index in messages[type]) {
                    if (messages[type].hasOwnProperty(index)) {
                        $('messages').select('ul')[0].insert('<li class="' + msgType + '"><span>' + messages[type][index] + '</span></li>');
                    }
                }
            }
        }
    },
    translate: function (toTranslate) {
        return this.translations.hasOwnProperty(toTranslate) ? this.translations[toTranslate] : toTranslate;
    },
    import: function () {
        if ($('import')) {
            var files = $('import').files;
            var data = new FormData();

            if (files.length > 0) {
                data.append('import', files[0], files[0].name);
                data.append('form_key', FORM_KEY);
                $('loading-mask').show();
                jQuery.ajax({
                    url: this.importUrl + '?isAjax=true', data: data,
                    type: 'post', dataType: 'json',
                    async: false, contentType: false,
                    cache: false, processData: false,
                    success: function (response) {
                        $('loading-mask').hide();
                        listProduct.reloadGrid(response.products);
                        listProduct.insertMessages(response.errors);
                    },
                    error: function () {
                        alert('Error');
                        $('loading-mask').hide();
                    }
                });
                $('import').value = '';
            } else {
                alert(this.translate('Please choose a file.'));
            }
        }
        return false;
    },
    reloadGrid: function (products) {
        var gridObject = eval(this.table + 'JsObject');
        if (products) {
            gridObject.reloadParams['products[]'] = products;
            $$('input[name=links[products]]')[0].value = this.serializeProducts(products);
        }
        gridObject.reload();
    },
    serializeProducts: function (products) {
        var array = [];
        for (var i = 0; i < products.length; i++) {
            var id = products[i];
            array.push(encodeURIComponent(id) + "=" + encodeURIComponent(encode_base64('id=' + id)));
        }
        return array.join('&');
    },
    dowloadCsv: function () {
        return window.location = this.csvDowloadUrl;
    }
};

var listProduct;
function initListProduct(parameters) {
    listProduct = new Epicor_Lists.listProduct;
    listProduct.initialize(parameters);
}

function productSelectAll()
{
    $$('#productsGrid_table input[type=checkbox]').each(function (elem) {
        if (elem.checked == false) {
            elem.simulate('click');
        }
    });

}

function productUnselectAll()
{
    $$('#productsGrid_table input[type=checkbox]').each(function (elem) {
        if (elem.checked) {
            elem.simulate('click');
        }
    });
}
