/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

require(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'mage/validation',
        'Magento_Ui/js/modal/confirm',
    ],
    function (
        $,
        modal,
        validation,
        confirm
    ) {
        var options = {
            type: 'popup',
            responsive: true,
            innerScroll: true,
            title: 'Test Traking URL',
            modalClass: 'custom-modal',
            buttons: [{
                text: 'Cancel',
                class: 'action-secondary',
                click: function () {
                    this.closeModal();
                }
            }, {
                text: 'Submit',
                class: 'action-primary',
                click: function () {
                    var url = $('input[name="tracking_url_hidden"]').val();
                    var trackingNumber = $('input[name="traking_number"]').val();
                    var formattedUrl = url.replace("{{TNUM}}", trackingNumber);
                    $('input[name="tracking_url"]').validate("required-entry");
                    $('input[name="traking_number"]').validation();
                    if ($('input[name="traking_number"]').validation('isValid')) {
                        window.open(formattedUrl, "_blank");
                    }
                }
            }]
        };

        var popup = modal(options, $('#popup-modal'));
        $("#test_track_url").click(function () {
            if (!$('input[name="tracking_url"]').val()) {
                confirm({
                    content: "Please enter a tracking url.",
                    title: 'Warning',
                    modalClass: 'confirm configuratorconfirmpopup',
                    clickableOverlay: false,
                    actions: {
                        cancel: function () {
                        },
                        always: function () {
                        }
                    }
                });

                return;
            }

            $('input[name="tracking_url"]').validation();
            if ($('input[name="tracking_url"]').validation('isValid')) {
                $("#popup-modal").modal('openModal');
                $('input[name="tracking_url_hidden"]').val($('input[name="tracking_url"]').val());
            }

        });
    }
);