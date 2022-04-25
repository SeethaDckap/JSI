define([
    'underscore',
    'jquery',
], function (_, $) {
    'use strict';
    return {
        currentAutoValue: null,
        initialAutoValue: null,
        incrementValue: null,
        currentPositionRow: null,
        getMainTable: function () {
            return $('#productsGrid_table tbody');
        },
        getAutoStartNumber: function () {
            let startVal = $(document).find('input#autonumber-start-value').val();
            if (typeof (startVal) !== "undefined" || startVal !== null) {
                this.setInitialAutoValue(startVal)
                return startVal;
            } else {
                return false;
            }
        },
        getAutoIncrement: function () {
            let incrementVal = $(document).find('input#autonumber-increment-value').val();
            if (typeof (incrementVal) !== "undefined" || incrementVal !== null) {
                return incrementVal;
            } else {
                return false;
            }
        },
        setInitialAutoValue: function (startVal) {
            if (this.initialAutoValue === null) {
                this.initialAutoValue = parseInt(startVal);
            }
        },
        checkInputs: function () {
            if (!this.isAutoNumberValuesSet()) {
                alert('Enter both a start and increment value');
                return false;
            }
            if (!this.isValidNumberType()) {
                alert('A positive integer is required');
                return false;
            }
            return true;
        },
        isAutoNumberValuesSet: function () {
            return !!(this.getAutoStartNumber() && this.getAutoIncrement());
        },
        isValidNumberType: function () {
            return this.isValidStartNumber() && this.isValidIncrementNumber();
        },
        isValidStartNumber: function () {
            let autoNumberStartValue = Number(this.getAutoStartNumber());
            return Math.floor(autoNumberStartValue) === autoNumberStartValue && autoNumberStartValue > 0;
        },
        isValidIncrementNumber: function () {
            let incrementNumberValue = Number(this.getAutoIncrement());
            return Math.floor(incrementNumberValue) === incrementNumberValue && incrementNumberValue > -1;
        },
        setPositionValue: function (row, index, selectedIndex) {
            this.currentPositionRow = row.find('td.ecc-list-position input.input-text');
            if (selectedIndex === 1) {
                this.setIncrementValue(parseInt(this.getAutoIncrement()));
                this.setInitialPositionRowValue();
            } else {
                this.currentPositionRow.val(this.currentAutoValue);
            }
            if (this.isRequiredToEmptyPositions()) {
                this.currentAutoValue = '';
            } else {
                this.currentAutoValue = this.currentAutoValue + this.incrementValue;
            }
            this.currentPositionRow.trigger('change');
        },
        setIncrementValue: function (incrementValue) {
            this.incrementValue = incrementValue;
        },
        isRequiredToEmptyPositions: function () {
            return this.incrementValue === 0 && this.initialAutoValue === 1
        },
        setInitialPositionRowValue: function () {
            if (this.isRequiredToEmptyPositions()) {
                this.currentPositionRow.val('');
            } else {
                this.currentPositionRow.val(this.currentAutoValue);
            }
        }
    };
});
