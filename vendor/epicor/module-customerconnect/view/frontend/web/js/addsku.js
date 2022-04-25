/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define(['jquery'], function($){
    'use strict';

    return function(config, element) {
        function addNewRow()
        {
            var newRow = $("<tr>");
            var cols = "";

            cols += '<td><input type="checkbox" name="record"></td>';

            cols += '<td><input type="text" class="form-control psku" id="psku_'+counter+'" name="productsku['+counter+']"/></td>';
            cols += '<td><input type="text" class="form-control csku_sku" id="csku_'+counter+'" name="mysku['+counter+']"/></td>';
            cols += '<td><input type="text" class="form-control description" id="desc_'+counter+'" name="description['+counter+']"/></td>';

            newRow.append(cols);
            $(".add-sku-table").append(newRow);
            $(".add-sku-table #psku_"+counter).focus();
            counter++;
        }

        var counter = 1;

        $("#addrow").on("click", function () {
            addNewRow();
        });

        $(".add-sku-table").on('keydown', '.description', function(e) {
            var keyCode = e.keyCode || e.which;

            if (keyCode == 9) {
                e.preventDefault();
                addNewRow();
            }
        });

        $(".delete-row").click(function() {
            $("table tbody").find('input[name="record"]').each(function() {
                if($(this).is(":checked")) {
                    $(this).parents("tr").remove();
                }
            });
            var rowCount = $('.add-sku-table tr').length;
            if (rowCount < 1) {
                addNewRow();
            }
        });

        $(document).on('click','#add-skus',function(e) {
            e.preventDefault();
            $('.reqError').remove();
            $('.limError').remove();
            var isNotEmpty;
            var isValid;
            var isPskuNotEmpty;
            var allSkus = '';
            $('input.csku_sku').each(function() {
                var elmt = $(this);
                var elmtId = elmt.attr('id').split('_');
                var idNo = elmtId[1];
                var check1 = 'psku_' + idNo;
                var check2 = 'desc_' + idNo;
                if ((elmt.val() == "") && (($('#'+check1).val().length > 0) || ($('#'+check2).val().length > 0))) {
                    isNotEmpty = true;
                    elmt.parent().append('<span class="reqError">This is required field.</span>');
                }
                if (elmt.val().length >= 50) {
                    isValid = true;
                    elmt.parent().append('<span class="limError">Max 50 chars allowed.</span>');
                }
            });
            $('input.psku').each(function() {
                var elem = $(this);
                var elemtId = elem.attr('id').split('_');
                var idNum = elemtId[1];
                var chck1 = 'csku_' + idNum;
                var chck2 = 'desc_' + idNum;
                var elv = elem.val();
                if ((elv == "") && (($('#'+chck1).val().length > 0) || ($('#'+chck2).val().length > 0))) {
                    isPskuNotEmpty = true;
                    elem.parent().append('<span class="reqError">This is required field.</span>');
                } else {
                    allSkus = allSkus + ',' + elv;
                }
            });
            if (isNotEmpty || isValid || isPskuNotEmpty) {
                e.preventDefault();
            } else {
                $.ajax({
                    url: '/customerconnect/massactions/verifyskus',
                    showLoader: true,
                    type: "POST",
                    data: {skus: allSkus}
                }).done( function (response) {
                    if (response != 'no-error') {
                        alert(response);
                        e.preventDefault();
                    } else {
                        $('#customerconnect_skus_add-form').submit();
                    }
                });
            }
        });
    }
});