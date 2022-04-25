define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'underscore',
        'Magento_Checkout/js/model/step-navigator',
        'Magento_Customer/js/model/customer',
        'uiLayout',
        'mageUtils',
        'Epicor_BranchPickup/js/epicor/model/address-list',
        'Epicor_BranchPickup/js/epicor/model/save-branch-information',
        'Magento_Ui/js/modal/alert',
        'Magento_Checkout/js/model/full-screen-loader',
        'Magento_Checkout/js/action/select-shipping-address',
        'Epicor_BranchPickup/js/epicor/model/branch-common-utils',
        'Magento_Ui/js/modal/modal',
        'mage/url',
        'Epicor_SalesRep/epicor/salesrep/js/model/contact',
        'mage/validation'
    ],
    function(
        $,
        ko,
        Component,
        _,
        stepNavigator,
        customer,
        layout,
        utils,
        addressList,
        saveBranchInformation,
        alertbox,
        fullScreenLoader,
        selectShippingAddressAction,
        Branchutils,
        modal,
        url,
        contact
    ) {
        var steps = ko.observableArray();

        'use strict';
        /**
         * Branch Pickup Address Template 
         */
        var defaultRendererTemplate = {
            parent: '${ $.$data.parentName }',
            name: '${ $.$data.name }',
            component: 'Epicor_BranchPickup/js/epicor/view/shipping-address/address-renderer/default'
        };
        return Component.extend({
            defaults: {
                template: 'Epicor_BranchPickup/shipping-address/list',
                visibleAddress: addressList().length > 0,
                rendererTemplates: [],
                inventoryView: window.checkoutConfig.inventoryView
            },
            steps: steps,
            stepCodes: [],
            //add here your logic to display step,
            isVisible: ko.observable(true),
            isLogedIn: customer.isLoggedIn(),
            //step code will be used as step content id in the component template
            stepCode: 'branch-pickup-container',
            //step title value
            stepTitle: 'Branch Pickup',

            /**
             *
             * @returns {*}
             */
            initialize: function() {
                this._super()
                    .initChildren();
                var visi = this.isVisible();
                var self = this;
                steps.push({
                    code: 'branch-pickup-container',
                    alias: 'branch-pickup-container',
                    title: 'branch-pickup-container',
                    isVisible: visi,
                    navigate: null,
                    sortOrder: 1
                });
                var hash = window.location.hash.replace('#', '');
                this.stepCodes.push('branch-pickup-container');
                addressList.subscribe(
                    function(changes) {
                        var self = this;
                        changes.forEach(function(change) {
                            if (change.status === 'added') {
                                self.createRendererComponent(change.value, change.index);
                            }
                        });
                    },
                    this,
                    'arrayChange'
                );
                if (hash == "payment") {
                    self.isVisible(false);
                }
                return this;
            },
            initConfig: function() {
                this._super();
                // the list of child components that are responsible for address rendering
                this.rendererComponents = [];
                return this;
            },

            initChildren: function() {
                _.each(addressList(), this.createRendererComponent, this);
                return this;
            },

            /**
             * The navigate() method is responsible for navigation between checkout step
             * during checkout. You can add custom logic, for example some conditions
             * for switching to your custom step
             */
            navigate: function() {
                this.isVisible(true);
            },
            /**
             * Set shipping information handler
             */
            setShippingInformation: function() {
                
                
                if(window.checkoutConfig.isSalesRep && window.checkoutConfig.isSalesRepContactReq){
                    if (!contact.selectedcontact()) {
                        alertbox({
                            title: 'Error',
                            content: 'Please Choose a Contact.'
                        });
                        return false;
                    }
                }
                
                //if (this.validateShippingInformation()) {
                var locationCode = this.branchPickupValidation();
                var isCustomerLoggedin = window.isCustomerLoggedIn;
                if (!isCustomerLoggedin) {
                    var validateFields = this.validateFields();
                    if (!validateFields) {
                        return false;
                    }
                }
                if (locationCode) {
                    var self = this;
                    fullScreenLoader.startLoader();
                    $.ajax({
                        showLoader: false,
                        data: {
                            locationcode: locationCode
                        },
                        url: url.build('branchpickup/pickup/changepickuplocation'),
                        type: "POST",
                    }).done(function(data) {
                        fullScreenLoader.stopLoader();
                        var jsonData = data.details;
                        saveBranchInformation.saveShippingInformation(locationCode, jsonData).done(
                            function() {
                                stepNavigator.next();
                            }
                        );
                        $('#branch-pickup-container').hide();
                        self.isVisible(false);
                    });

                } else {
                    alertbox({
                        title: 'Error',
                        content: 'Please select a location'
                    });
                }
                //}
            },
            validateFields: function() {
                var checkEmail = $('#bcustomer-email').val();
                var getEmail = this.validateEmail(checkEmail);
                var getFname = $('input[name="firstname"]').val();
                var getLname = $('input[name="lastname"]').val();
                if (!getEmail) {
                    $('#bcustomer-email').focus();
                } else if (!getFname) {
                    $('input[name="firstname').focus();
                } else if (!getLname) {
                    $('input[name="lastname').focus();
                }
                if (getEmail) {
                    $('#bcustomer-email-error').hide();
                }
                if (!getEmail || !getFname || !getLname) {
                    alertbox({
                        title: 'Error',
                        content: 'Please Fill the Required Fields'
                    });
                    return false;
                } else {
                    return true;
                }
            },
            validateEmail: function(sEmail) {
                var filter = /^([\w-\.]+)@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.)|(([\w-]+\.)+))([a-zA-Z]{2,4}|[0-9]{1,3})(\]?)$/;
                if (filter.test(sEmail)) {
                    return true;
                } else {
                    return false;
                }
            },
            branchPickupValidation: function() {
                var locationCode = '';
                var containter = $("#branchpickup-addresses").find("div.selected-branchpickup-item");
                if (containter) {
                    locationCode = $('.selected-branchpickup-item').attr('data-custom');
                }
                return locationCode;
            },

            /**
             * Create new component that will render given address in the address list
             *
             * @param address
             * @param index
             */
            createRendererComponent: function(branchaddress, index) {
                if (index in this.rendererComponents) {
                    this.rendererComponents[index].branchaddress(branchaddress);
                } else {
                    // rendererTemplates are provided via layout
                    var rendererTemplate = (branchaddress.getType() != undefined && this.rendererTemplates[branchaddress.getType()] != undefined) ?
                        utils.extend({}, defaultRendererTemplate, this.rendererTemplates[branchaddress.getType()]) :
                        defaultRendererTemplate;
                    var templateData = {
                        parentName: this.name,
                        name: index
                    };
                    var rendererComponent = utils.template(rendererTemplate, templateData);
                    utils.extend(rendererComponent, {
                        branchaddress: ko.observable(branchaddress)
                    });
                    layout([rendererComponent]);
                    this.rendererComponents[index] = rendererComponent;
                }
            },
            checkBranchPickupSelected: function() {
                var shippingAddress = window.checkoutConfig.selectedBranch;
                if(!shippingAddress) {
                    shippingAddress =false;
                }                
                return shippingAddress;
            },
            showShippingAddress: function() {
                //var address = window.checkoutConfig.defaultShippingAddress;
                //alert(console.log(address))
                //selectShippingAddressAction(address);
                saveBranchInformation.resetAddress();
                //$('#checkout-step-shipping').find('div[class="shipping-address-item not-selected-item"]').trigger('click');
                //$('[class="action action-select-shipping-item"]:first-child').trigger('click');
                Branchutils.showShippingAddress();
                return true;
            },
            showBranchSearchPopup: function() {
                fullScreenLoader.startLoader();
                if ($('#branch-grid-loader-popup-modal').length) {
                    $('#branch-grid-loader-popup-modal').remove();
                    $('#branch-grid-loader-popup-showmodal').remove();
                }
                var options = {
                    type: 'popup',
                    responsive: false,
                    innerScroll: false,
                    title: 'Select Branch'
                };
                $("#branch-grid-loader").append("<div id='branch-grid-loader-popup-modal'></div>");
                $("#branch-grid-loader-popup-modal").append("<div id='branch-grid-loader-popup-showmodal'></div>");
                var popup = modal(options, $('#branch-grid-loader-popup-modal'));
                $('#branch-grid-loader-popup-modal').modal('openModal');
                var ifr = $('<iframe/>', {
                    src: url.build('branchpickup/pickup/pickupsearch'),
                    id: 'branchpopupiframe',
                    style: 'width:775px;height:614px;display:block;border:none;',
                    load: function() {
                        fullScreenLoader.stopLoader();
                    }
                });
                $('#branch-grid-loader-popup-showmodal').append(ifr);
                //$('#shipping-grid-loader-popup-showmodal').append(data);
                $('.modal-footer').hide();

            },
            showBranchSelectorPopup: function() {
                fullScreenLoader.startLoader();
                if ($('#branch-grid-loader-popup-modal').length) {
                    $('#branch-grid-loader-popup-modal').remove();
                    $('#branch-grid-loader-popup-showmodal').remove();
                }
                var options = {
                    type: 'popup',
                    responsive: false,
                    innerScroll: false,
                    title: 'Select Branch'
                };
                $("#branch-grid-loader").append("<div id='branch-grid-loader-popup-modal'></div>");
                $("#branch-grid-loader-popup-modal").append("<div id='branch-grid-loader-popup-showmodal'></div>");
                var popup = modal(options, $('#branch-grid-loader-popup-modal'));
                $('#branch-grid-loader-popup-modal').modal('openModal');
                var ifr = $('<iframe/>', {
                    src: url.build('branchpickup/pickup/pickupselector'),
                    id: 'branchpopupiframe',
                    style: 'width:775px;height:614px;display:block;border:none;',
                    load: function() {
                        fullScreenLoader.stopLoader();
                    }
                });
                $('#branch-grid-loader-popup-showmodal').append(ifr);
                //$('#shipping-grid-loader-popup-showmodal').append(data);
                $('.modal-footer').hide();

            },
            showBranchPickupAddress: function() {
                Branchutils.showBranchPickupAddress();
                return true;
            }
        });
    }
);