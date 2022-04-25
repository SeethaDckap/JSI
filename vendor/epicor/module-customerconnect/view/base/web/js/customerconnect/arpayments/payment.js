require([
        'jquery',
        'mage/url',
        'mage/storage',
        'mage/translate',
        'mage/template',
        'domReady',
        'Magento_Ui/js/modal/modal',
        'Magento_Ui/js/modal/alert',
        'Magento_Checkout/js/model/full-screen-loader',
        'mage/mage',
        'jquery/ui'
    ],
    function($, url, storage, $t, alerts, domReady, modal, alerts, fullScreenLoader) {
        window.arPaymentMethodJs = {
            proceedToPay: function() {
                $('body').trigger('processStart');
                var paymentGateway = $("input[name='payment[method]']:checked").val();
                if(!paymentGateway) {
                    alert('please select a payment method');
                    $('body').trigger('processStop');
                    return false;
                } 
                var gateway = paymentGateway;
                var self = this;
                switch (gateway) {
                    case 'elements':
                        self.continueToElements();
                        break;
                    default:
                        alert('Place Order Error');
                }
            },
            /** Redirect to Elements */
            continueToElements: function() {
                var self = this;
                if ($('#elementsFrame').length) {
                    $('#elementsFrame').remove();
                }
                self.getIframeUrl();
                return this;
            },
            showElementsError: function(msg) {
                alert(msg);
            },
            getIframeUrl: function() {
                var iframeself = this;
                if ($('#elements-iframe-popup-modal').length) {
                    $('#elements-iframe-popup-modal').remove();
                }
                if ($('.elementspopup').length) {
                    $('.elementspopup').remove();
                }
                let isMobile = 0;
                if (screen.width <= 1024) {
                    isMobile = 1;
                }
                let postData = {
                    'isMobile': isMobile
                }
                $.ajax({
                    showLoader: true,
                    url: url.build('elements/payment/Arsavereview'),
                    type: "POST",
                    dataType: 'json',
                    data: postData
                }).done(function(data) {
                    var obj = $.parseJSON(data);
                    var getSuccess = obj.setupSuccess;
                    if (getSuccess == "Y") {
                        $("#paymentwrapper").append("<div id='elements-iframe-popup-modal'><div id='show-elements-iframe'></div></div>");
                        if (!obj.debug.tokenInfo.error) {
                            var options = {
                                type: 'popup',
                                responsive: true,
                                innerScroll: true,
                                modalClass: 'elementspopup',
                                // title: 'Elements Payment'
                            };
                            var popup = modal(options, $('#elements-iframe-popup-modal'));
                            $('#elements-iframe-popup-modal').modal('openModal');
                            $('.modal-footer').hide();
                            var ifr = $('<iframe/>', {
                                src: obj.transactionSetupUrl,
                                id: 'elementsFrame',
                                style: 'width:100%;height:487px;display:block;border:none;',
                                load: function() {
                                    $('body').trigger('processStop');
                                }
                            });
                            $('#show-elements-iframe').append(ifr);
                            return true;
                        } else {
                            $('body').trigger('processStop');
                            iframeself.showElementsError(obj.debug.tokenInfo.error);
                        }
                    } else {
                        $('body').trigger('processStop');
                        iframeself.showElementsError("Error occured while setting up Card payment.Please use another payment method");
                    }
                });
            },
            successpost: function() {
                $('body').trigger('processStart');
                var iframeself = this;
                var serviceUrl ='customerconnect/arpayments/arplaceorderpost';
                $("#order-buttons-container").hide();
                var payload = {
                    orderplaced: true,
                    success: true
                };
                storage.post(
                    serviceUrl,
                    payload,
                    false,
                    'application/x-www-form-urlencoded; charset=UTF-8'                    
                ).done(function(response) {
                    var json = $.parseJSON(response);
                    if(!json.error) {
                       var baseurl = $("#baseUrl").val();
                       window.location.href =  baseurl + "customerconnect/arpayments/Successorder";
                    } else {
                        $("#order-buttons-container").show();
                        $('body').trigger('processStop');
                        alerts({
                            content: $t('Error')
                        });                        
                    }
                }).fail(function(response) {
                    $("#order-buttons-container").show();
                    alerts({
                        content: $t('Error occured while setting up Card payment.Please use another payment method')
                    });
                    $('body').trigger('processStop');
                }); 
            }            
        };
    });