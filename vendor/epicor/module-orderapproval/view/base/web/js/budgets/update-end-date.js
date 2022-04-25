/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    "jquery",
    'jquery/ui',
], function ($) {
    return {
        isSet: function(){
            let startDate = this.getStartDate();
            let duration = this.getDuration();
            let type = this.getType();
            return this.isValueSet(startDate) && this.isValueSet(duration) && this.isValueSet(type);
        },

        getDuration: function() {
            return $('#add_budget_form input#duration').val();
        },

        getStartDate: function() {
            return $('input#start_date').val();
        },

        getType: function() {
            return $('#budget_type option:selected').val();
        },

        getEndDateApiUrl: function() {
            return window.location.protocol + '//' + window.location.hostname +'/rest/V1/budgets/get-end-date';
        },
        isValueSet: function(value) {
            if(value){
                return true;
            }
            return false;
        },

        getEndDate: function () {
            $.ajax({
                url: this.getEndDateApiUrl(),
                data: {start: this.getStartDate(), duration: this.getDuration(), type: this.getType()},
                type: 'get',
                contentType: "application/json",
            }).done(function (data) {
                $('#end_date').val(data);
            });
        }
    }
});