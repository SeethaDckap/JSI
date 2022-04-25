/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define([
    'jquery'
], function ($) {
    'use strict';

    $(document).on('click', 'button.reject-button', function (e) {
        setApprovalStatus('rejected')
    });

    $(document).on('click', 'button.approve-button', function (e) {
        setApprovalStatus('approved')
    });

    function setApprovalStatus(type){
        let approvalForm = $("#approve-reject-actions");
        let approvalInput = approvalForm.find('input.approval-status');
        approvalInput.val(type);
    }

    $(document).on('click', 'div#order-approval-approve-reject button', function (e) {
        let approvalForm = $("form#order-approval_approve-reject");
        let approvalTableRows = $("#order-approval_approve-reject table tbody tr");
        let approvalInput = $('input#order-approval-approved');
        let rejectedInput = $('input#order-approval-rejected');
        let approvedValues = [];
        let rejectedValues = [];
        approvalTableRows.each(function (index, el) {
            let checkedInput = $(el).find('td.col-approve input');
            let checkedValue = checkedInput.val();

            if (checkedInput.prop('checked') === true) {
                let currentCell = $(checkedInput).closest('td');
                if (currentCell.hasClass('col-approve')) {
                    approvedValues.push(checkedValue);
                }
            }
        });
        approvalTableRows.each(function (index, el) {
            let checkedInput = $(el).find('td.col-reject input');
            let checkedValue = checkedInput.val();

            if (checkedInput.prop('checked') === true) {
                let currentCell = $(checkedInput).closest('td');
                if (currentCell.hasClass('col-reject')) {
                    rejectedValues.push(checkedValue);
                }

            }
        });
        if(approvedValues.length > 0){
            let approvedJson = {'approved': approvedValues};
            approvalInput.val(JSON.stringify(approvedJson));
        }
        if(rejectedValues.length > 0){
            let rejectedJson = {'rejected': rejectedValues};
            rejectedInput.val(JSON.stringify(rejectedJson));
        }
        approvalForm.submit();
    });

    $(document).on('click', 'table#approval-grid_table tbody td input', function (e) {
        let selector = e.target;
        let currentRow = $(selector).closest('tr');
        let approveCheck = currentRow.find('td.col-approve input');
        let rejectCheck = currentRow.find('td.col-reject input');
        let cell = $(selector).closest('td');
        if ($(selector).prop('checked') === true) {
            if (cell.hasClass('col-approve')) {
                rejectCheck.prop('disabled', 'disabled');
            }
            if (cell.hasClass('col-reject')) {
                approveCheck.prop('disabled', 'disabled');
            }
        } else {
            if (cell.hasClass('col-approve')) {
                rejectCheck.prop('disabled', false);
            }
            if (cell.hasClass('col-reject')) {
                approveCheck.prop('disabled', false);
            }
        }
    });
});
