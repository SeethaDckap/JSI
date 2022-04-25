require([
        'jquery',
        'mage/url',
        'mage/storage',
        'mage/translate',
        'mage/template',
        'domReady',
        'Magento_Ui/js/modal/modal',
        'Magento_Ui/js/modal/confirm',
        'Magento_Ui/js/modal/alert',
        'Magento_Customer/js/customer-data',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/mage',
        'jquery/ui'
    ],
    function($, url, storage, $t, alerts, domReady, modal, confirmation, alerts, customerData, fullScreenLoader) {
        window.arPaymentsJs = {
            //Handling Clicks when the clicked on dispute
            disputeClick: function(checkboxid) {
                var id =$('[id="dispute_invoices_' + checkboxid + '"]');
                var checkSelected = $(id).is(":checked");
                var checkbox = $('[id="dispute_invoices_comments_' + checkboxid + '"]');
                var sign = $('[id="' + checkboxid + '"]');
                if (checkSelected) {
                    var plusminus = $(sign).html();
                    if (plusminus == "+") {
                        $(checkbox).show();
                        $(sign).html() == '+' ? $(sign).html('-') : $(sign).html('+');
                    } else if (plusminus == "-") {
                        $(checkbox).show();
                    } else {
                        $(checkbox).hide();
                        $(sign).html() == '-' ? $(sign).html('+') : $(sign).html('-');
                    }
                    $('[id="dispute_invoice_serialize_' + checkboxid + '"]').val('checked');
                } else {
                    if ($(checkbox)) {
                        var plusminus = $(sign).html();
                        $(checkbox).hide();
                        $(sign).html() == '+' ? $(sign).html('+') : $(sign).html('+');
                    }
                    $('[id="dispute_invoice_serialize_' + checkboxid + '"]').val('unchecked');
                }
            },
            //Handling Clicks when the clicked on dispute plus minus
            disputePlusClick: function(checkboxid) {
                var id = $('[id="dispute_invoices_' + checkboxid + '"]');
                var checkSelected = $(id).is(":checked");
                var checkbox = $('[id="dispute_invoices_comments_' + checkboxid + '"]');
                var sign = $('[id="' + checkboxid + '"]');
                if (checkbox) {
                    var plusminus = $(sign).html();
                    if (plusminus == "+") {
                        $(checkbox).show();
                    } else if (plusminus == "-") {
                        $(checkbox).hide();
                    }
                    $(sign).html() == '-' ? $(sign).html('+') : $(sign).html('-');
                }
            },
            //When the user clicks on the APPLY Payment
            //Also we are calling this while calculating the total amount apply
            calculateArSum: function(inputElm, refererVals) {
                var self = this;
                var addressPopup = $("#arpayment_address_value").val();
                if ((typeof(refererVals) != "undefined")) {
                    var referer = refererVals;
                } else {
                    var referer = false;
                }
                if ((addressPopup != "1") && (!referer)) {
                    var referer = "applypayment";
                    self.addresspopup(referer);
                    return false;
                }
                var getSerialized = self.getSerializeValues();
                var items = self.getArItems();
                var payAmt = parseFloat($('#allocate_amount').val());
                var checkIfNumeric = $('#allocate_amount').val();
                if ((!checkIfNumeric) && (typeof(inputElm) == "undefined")) {
                    arPaymentsJs.arpaymentsalert('Please Enter a valid Amount');
                    $('#allocate_amount').val($('#allocate_amount_original').val());
                    return false;
                }
                var checkAnythingSelected = true;
                if (typeof(inputElm) != "undefined") {
                    checkAnythingSelected = self.checkboxSelected();
                }
                ///check anything was selected or not
                if (checkAnythingSelected) {
                    var newItems = [];
                    for (var i = 0; i < items.length; i++) {
                        newItems[i] = 0;
                    }
                    var i = 0;
                    while (payAmt != 0 && i < items.length) {
                        var temp = payAmt - items[i];
                        if (temp > 0) {
                            newItems[i] = items[i];
                            payAmt = parseFloat(temp).toFixed(2);
                        } else if (temp <= 0) {
                            newItems[i] = parseFloat(payAmt).toFixed(2);
                            payAmt = 0;
                        }
                        i++;
                        //sum = itemSum(items);
                    }
                    var pendingAmount = payAmt;
                    self.setArItems(newItems, pendingAmount);
                } else {
                    self.calculateArCheckSum();
                }

            },
            //Get the serialized values from the input box
            getSerializeValues: function() {
                var self = this;
                var query = {},
                    selfJson, selfJsonLength;
                var getSerializeJson = $("input[name='links[invoices]']").val();
                if (!getSerializeJson) {
                    selfJson = {};
                    selfJsonLength = 0;
                } else {
                    selfJson = self.deparam(getSerializeJson);
                    selfJsonLength = self.objSize(selfJson);
                }
                query['json'] = selfJson;
                query['length'] = selfJsonLength;
                return query;
            },
            //When we are doing a Search/Reset - we are calculating the result in the background
            calculateOnSearchReset: function() {
                var self = this;
                if ($('.col-select_arpayments input:checkbox:checked').length > 0) {
                    var items = self.getArItemsSelectedCheckbox();
                } else {
                    var items = self.getArItems();
                }
                var payAmt = parseFloat($('#allocate_amount').val());
                var checkIfNumeric = $('#allocate_amount').val();
                if ((!checkIfNumeric)) {
                    $('#allocate_amount').val($('#allocate_amount_original').val());
                }
                var newItems = [];
                for (var i = 0; i < items.length; i++) {
                    newItems[i] = 0;
                }
                var i = 0;
                while (payAmt != 0 && i < items.length) {
                    var temp = payAmt - items[i];
                    if (temp > 0) {
                        newItems[i] = items[i];
                        payAmt = temp;
                    } else if (temp <= 0) {
                        newItems[i] = payAmt.toFixed(2);
                        payAmt = 0;
                    }
                    i++;
                    //sum = itemSum(items);
                }
                var pendingAmount = payAmt.toFixed(2);
                self.setArItems(newItems, pendingAmount);
            },
            //Getting the items for the current page
            getArItems: function() {
                var items = new Array();
                var i = 0;
                $('#customer_arpayments_invoices_list_table tbody tr:not(.disable_check_arpayment)').each(function(row) {
                    items[i] = $(this).find(".aroutstanding_value").val();
                    i++;
                });
                return items;
            },
            //Getting the selected items in the current page
            getArItemsSelectedCheckbox: function() {
                var items = new Array();
                var i = 0;
                $('#customer_arpayments_invoices_list_table tbody tr:not(.disable_check_arpayment)').each(function(row) {
                    if ($(this).find(".col-select_arpayments input:checkbox:checked").length > 0) {
                        items[i] = $(this).find(".aroutstanding_value").val();
                    } else {
                        items[i] = 0;
                    }
                    i++;
                });
                return items;
            },
            checkboxSelected: function() {
                if ($('.col-select_arpayments input:checkbox:checked').length > 0) {
                    return true;
                } else {
                    return false;
                }
            },
            //Setting the amount to the Invoices
            setArItems: function(items, payAmt) {
                var originalAllocated = $('#allocate_amount').val();
                var self = this;
                if (!isNaN(payAmt)) {
                    $('#arpayment_left .amount_left_ar').html(payAmt);
                    $('#allocate_amount_original').val(originalAllocated);
                    $('#amount_left_ar').val(payAmt);
                } else {
                    $('#arpayment_left .amount_left_ar').html("0.00");
                    $('#allocate_amount_original').val("0.00");
                    $('#amount_left_ar').val("0.00");
                }
                var i = 0;
                $('#customer_arpayments_invoices_list_table tbody tr:not(.disable_check_arpayment)').each(function(row) {
                    var checked = $(this).find(".col-select_arpayments input:checkbox").is(":checked");
                    if (checked) {
                        $(this).find(".col-select_arpayments input:checkbox").trigger('click');
                    }
                    $(this).find(".col-arpayment_amount input.arpayment_amount").val(items[i]);
                    if (items[i] > 0) {
                        var outstanding = $(this).find(".col-arpayment_amount input.aroutstanding_value").val();
                        var checkedAgain = $(this).find(".col-select_arpayments input:checkbox").is(":checked");
                        if (!checkedAgain) {
                            $(this).find(".col-select_arpayments input:checkbox").trigger('click');
                        }
                        var outstandingAmnt = outstanding - items[i];
                        if (!outstandingAmnt) {
                            outstandingAmnt = "0.00";
                        }

                        $(this).find(".col-arpayment_amount span.balance_ar").html(outstandingAmnt);
                        $(this).find(".col-arpayment_amount input.ar_remaining_value").val(outstandingAmnt);
                    } else {
                        var outstandingAmnt = $(this).find(".col-arpayment_amount input.aroutstanding_value").val();
                        $(this).find(".col-arpayment_amount span.balance_ar").html(outstandingAmnt);
                        $(this).find(".col-arpayment_amount input.ar_remaining_value").val(outstandingAmnt);  
                        if ($(this).find(".col-dispute_invoice input:checkbox").length > 0) {
                             var checkDispute = $(this).find(".col-dispute_invoice input:checkbox").is(":checked");
                             if(checkDispute) {
                                $(this).find(".col-dispute_invoice input:checkbox").trigger('click'); 
                                $(this).find("input.dispute_invoice_serialize").val('unchecked');
                             }
                                $(this).find(".dispute_invoices_comments").val('');
                                $(this).find("input.dispute_invoices_serializecomments").val('');
                         }
                    }
                    i++;
                });
                self.calculateArAmountAllocate();
            },
            //Doing a check to calculate the amount left, allocated amount
            calculateArCheckSum: function() {
                var payAmt = parseFloat($('#allocate_amount').val());
                if ($('.col-select_arpayments input:checkbox:checked').length > 0) {
                    var i = 0;
                    $('.col-select_arpayments input:checkbox:checked').each(function(e) {
                        var temp = payAmt - $(this).find(".col-arpayment_amount input.arpayment_amount").val();
                        if (temp > 0) {
                            payAmt = temp;
                        } else if (temp <= 0) {
                            payAmt = 0;
                        }
                    });
                }
                if (!isNaN(payAmt)) {
                    var pendingAmount = payAmt.toFixed(2);
                    $('#arpayment_left .amount_left_ar').html(pendingAmount);
                    $('#amount_left_ar').val(pendingAmount);
                    $('#allocate_amount_original').val(parseFloat($('#allocate_amount').val()));
                }
            },
            //Calculating the amount for all the invoices that was selected
            //We are reading the values from the hidden text box and calculating the amount
            calculateArAmount: function(el, b) {
                var parentRows = $(el).parents('tr').first();
                var inptTxtVal = $(parentRows).find(".col-arpayment_amount input.arpayment_amount ").val();
                var outstanding = $(parentRows).find(".col-arpayment_amount input.aroutstanding_value").val();
                var checked = $(parentRows).find(".col-select_arpayments input:checkbox").is(":checked");
                if (inptTxtVal.length == 0 && b.keyCode != '8') {
                    el.value = 0;
                    $(parentRows).find(".col-arpayment_amount span.balance_ar").html(outstanding);
                    $(parentRows).find(".col-arpayment_amount input.ar_remaining_value").val(outstanding);
                    if (checked) {
                        $(parentRows).find(".col-select_arpayments input:checkbox").trigger('click');
                    }
                    return;
                }
                var totalValue = 0;
                var self = this;
                var getSerialized = self.getSerializeValues();
                var amountVals = 0;
                if (getSerialized.length > 0) {
                    for (var mainkey in getSerialized.json) {
                        if (getSerialized.json.hasOwnProperty(mainkey)) {
                            $.each(getSerialized.json[mainkey], function(key, value) {
                                if (key == "arpayment_amount[]") {
                                    amountVals = value;
                                }
                            });
                            totalValue += parseFloat(amountVals);
                        }
                    };
                }
                self.incDecAmount(totalValue.toFixed(2));
                self.canUpdateAllocatedByInvoice();
                self.getSerializeData();
            },
            //Calculating how much amount was left
            incDecAmount: function(inptTxtVal) {
                var allocate_amount = $('#allocate_amount').val();
                var amountLeft = $('#amount_left_ar').val();
                if (allocate_amount && amountLeft) {
                    var caclculateAmount = allocate_amount - inptTxtVal;
                    if (caclculateAmount >= 0) {
                        $('.amount_left_ar').html(caclculateAmount.toFixed(2));
                        $('#amount_left_ar').val(caclculateAmount.toFixed(2));
                    } else {
                        $('.amount_left_ar').html("0.00");
                        $('#amount_left_ar').val(0.00);
                    }
                }
            },
            //Select only allocated invoices
            canUpdateAllocatedByInvoice: function() {
                var allocateAmount = $('#allocate_amount').val();
                var canUpdateByInvoice = $('#canUpdateByInvoice').val();
                if ((allocateAmount == '' || parseFloat(allocateAmount) == '0') && (canUpdateByInvoice == 0 || canUpdateByInvoice =="")) {
                    $('#canUpdateByInvoice').val(1);
                    canUpdateByInvoice = 1;
                }
                if (canUpdateByInvoice == 1) {
                    var self = this;
                    var getSerialized = self.getSerializeValues();
                    var amountVals = 0;
                    var invoiceTotal = 0;
                    if (getSerialized.length > 0) {
                        for (var mainkey in getSerialized.json) {
                            if (getSerialized.json.hasOwnProperty(mainkey)) {
                                $.each(getSerialized.json[mainkey], function(key, value) {
                                    if (key == "arpayment_amount[]") {
                                        amountVals = value;
                                        if (amountVals == 0) {
                                            var checked = $('[id="id_' + mainkey + '"]').is(":checked");
                                            if (checked) {
                                               $('[id="id_' + mainkey + '"]').trigger('click');
                                            }
                                        }
                                    }
                                });
                                invoiceTotal += parseFloat(amountVals);
                            }
                        };
                    }
                    $('#allocate_amount').val(invoiceTotal.toFixed(2));
                    $('#allocate_amount_original').val(invoiceTotal.toFixed(2));
                }
            },
            checkOnFocus: function(element) {
                if ($(element).val() == "0") {
                    $(element).val("");
                }
            },
            //Whenever a amount was entered in Invoice Row
            //Then we are doinga calculation
            checkArRowTotal: function(el, b) {
                var parentRows = $(el).parents('tr').first();
                var outstanding = $(parentRows).find(".col-arpayment_amount input.aroutstanding_value").val();
                var inptTxtVal = $(parentRows).find(".col-arpayment_amount input.arpayment_amount ").val();
                var checked = $(parentRows).find(".col-select_arpayments input:checkbox").is(":checked");
                if (inptTxtVal.length == 0 && b.keyCode != '8') {
                    el.value = 0;
                    $(parentRows).find(".col-arpayment_amount span.balance_ar").html(outstanding);
                    $(parentRows).find(".col-arpayment_amount input.ar_remaining_value").val(outstanding);
                    if (checked) {
                        $(parentRows).find(".col-select_arpayments input:checkbox").trigger('click');
                    }
                    return;
                }
                var self = this;
                self.applyArpaymentsBalance();
                var currentHiddentAmount = $(parentRows).find(".col-arpayment_amount input.aroutstanding_value").val();
                if (Number(inptTxtVal) > Number(currentHiddentAmount)) {
                    b.preventDefault();
                    el.value = currentHiddentAmount;
                    if (!checked) {
                        $(parentRows).find(".col-select_arpayments input:checkbox").trigger('click');
                    }
                    $(parentRows).find(".col-arpayment_amount span.balance_ar").html((outstanding - currentHiddentAmount).toFixed(2));
                    $(parentRows).find(".col-arpayment_amount input.ar_remaining_value").val((outstanding - currentHiddentAmount).toFixed(2));
                    var getInvoiceId = $(parentRows).find(".col-select_arpayments input:checkbox").prop('value');
                    $('#details_'+getInvoiceId).focus();
                    arPaymentsJs.arpaymentsalert('Maximum payment amount allowed for this invoice is', currentHiddentAmount);
                    $(parentRows).find(".col-arpayment_amount input.arpayment_amount").val(currentHiddentAmount);
                    $(parentRows).find(".col-arpayment_amount input.arpayment_amount").trigger('click');
                    $(parentRows).find(".col-arpayment_amount input.arpayment_amount").trigger('blur');
                    $(parentRows).find(".col-arpayment_amount input.arpayment_amount").trigger('change');
                    return;
                } else {
                    $(parentRows).find(".col-arpayment_amount input.ar_remaining_value").val((outstanding - inptTxtVal).toFixed(2));
                    $(parentRows).find(".col-arpayment_amount span.balance_ar").html((outstanding - inptTxtVal).toFixed(2));
                    if (inptTxtVal == 0) {
                        if (checked) {
                            $(parentRows).find(".col-select_arpayments input:checkbox").trigger('click');
                        }
                    } else {
                        if (!checked) {
                            $(parentRows).find(".col-select_arpayments input:checkbox").trigger('click');
                        }
                    }
                    el.value = inptTxtVal;
                    return true;
                }
            },
            applyArpaymentsBalance: function() {
                var allocate_amount = $('#allocate_amount').val();
                var allocate_amount_original = $('#allocate_amount_original').val();
                if ((allocate_amount) && (allocate_amount_original === "")) {
                    $('#allocate_amount_original').val(allocate_amount);
                    $('.amount_left_ar').html(allocate_amount);
                    $('#amount_left_ar').val(allocate_amount);
                }
            },
            //Proceed to the checkout page
            proceedToPreview: function() {
                var self = this;
                if ($('.landingDetails').length) {
                    $('.landingDetails').remove();
                }
                $('body').trigger('processStart');
                var checkPopupAddress = this.checkPopupAddress();
                if (!checkPopupAddress) {
                    $('body').trigger('processStop');
                    var referer = "proceed";
                    self.addresspopup(referer);
                    return false;
                }
                var getSerialized = self.getSerializeValues();
                if (getSerialized) {
                    var selfJson = getSerialized.json;
                    var selfJsonLength = getSerialized.length;
                } else {
                    selfJsonLength = 0;
                }
                var newItems = [];
                if (selfJsonLength > 0) {
                    var i = 0;
                    for (var key in selfJson) {
                        if (selfJson.hasOwnProperty(key)) {
                            $.each(selfJson[key], function(key, value) {
                                if (key == "arpayment_amount[]") {
                                    newItems[i] = value;
                                }
                            });
                        }
                        i++;
                        //var parentRows = $(this).parents('tr').first();
                        //var amountVals = $(parentRows).find(".col-arpayment_amount input.arpayment_amount").val();
                        //newItems[i] = amountVals;
                    }
                } else {
                    var amountVals = $('#allocate_amount').val();
                    var paymentOnAccount = $('#payment_on_account').prop('checked');
                    var ignoreItems = false;
                    if ((paymentOnAccount) && (amountVals > 0)) {
                        ignoreItems = true;
                    } else if ((paymentOnAccount) && (amountVals <= 0)) {
                        $('body').trigger('processStop');
                        arPaymentsJs.arpaymentsalert('Please Enter a valid Amount');
                        return false;
                    } else {
                        $('body').trigger('processStop');
                        arPaymentsJs.arpaymentsalert('Please select one or more invoices');
                        return false;
                    }
                }
                if (!ignoreItems) {
                    var hasEmptyElements = self.hasEmptyArElement(newItems);
                    if (!hasEmptyElements) {
                        $('body').trigger('processStop');
                        arPaymentsJs.arpaymentsalert('Please enter a valid amount for the selected invoices');
                        return false;
                    }
                }
                self.updateInvoicePost();
            },
            //Checking the object Size
            objSize: function(obj) {
                var count = 0;
                if (typeof obj == "object") {

                    if (Object.keys) {
                        count = Object.keys(obj).length;
                    } else if (window._) {
                        count = _.keys(obj).length;
                    } else if (window.$) {
                        count = $.map(obj, function() {
                            return 1;
                        }).length;
                    } else {
                        for (var key in obj)
                            if (obj.hasOwnProperty(key)) count++;
                    }
                }
                return count;

            },
            //Converting the params into object
            deparam: function(queryString) {
                if (queryString.indexOf('?') > -1) {
                    queryString = queryString.split('?')[1];
                }
                var pairs = queryString.split('&');
                var result = {};
                var self = this;
                pairs.forEach(function(pair) {
                    pair = pair.split('=');
                    if (pair) {
                        splitPair = pair[1];
                        splits = self.parseQuery(self.decodeBase64(splitPair));
                        result[pair[0]] = splits;
                    }
                });
                return result;
            },
            //Currently all the values are convereted into 
            //a base 64 encode. We are converting into base64 decode
            decodeBase64: function(input) {
                if (!input) {
                    return;
                }
                var keyStr = "ABCDEFGHIJKLMNOP" +
                    "QRSTUVWXYZabcdef" +
                    "ghijklmnopqrstuv" +
                    "wxyz0123456789+/" +
                    "=";
                var output = "";
                var chr1, chr2, chr3 = "";
                var enc1, enc2, enc3, enc4 = "";
                var i = 0;

                // remove all characters that are not A-Z, a-z, 0-9, +, /, or =
                var base64test = /[^A-Za-z0-9\+\/\=]/g;
                input = input.replace(/[^A-Za-z0-9\+\/\=]/g, "");
                do {
                    enc1 = keyStr.indexOf(input.charAt(i++));
                    enc2 = keyStr.indexOf(input.charAt(i++));
                    enc3 = keyStr.indexOf(input.charAt(i++));
                    enc4 = keyStr.indexOf(input.charAt(i++));

                    chr1 = (enc1 << 2) | (enc2 >> 4);
                    chr2 = ((enc2 & 15) << 4) | (enc3 >> 2);
                    chr3 = ((enc3 & 3) << 6) | enc4;

                    output = output + String.fromCharCode(chr1);

                    if (enc3 != 64) {
                        output = output + String.fromCharCode(chr2);
                    }
                    if (enc4 != 64) {
                        output = output + String.fromCharCode(chr3);
                    }

                    chr1 = chr2 = chr3 = "";
                    enc1 = enc2 = enc3 = enc4 = "";

                } while (i < input.length);
                return output.replace(/[^\x20-\x7E]+/g, ''); //this.escapeUnicode(decodeURIComponent(output));                
            },
            //Parsing the converted queries inside the hidden grid box
            parseQuery: function(str) {
                if (typeof str != "string" || str.length == 0) return {};
                var s = str.split("&");
                var s_length = s.length;
                var bit, query = {},
                    first, second;
                for (var i = 0; i < s_length; i++) {
                    bit = s[i].split("=");
                    first = decodeURIComponent(bit[0]);
                    if (first.length == 0) continue;
                    second = decodeURIComponent(bit[1]);
                    if (typeof query[first] == "undefined") query[first] = second;
                    else if (query[first] instanceof Array) query[first].push(second);
                    else query[first] = [query[first], second];
                }
                return query;
            },
            hasEmptyArElement: function(my_arr) {
                for (var i = 0; i < my_arr.length; i++) {
                    if (my_arr[i] == 0)
                        return false;
                }
                return true;
            },
            //Before opening the checkout page
            //we are updating the invoices
            updateInvoicePost: function() {
                var ajaxValue = ['ajaxvalue'];
                var self = this;
                var serviceUrl, payload;
                var proceedPost = this.proceedToPost();
                var allocatedAmount = $('#allocate_amount').val();
                var amountLeft = $('#amount_left_ar').val();
                var paymentOnAccount = $('#payment_on_account').prop('checked');
                serviceUrl = 'customerconnect/arpayments/updateinvoices';
                payload = {
                    invoiceinfo: proceedPost,
                    allocatedAmount: allocatedAmount,
                    amountLeft: amountLeft,
                    paymentOnAccount: paymentOnAccount
                };
                storage.post(
                    serviceUrl,
                    payload,
                    false,
                    'application/x-www-form-urlencoded; charset=UTF-8'
                ).done(function(response) {
                    self.checkoutpopup(proceedPost);
                }).fail(function(response) {
                    alerts({
                        content: $t('There was error during saving data')
                    });
                });
            },
            openreviewpopup: function() {
                var options = {
                    type: 'popup',
                    responsive: true,
                    innerScroll: true,
                    // title: 'Elements Payment'
                };
                var popup = modal(options, $('#arpayments-iframe-popup-modal'));
                $('#arpayments-iframe-popup-modal').modal('openModal');
                $('.modal-footer').hide();
            },
            //Proceeding to the checkout page with the json values
            proceedToPost: function() {
                var newItems = {};
                var self = this;
                var getSerialized = self.getSerializeValues();
                if (getSerialized) {
                    var selfJson = getSerialized.json;
                    var selfJsonLength = getSerialized.length;
                    if (selfJsonLength > 0) {
                        var objs = {};
                        objs['dispute'] = false;
                        objs['disputeComment'] = false;
                        var newItems = {};
                        for (var key in selfJson) {
                            $.each(selfJson[key], function(keys, value) {
                                if (keys == "arpayment_amount[]") {
                                    objs['jsonPaymentVals'] = value;
                                }
                                if (keys == "ar_remaining_value[]") {
                                    objs['balance_ar'] = value;
                                }
                                if (keys == "arpaymentjson[]") {
                                    objs['jsonVals'] = value;
                                }
                                if (keys == "settlement_discount") {
                                    objs['settlementDiscount'] = value;
                                } else {
                                    objs['settlementDiscount'] = "0.00";
                                }
                                
                                
                                if (keys == "aroutstanding_value[]") {
                                    objs['settlementTermAmount'] = value;
                                }

                                if (keys == "dispute_invoice_serialize[]") {
                                    if (value == "checked") {
                                        var disputes = true;
                                    } else {
                                        var disputes = false;
                                    }
                                    objs['dispute'] = disputes;
                                }

                                if (keys == "dispute_invoices_serializecomments[]") {
                                    objs['disputeComment'] = value;
                                }

                            });
                            var appendjson = JSON.parse(JSON.stringify({
                                'userPaymentAmount': objs['jsonPaymentVals'],
                                'dispute': objs['dispute'],
                                'disputeComment': objs['disputeComment'],
                                'remainingBalance': objs['balance_ar'],
                                'settlementDiscount': objs['settlementDiscount'],
                                'settlementTermAmount': objs['settlementTermAmount']
                            }));
                            var trimmedJson = objs['jsonVals'].replace(/(.+?\}).*/, "$1");
                            var createjsonObject = JSON.parse(trimmedJson);
                            var createjsonObjects = self.extendArJson(createjsonObject, appendjson);;
                            newItems[key] = createjsonObjects;
                            //$i++;
                        }
                    }
                };
                return JSON.stringify(newItems);
            },
            extendArJson: function(o1, o2) {
                for (var key in o2) {
                    o1[key] = o2[key];
                }
                return o1;
            },
            calculateArAmountAllocate: function(items) {
                var totalValue = 0;
                var self = this;
                var getSerialized = self.getSerializeValues();
                var selfJson = getSerialized.json;
                var selfJsonLength = getSerialized.length;
                var amountVals = 0;
                if (selfJsonLength > 0) {
                    for (var mainkey in selfJson) {
                        if (selfJson.hasOwnProperty(mainkey)) {
                            $.each(selfJson[mainkey], function(key, value) {
                                if (key == "arpayment_amount[]") {
                                    amountVals = value;
                                }
                                if (key == "aroutstanding_value") {
                                    var outstanding = value;
                                }

                            });
                            totalValue += parseFloat(amountVals);
                            //$(this).find(".col-arpayment_amount span.balance_ar").html((outstanding - amountVals).toFixed(2));
                            //$(this).find(".col-arpayment_amount input.ar_remaining_value").val((outstanding - amountVals).toFixed(2));
                        }
                    };
                }
                self.incDecAmount(totalValue);
                if (totalValue == "0") {
                    $('.paymentamount_arpay').html(0.00);
                } else {
                    self.getSerializeData();
                }

            },
            //get the serialzied data from the hidden field
            getSerializeData: function() {
                var self = this;
                var totalValue = 0;
                var getSerializeJson = $("input[name='links[invoices]']").val();
                var selfJson = self.deparam(getSerializeJson);
                var selfJsonLength = self.objSize(selfJson);
                var newItems = [];
                if (selfJsonLength > 0) {
                    var i = 0;
                    for (var mainkey in selfJson) {
                        if (selfJson.hasOwnProperty(mainkey)) {
                            $.each(selfJson[mainkey], function(key, value) {
                                if (key == "arpayment_amount[]") {
                                    newItems[i] = value;
                                    totalValue += parseFloat(newItems[i]);
                                }
                                if (key == "dispute_invoices_serializecomments[]") {
                                    var serializercomments = $('[id="dispute_invoices_serializecomments_' + mainkey + '"]').val();
                                    $('[id="dispute_invoices_comments_' + mainkey + '"]').html(serializercomments);
                                    $('[id="dispute_invoices_comments_' + mainkey + '"]').val(serializercomments);
                                }
                                if (key == "ar_remaining_value[]") {
                                    var outstandingValue = $('[id="aroutstanding_value_' + mainkey + '"]').val();
                                    var paymentamount = $('[id="arpayment_amount_' + mainkey + '"]').val();
                                    $('[id="balance_ar_' + mainkey + '"]').html((outstandingValue - paymentamount).toFixed(2));
                                    $('[id="ar_remaining_value_' + mainkey + '"]').val((outstandingValue - paymentamount).toFixed(2));
                                }
                                if (key == "dispute_invoice_serialize[]") {
                                    var serializercheckbox = $('[id="dispute_invoice_serialize_' + mainkey + '"]').val();
                                    if (serializercheckbox == "checked") {
                                        $('[id="dispute_invoices_' + mainkey + '"]').prop('checked', true);
                                    }
                                }
                            });
                        }
                        i++;
                    }
                    $('.paymentamount_arpay').html(totalValue.toFixed(2));
                }
            },
            //Address Popup
               addresspopup: function(referer) {
                if ($('#arpayments-edit-popup-modal').length) {
                    $('#arpayments-edit-popup-modal').remove();
                    $('#show-arpayments-editpopup').remove();
                }
                if ($('.checkoutpopup').length) {
                    $('.checkoutpopup').remove();
                }
                if ($('.landingpopup').length) {
                    $('.landingpopup').remove();
                }
                if ($('.landingDetails').length) {
                    $('.landingDetails').remove();
                }                

                var baseurl = $("#baseUrl").val();
                $.ajax({
                    showLoader: true,
                    data: {
                        mode: 'super',
                        referer: referer
                    },
                    url: baseurl + "customerconnect/arpayments/addressupdate",
                    type: "POST",
                    //dataType:'json',
                }).done(function(data) {
                    var options = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        modalClass: 'landingpopup',
                        title: 'Add/update billing address'
                    };


                    if ($('#address_form_block').length) {
                        if($('aside.confirm').length) {
                            $("aside.confirm .action-close").trigger("click");
                        }
                        $('#address_form_block').remove();
                    }  

                    if ($('.landingpopup').length) {
                        $('.landingpopup').remove();
                    }                                      


                    $("#customer_arpayments_invoices_list").append("<div id='arpayments-edit-popup-modal'></div>");
                    $("#arpayments-edit-popup-modal").append("<div id='show-arpayments-editpopup'></div>");
                    var popup = modal(options, $('#arpayments-edit-popup-modal'));
                    $('#arpayments-edit-popup-modal').modal('openModal');
                    $('.landingpopup .modal-footer').hide();
                    $('#show-arpayments-editpopup').append(data);
                   
                });
            },
            //Details Popup
            detailspopup: function(invoiceno, orderId) {
                if ($('#arpayments-details-popup-modal').length) {
                    $('#arpayments-details-popup-modal').remove();
                    $('#show-arpayments-detailspopup').remove();
                }

                if ($('.landingDetails').length) {
                    $('.landingDetails').remove();
                }

                var dispute = false;
                var self = this;
                var disputeComment = '';
                if ($('[id="dispute_invoices_' + orderId + '"]').length > 0) {
                    if ($('[id="dispute_invoices_' + orderId + '"]').is(":checked")) {
                        dispute = true;
                    }
                    disputeComment = $('[id="dispute_invoices_comments_' + orderId + '"]').val();
                }

                var baseurl = $("#baseUrl").val();
                $.ajax({
                    showLoader: true,
                    data: {
                        mode: 'super',
                        invoiceid: orderId,
                        dispute: dispute,
                        disputeComment: disputeComment
                    },
                    url: baseurl + "customerconnect/arpayments/invoicedetails?invoice=" + invoiceno,
                    type: "POST",
                    //dataType:'json',
                }).done(function(data) {
                    var options = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        modalClass: 'landingDetails',
                        title: 'Invoice Number : ' + orderId,
                        closed: function() {
                            self.setCommenttoGrid(orderId);
                        },
                    };
                    $("#customer_arpayments_invoices_list").append("<div id='arpayments-details-popup-modal'></div>");
                    $("#arpayments-details-popup-modal").append("<div id='show-arpayments-detailspopup'></div>");
                    var popup = modal(options, $('#arpayments-details-popup-modal'));
                    $('#arpayments-details-popup-modal').modal('openModal');
                    $('body').trigger('processStop');
                    $('.modal-footer').hide();
                    var html = $.parseHTML( data);
                    $('#show-arpayments-detailspopup').append(html);
                });
            },
            //Toggling the Ar checkbox
            toggleArCheckbox: function(invoiceId) {
                if ($('[id="dispute_popupinvoices_' + invoiceId + '"]').is(":checked")) {
                    $('[id="dispute_invoices_' + invoiceId + '"]').prop('checked', true);
                    $('[id="dispute_invoice_serialize_' + invoiceId + '"]').val('checked');
                } else {
                    $('[id="dispute_invoices_' + invoiceId + '"]').prop('checked', false);
                    $('[id="dispute_invoice_serialize_' + invoiceId + '"]').val('unchecked');
                }
            },
            setCommenttoGrid: function(invoiceId) {
                var checkedId = $('[id="dispute_popupinvoices_comments_' + invoiceId + '"]');
                if ($('.landingDetails').length) {
                    $('.landingDetails').remove();
                }                
                if ($(checkedId).length > 0) {
                    var assignValue = $(checkedId).val();
                    var assingchildvalue = $('[id="dispute_invoices_comments_' + invoiceId + '"]');
                    if (assignValue !== '') {
                        assingchildvalue.val(assignValue);
                        $('[id="dispute_invoices_serializecomments_' + invoiceId + '"]').val(assignValue);
                        $('[id="dispute_invoices_serializecomments_' + invoiceId + '"]').trigger('click');
                        $('[id="dispute_invoices_serializecomments_' + invoiceId + '"]').trigger('blur');
                        $('[id="dispute_invoices_serializecomments_' + invoiceId + '"]').trigger('change');
                    } else {
                        assingchildvalue.val('');
                        $('[id="dispute_invoices_serializecomments_' + invoiceId + '"]').val('');
                        $('[id="dispute_invoices_serializecomments_' + invoiceId + '"]').trigger('click');
                        $('[id="dispute_invoices_serializecomments_' + invoiceId + '"]').trigger('blur');
                        $('[id="dispute_invoices_serializecomments_' + invoiceId + '"]').trigger('change');
                    }
                }
            },
            selectAddressForBilling: function(value) {
                if (value) {
                    $('#erpaddressSubmit').show();
                    $('#form-validate').hide();
                } else {
                    $('#erpaddressSubmit').hide();
                    $('#form-validate').show();
                }
            },
            //Address form validation
            //Preventing the values
            preventFormSubmit: function(formid, postUrl, mode, referer) {
                if (formid == "erp") {
                    var selected = $('#araddress option:selected').val();
                    var newAddress = false;
                } else {
                    var dataForm = $('#' + formid);
                    if (!$(dataForm).valid()) {
                        return false;
                    }
                    var checkValidation = dataForm.mage('validation', {});
                    var serializepost = $('#' + formid).serializeArray();
                    var indexed_array = {};
                    $.map(serializepost, function(n, i) {
                        indexed_array[n['name']] = n['value'];
                    });
                    var custom_arr1 = [];
                    custom_arr1.push(indexed_array['address1']);
                    custom_arr1.push(indexed_array['address2']);
                    custom_arr1.push(indexed_array['address3']);
                    indexed_array.street = custom_arr1;
                    var newAddress = true;
                }
                $('body').trigger('processStart');
                var serviceUrl = 'customerconnect/arpayments/addresspost';
                if (newAddress) {
                    var payload = {
                        addressInfo: indexed_array,
                        newAddress: newAddress,
                        mode: mode,
                    };
                } else {
                    var payload = {
                        erpInfo: selected,
                        newAddress: newAddress,
                        mode: mode,
                    };
                }
                storage.post(
                    serviceUrl,
                    payload,
                    false,
                    'application/x-www-form-urlencoded; charset=UTF-8'
                ).done(function(response) {
                    $('body').trigger('processStop');
                    var json = $.parseJSON(response);
                    if (!json.error) {
                        if (mode == "checkout") {
                            $('#arpayment_address_value').val(1);
                            if ($('#chekout_address_html').length > 0) {
                                $('#chekout_address_html').remove();
                            }
                            $(json.content).insertAfter('#addressdetails');
                            if ($('#address_block').length > 0) {
                                $('#address_block').remove();
                            }
                            $(json.parentcontent).insertAfter('#payment_block');
                            $(".checkoutpopup .action-close").trigger("click");
                        } else {
                            var allocate_amount = $('#allocate_amount').val();
                            var getAddressValue = $('#arpayment_address_value').val();
                            $('#arpayment_address_value').val(1);
                            if ((allocate_amount > 0) && (!getAddressValue) && (referer === "applypayment")) {
                                $("#allocatebutton").trigger("click");
                            }
                            if ($('#address_block').length > 0) {
                                $('#address_block').remove();
                            }
                            $(json.content).insertAfter('#payment_block');
                            $(".action-close").trigger("click");
                            if (referer == "proceed") {
                                arPaymentsJs.proceedToPreview();
                            }
                        }

                    } else {
                        arPaymentsJs.arpaymentsalert('Address Information was not properly set');
                    }
                    $('body').trigger('processStop');
                }).fail(function(response) {
                    arPaymentsJs.arpaymentsalert('There was error during saving data');
                    $('body').trigger('processStop');
                });
            },
            addOrUpdateAddress: function(update, valueamount) {
                arPaymentsJs.addresspopup('changeaddress');
            },
            arpaymentsalert: function(message, customvalue) {
                if (typeof customvalue === 'undefined') {
                    custom = '';
                } else {
                    custom = customvalue;
                }
                alerts({
                    content: $t(message + " " + custom)
                });
            },
            clearAllocatedInvoiceAmount: function() {
                var self = this;
                //$("input[name='links[invoices]']").prop("value", "");
                var getId = $("input[name='links[invoices]']").prop("id");
                var allocateAmt = $("#allocate_amount");
                $('#payment_on_account').prop('checked', false);
                $('#allocate_amount').prop("value", "");
                $('#canUpdateByInvoice').prop("value", "");
                $('#allocate_amount_original').prop("value", "");
                $('#amount_left_ar').val(0.00);
                $('.amount_left_ar').html("0.00");
                self.calculateArSum(true);
                $("#" + getId).trigger('keyup');
                $("#" + getId).trigger('change');
                //$("input[name='links[invoices]']").prop("value", "");
                //$('.paymentamount_arpay').html(0.00);
            },
            checkPopupAddress: function() {
                var self = this;
                var addressPopup = $("#arpayment_address_value").val();
                if (addressPopup != "1") {

                    return false;
                } else {
                    return true;
                }
            },
            checkoutpopup: function(proceedPost) {
                if ($('#arpayments-checkout-popup-modal').length) {
                    $('#arpayments-checkout-popup-modal').remove();
                    $('#show-arpayments-checkoutpopup').remove();
                }
                if ($('.checkoutscreen').length) {
                    $('.checkoutscreen').remove();
                }


                var innerHtml = $('.paymentamount_arpay').html(); // = 1
                var allocatedAmount = $('#allocate_amount').val();
                var amountLeft = $('#amount_left_ar').val();
                var paymentOnAccount = $('#payment_on_account').prop('checked');
                var baseurl = $("#baseUrl").val();
                var baseurlCheckout = $("#baseUrl").val()+ "customerconnect/arpayments/archeckout#payment";
                
                $.ajax({
                    showLoader: false,
                    data: {
                        mode: 'super',
                        invoiceinfo: proceedPost,
                        totalAmountForInvoice: innerHtml,
                        allocatedAmount: allocatedAmount,
                        amountLeft: amountLeft,
                        paymentOnAccount: paymentOnAccount
                    },
                    url: baseurl + "customerconnect/arpayments/checkout",
                    type: "POST",
                    //dataType:'json',
                }).done(function(data) {
                    var ifr=$('<iframe/>', {
                         src: baseurlCheckout,
                         id:  'arpaymentsIframe',
                         allowtransparency: true,
                         style:'width:100%;background-color:transparent;height:100%;min-height:600px;display:block;border:none;'
                    });
                    var options = {
                        type: 'popup',
                        modalClass: 'checkoutscreen',
                        responsive: false,
                        innerScroll: true,
                        title: 'Payments'
                    };
                    $('body').trigger('processStop');

                    $("#customer_arpayments_invoices_list").append("<div id='arpayments-checkout-popup-modal'></div>");
                    $("#arpayments-checkout-popup-modal").append("<div id='show-arpayments-staticcontents'></div><div id='show-arpayments-checkoutpopup' style='display: none;'></div>");
                    var popup = modal(options, $('#arpayments-checkout-popup-modal'));
                    $('#arpayments-checkout-popup-modal').modal('openModal');
                    $('#show-arpayments-staticcontents').append(data);
                    $('#show-arpayments-checkoutpopup').append(ifr);
                    $('.modal-footer').hide();
                    $('#footer-col-2').hide();
                });
            },
            continueToReview: function() {
                $('body').trigger('processStart');
                var ajaxValue = $("input[name='payment[method]']:checked").val();
                var self = this;
                var serviceUrl, payload;
                serviceUrl = 'customerconnect/arpayments/savepayment';
                payload = {
                    payment: ajaxValue
                };
                var baseurlCheckout = $("#baseUrl").val()+ "customerconnect/arpayments/archeckout#payment";
                if ($('#arpaymentsIframe').length) {
                    $('#arpaymentsIframe').remove();
                }                
                var ifr=$('<iframe/>', {
                     src: baseurlCheckout,
                     id:  'arpaymentsIframe',
                     allowtransparency: true,
                     style:'width:100%;background-color:transparent;height:100%;min-height:600px;display:block;border:none;'
                });                
                $('#show-arpayments-checkoutpopup').append(ifr);
                var iframe=$('#arpaymentsIframe');
                iframe.onload=function(){
                    self.arpaymentsInvalidateCart();
                };
                $('body').trigger('processStop');
                $('#footer-col-2').addClass('open active').removeClass('close');
                $('#footer-col-1').addClass('').removeClass('active');
                $('#footer-col-2').show();
                $('#footer-col-header-1').addClass('').removeClass('open');
                $('#footer-col-1').addClass('close').removeClass('open');
                $('#show-arpayments-checkoutpopup').show();
                $('#footer-col-1').hide();
            },
            addressCheckoutpopup: function() {
                if ($('#arpayments-editcheckout-popup-modal').length) {
                    $('#arpayments-editcheckout-popup-modal').remove();
                    $('#show-arpayments-editcheckoutpopup').remove();
                }
                if ($('.checkoutpopup').length) {
                    $('.checkoutpopup').remove();
                }
                if ($('.landingpopup').length) {
                    $('.landingpopup').remove();
                }
                var baseurl = $("#baseUrl").val();
                $.ajax({
                    showLoader: true,
                    data: {
                        mode: 'checkout'
                    },
                    url: baseurl + "customerconnect/arpayments/addressupdate",
                    type: "POST",
                    //dataType:'json',
                }).done(function(data) {
                    var options = {
                        type: 'popup',
                        responsive: true,
                        innerScroll: true,
                        modalClass: 'checkoutpopup',
                        title: 'Add/update billing address'
                    };
                    $("#customer_arpayments_invoices_list").append("<div id='arpayments-editcheckout-popup-modal'></div>");
                    $("#arpayments-editcheckout-popup-modal").append("<div id='show-arpayments-editcheckoutpopup'></div>");
                    var popup = modal(options, $('#arpayments-editcheckout-popup-modal'));
                    $('#arpayments-editcheckout-popup-modal').modal('openModal');
                    $('#show-arpayments-editcheckoutpopup').append(data);
                    $('.modal-footer').hide();
                });
            },
            getAddressCheckedOrNot: function() {
                var addressPopup = $("#arpayment_address_value").val();
                return addressPopup;
            },
            arpaymentsInvalidateCart : function() {
                var sections = ['cart'];
                customerData.invalidate(sections);
                customerData.reload(sections, true);                
            },
            blurAllocateAmount: function() {
                var amountLeft = $('#amount_left_ar').val();
                var originalAmnt = $('#allocate_amount_original').val();
                var allocateAmt = $("#allocate_amount");
                if (((amountLeft != "") && (amountLeft != 0) && (amountLeft != '0.00') && (allocateAmt.val() != originalAmnt)) || ((allocateAmt.val() != originalAmnt) && (originalAmnt != '') && (originalAmnt != '0') && (originalAmnt != '0.00'))) {
                    confirmation({
                        //title: 'Cancel order',
                        content: 'Do you like to change the allocated amount?',
                        actions: {
                            confirm: function() {
                                $('#payment_on_account').prop('checked', false);
                                if (!isNaN(allocateAmt.val())) {
                                    var calculateAmt = (allocateAmt.val()) ? allocateAmt.val() : '0';
                                    $('#allocate_amount').val(parseFloat(calculateAmt));
                                } else {
                                    $('#allocate_amount').val(0);
                                }
                                if ($('#allocate_amount').val() == "") {
                                    $('#allocate_amount').val(0);
                                }
                                arPaymentsJs.calculateArSum(true, 'bluramount');
                            },
                            cancel: function() {
                                var originalAllocated = $('#allocate_amount');
                                originalAllocated.val($('#allocate_amount_original').val());
                            },
                            always: function() {}
                        }
                    });
                } else if ((allocateAmt.val() == 0) && (allocateAmt.val() != originalAmnt)) {
                    arPaymentsJs.calculateArSum(true, 'bluramount');
                } else {
                    arPaymentsJs.calculateArCheckSum();
                }
            },
            filterInvoiceGrid: function(event) {
                var element = Event.element(event);
                var spantag = false;
                if(element.tagName =="SPAN") {
                    var elementClass = $(element).closest('th').prop('className');
                    var ElementContainsClass = $(element).closest('th').hasClass('active');
                    var classList = elementClass.split(/\s+/);
                    spantag = true;
                } else {
                     ElementContainsClass = element.classList.contains('active');
                     classList = $(element).attr('class').split(/\s+/);
                }
                
                var str2="col-aged_balance_filter_";
                var matchValue = '';         
                $.each(classList, function(index, item) {
                   if(item.indexOf(str2) != -1){
                     var newString = item.split("aged_balance_filter_").pop();
                     matchValue =newString;
                   }
                }); 
                $('#customer_arpayments_agedbalances_list_table th').removeClass("active");
                if(ElementContainsClass) {
                    $('#customer_arpayments_invoices_list_filter_aged_period_number').val("");
                } else {
                    if(spantag) {
                      $(element).closest('th').addClass(' active');
                    } else {
                      element.className = element.className + " active";  
                    }
                    $('#customer_arpayments_invoices_list_filter_aged_period_number').val(matchValue);
                }
                customer_arpayments_invoices_listJsObject.doFilter();
            },
            restClearAgedFilter: function() {
                $('#customer_arpayments_agedbalances_list_table th').removeClass("active");
                $('#customer_arpayments_invoices_list_filter_aged_period_number').val("");
                customer_arpayments_invoices_listJsObject.doFilter();
            }
        }

        domReady(function() {
            Event.live('.allocate_amount', 'keyup', function(el, e) {
                el.value = el.value.replace(/([^\d]*)(\d*(\.\d{0,2})?)(.*)/, '$2');
            });
            Event.live('.allocate_amount', 'paste', function(el, e) {
                setTimeout(function() {
                    el.value = el.value.replace(/([^\d]*)(\d*(\.\d{0,2})?)(.*)/, '$2');
                }, 100);
            });
            Event.live('.arpayment_amount', 'keyup', function(el, e) {
                el.value = el.value.replace(/([^\d]*)(\d*(\.\d{0,2})?)(.*)/, '$2');
                var enteredAmount = el.value;
                if ((enteredAmount != "0") && (arPaymentsJs.getAddressCheckedOrNot() != "1")) {
                    e.preventDefault(); // e.stopPropagation();
                    arPaymentsJs.addresspopup("paymentamount");
                }
                arPaymentsJs.checkArRowTotal(el, e);
                arPaymentsJs.calculateArAmount(el, e);
            });
            Event.live('.arpayment_amount', 'paste', function(el, e) {
                setTimeout(function() {
                    el.value = el.value.replace(/([^\d]*)(\d*(\.\d{0,2})?)(.*)/, '$2');
                    arPaymentsJs.checkArRowTotal(el, e);
                    arPaymentsJs.calculateArAmount(el, e);
                }, 100);
            });
            Event.live('.arpayment_amount', 'cut', function(el, e) {
                setTimeout(function() {
                    el.value = el.value.replace(/([^\d]*)(\d*(\.\d{0,2})?)(.*)/, '$2');
                    arPaymentsJs.checkArRowTotal(el, e);
                    arPaymentsJs.calculateArAmount(el, e);
                }, 100);
            });
            Event.live('.col-select_arpayments input.checkbox', 'click', function(el, e) {
                if (!el.checked) {
                    var checkboxid = el.value;
                    $('[id="arpayment_amount_' + checkboxid + '"]').val(0);
                    var outstandingVals = $('[id="aroutstanding_value_' + checkboxid + '"]').val();
                    $('[id="ar_remaining_value_' + checkboxid + '"]').val(outstandingVals);
                    $('[id="balance_ar_' + checkboxid + '"]').html(outstandingVals);
                    arPaymentsJs.calculateArAmountAllocate();
                    arPaymentsJs.getSerializeData();
                }
            });
            Event.live('#payment_on_account', 'click', function(el, e) {
                var allocateAmount = $('#allocate_amount').val();
                if (allocateAmount > 0) {
                    return true;
                } else {
                    $('#payment_on_account').prop('checked', false);
                    alerts({
                        content: $t('Allocated Amount should be greater than 0')
                    });
                }
            });



            Event.live('.dispute_invoices_comments', 'keyup', function(e) {
                var invoiceId = $(e).data("id");
                var keyed = $(e).val().replace(/[\n]/g, '');
                $('[id="dispute_invoices_serializecomments_' + invoiceId + '"]').val(keyed);
                $('[id="dispute_invoices_serializecomments_' + invoiceId + '"]').trigger('click');
                $('[id="dispute_invoices_serializecomments_' + invoiceId + '"]').trigger('blur');
                $('[id="dispute_invoices_serializecomments_' + invoiceId + '"]').trigger('change');
            });

            Event.live('.dispute_invoices_comments', 'paste', function(e) {
                setTimeout(function() {
                    var invoiceId = $(e).data("id");
                    var keyed = $(e).val().replace(/[\n]/g, '<br />');
                $('[id="dispute_invoices_serializecomments_' + invoiceId + '"]').val(keyed);
                $('[id="dispute_invoices_serializecomments_' + invoiceId + '"]').trigger('click');
                $('[id="dispute_invoices_serializecomments_' + invoiceId + '"]').trigger('blur');
                $('[id="dispute_invoices_serializecomments_' + invoiceId + '"]').trigger('change');
                }, 100);
            });

            //Checkout Page javascript
            Event.live('.footer-toggle', 'click', function(e) {
                var collapse_content_selector = $(e).attr('data-role');
                if ((collapse_content_selector == "#footer-col-1") && ($(collapse_content_selector).hasClass("close"))) {
                    $('#footer-col-2').hide();
                    $('#footer-col-1').show();
                    $('#footer-col-header-1').addClass('open');
                    $('#show-arpayments-checkoutpopup').hide();
                    $('#footer-col-2').addClass('close').removeClass('open');
                }
            });
            
            var arr = $$('#customer_arpayments_agedbalances_list_table th');
            for (var i = 0, len = arr.length; i < len; i++) {
                arr[i].observe('click', arPaymentsJs.filterInvoiceGrid.bind(this));
            };
        });
    });