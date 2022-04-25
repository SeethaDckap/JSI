define([
        'jquery',
        'Magento_Ui/js/modal/modal'
    ], function ($, modal) {
        'use strict';

        return {
            emailPopUp: $('.preq-wait-main'),
            modalOption: {
                type: 'popup',
                modalClass: 'preq-wait',
                responsive: true,
                innerScroll: true,
                clickableOverlay: true,
                title: '',
                buttons: [],
            },
            showPopup: function () {
                modal(this.modalOption, this.emailPopUp);
                this.emailPopUp.modal('openModal');
            },
            closePopup: function () {
                $('.preq-wait-main').modal('closeModal');
            }

        };
    }
);
