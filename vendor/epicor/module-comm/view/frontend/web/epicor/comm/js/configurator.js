/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
require([
    'jquery',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/confirm',
    'mage/translate'
], function($, modal, confirmation, $tr) {
    window.ewaProduct = {
        loaded: false,
        ewcproduct: false,
        errorVals: false,
        live: true,
        debugData: {
            data: '{"width":1024,"height":798}'
        },
        debugEwcData: {
            data: '{"width":1024,"height":798}'
        },
        ewaLoadTimeout: 60000,
        badUrlTimeout: 500000,
        badUrlTimer: null,
        errorTimer: null,
        prefix: '',
        warningMessage: $tr('Your changes will be lost if you close. Click OK if you are you sure you want to close without saving.'),
        submit: function(data, returnurl) {
            data['action'] = 'load';
            var ewaUrl = this.buildEwaUrl(this.prefix + '/eccProcessEwa.php', data);
            this.buildWindow(ewaUrl, returnurl);
        },
        edit: function(data, returnurl) {
            data['action'] = 'edit';
            var ewaUrl = this.buildEwaUrl(this.prefix + '/eccProcessEwa.php', data);
            this.buildWindow(ewaUrl, returnurl);
        },
        buildEwaUrl: function(ewaUrl, data) {
            i = 1;
            for (var key in data) {
                if (data[key] != undefined && data[key] != '' && data[key] != 0) {
                    if (i == 1)
                        ewaUrl += '?' + key + '=' + data[key];
                    else
                        ewaUrl += '&' + key + '=' + data[key];
                }
                i++;
            }
            var address = this.getAddress();
            if (address) {
                // ewaUrl += '/address/' + encodeURIComponent(Object.toJSON(address));
                for (var key in address) {
                    if (address[key] != undefined && address[key] != '' && address[key] != 0) {
                        ewaUrl += '&' + key + '=' + address[key];
                    }

                }
            }
            return ewaUrl;
        },
        buildWindow: function(url, returnurl, error) {
            if (error === undefined) {
                error = false;
            }
            var self = this;
            this.loaded = false;
            // create iFrame
            if ($('#ewaWrapper').length) {
                $('#ewaWrapper').remove();
            }
            if (returnurl) {
                url = url + '&return=' + returnurl;
            }
            
            var getProductType = self.getQueryStringParams('type',url);
            if(getProductType =="K") {
                var myIframe = $('<iframe id="ewaIframe">').prop({
                    src: url,
                    scrolling: 'yes',
                    frameborder: 0
                });     
                ewaProduct.ewcproduct = true;
            } else {
                var myIframe = $('<iframe id="ewaIframe">').prop({
                    src: url,
                    scrolling: 'no',
                    frameborder: 0
                });         
                ewaProduct.ewcproduct = false;
            }
            $('body').append("<div id='ewaWrapper'><div id='show-configurator-iframe'><div id='ewaWrapperContent'></div></div></div>");
            this.createConfiguratorPopup(error);
        //    $('body').loader('show');
            $('body').trigger('processStart');
            $('#ewaWrapperContent').append(myIframe);
            if (!error) {
                $('#ewaIframe').on('load', function(){
                    $('body').loader('hide');
                    clearTimeout(self.badUrlTimer);
                    clearTimeout(self.errorTimer);
                });
            } else {
                $('body').loader('hide');
                self.badUrlTimer = setTimeout(self.badUrl(), self.badUrlTimeout);
            }         
        },
        getQueryStringParams: function(sParam,sPageURL) {
            var sURLVariables = sPageURL.split('&');
             for (var i = 0; i < sURLVariables.length; i++) {
               var sParameterName = sURLVariables[i].split('=');
               if (sParameterName[0] == sParam)  {
                   return sParameterName[1];
               }
             }
        },        
        alertWarningmsg: function() {
            var self = this;
            confirmation({
                content: self.warningMessage,
                title: 'Warning',
                modalClass: 'confirm configuratorconfirmpopup',
                clickableOverlay: false,
                actions: {
                    confirm: function() {
                        self.confirmclosepopup();
                    },
                    cancel: function() {},
                    always: function() {}
                }
            });
        },
        confirmclosepopup: function() {
            var self = this;
            clearTimeout(self.badUrlTimer);
            clearTimeout(self.errorTimer);
            if ($('.configuratorpopup').length) {
                $('body').removeClass('_has-modal');
                $('.modals-overlay').remove();
                $('.configuratorpopup').remove();
            }
        },
        onClosePopUp: function() {
            if ($('.configuratorconfirmpopup').length) {
                $('.configuratorconfirmpopup').remove();
            }
            var self = this;
            var warning = self.errorVals;
            if (!warning) {
                return self.alertWarningmsg();
            }
            self.confirmclosepopup();
        },
        closepopup: function(idVals) {
            var self = this;
            self.confirmclosepopup();
        },
        createConfiguratorPopup: function(errorMsg) {
            this.errorVals = errorMsg;
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                clickableOverlay: false,
                modalClass: 'configuratorpopup',

            };
            options.modalCloseBtnHandler = this.onClosePopUp.bind(this);
            options.keyEventHandlers = {
                escapeKey: function() {
                    return;
                }
            };
            var popup = modal(options, $('#ewaWrapper'));
            $('#ewaWrapper').modal('openModal');
            $('.modal-footer').hide();
            return popup;
        },
        getQueryStringParams: function(sParam, sPageURL) {
            var sURLVariables = sPageURL.split('&');
            for (var i = 0; i < sURLVariables.length; i++) {
                var sParameterName = sURLVariables[i].split('=');
                if (sParameterName[0] == sParam) {
                    return sParameterName[1];
                }
            }
        },
        badUrl: function() {
            if (!this.loaded) {
                if (this.live) {
                    this.closepopup('ewaWrapper');
                    $('body').loader('hide');
                    this.buildWindow(this.prefix + '/comm/configurator/badurl', undefined, true);
                } else {
                    this.onMessage(this.debugData);
                }
            }
        },
        redirect: function(url) {
            this.closepopup('ewaWrapper');
            if (url == '')
                location.reload();
            else
                location.replace(url);
        },
        autoreveal: function() {
            if (!this.loaded) {
                if (this.live) {
                    this.closepopup('ewaWrapper');
                    this.buildWindow(this.prefix + '/comm/configurator/error', undefined, true);
                } else if (!this.live) {
                    this.onMessage(this.debugData);
                }
            }
        },
        onMessage: function(event) {
            try {
                var message = JSON.parse(event.data);
            } catch (e) {
                if($.fn.loader !== undefined) {
                    $('body').loader('hide');
                }
                return false;
            }
            clearTimeout(ewaProduct.badUrlTimer);
            clearTimeout(ewaProduct.errorTimer);
            this.loaded = true;
            $('body').loader('hide');
            var padding = 30;
            var border = 2;
            var closelink = 20;
            var ewaWidth = message.width * 1;
            var ewaHeight = message.height * 1;
            var maxWidth = window.innerWidth - 40;
            var maxHeight = window.innerHeight - 40;
            var wrapperWidth = Math.min(maxWidth - padding - border, ewaWidth);
            var wrapperHeight = Math.min(maxHeight - padding - border, ewaHeight + closelink);
            var iframeWidth = Math.min(maxWidth - padding - border, ewaWidth);
            var iframeHeight = Math.min(maxHeight - padding - border - closelink, ewaHeight);
            // $('#ewaWrapperContent').height(wrapperHeight + 100);
            // $('#ewaWrapperContent').width(wrapperWidth + 50);
            if(ewaProduct.ewcproduct) {
                $('.configuratorpopup iframe').height(wrapperHeight);
                $('.configuratorpopup div.modal-inner-wrap').css('max-height',"100%");
                $('.configuratorpopup div.modal-inner-wrap').css('margin',"0rem auto");
                $(".configuratorpopup div.modal-content").css("overflow","hidden");
            } else {
                $('.configuratorpopup iframe').height(iframeHeight + 50);
            }
            $('.configuratorpopup div.modal-inner-wrap').height(wrapperHeight + 100);
            $('.configuratorpopup div.modal-inner-wrap').width(wrapperWidth + 50);    
            $('.configuratorpopup iframe').width(iframeWidth);
        },
        getAddress: function() {
            var content = $('#delivery-address-content');
            if (content.length) {
                var serialized = content.find("select, textarea, input").serializeArray();
                var address = {};
                $.each(serialized, function() {
                    var newIndex = this.name.replace('delivery_address[', '').replace(']', '');
                    if (newIndex != 'old_data') {
                        address[newIndex] = this.value;
                    }
                });
                var contactName = '';
                var nameColumn = $('#rfq_contacts_table .contacts_row:visible .col-name').first();
                if (nameColumn.length) {
                    if (nameColumn.find('select').length) {
                        contactName = nameColumn.find('select option:selected').first().text();
                    } else {
                        contactName = nameColumn.html().toString().trim();
                    }
                }
                address.contact_name = contactName;
                return address;
            } else {
                return false;
            }
        }
    };
    $(document).ready(function() {
        if (window.attachEvent) {
            window.attachEvent('onmessage', ewaProduct.onMessage);
        } else if (window.addEventListener) {
            window.addEventListener('message', ewaProduct.onMessage, false);
        }
    });

});