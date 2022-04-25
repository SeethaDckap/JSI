/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery',
    'prototype',
    'mage/validation'
], function (jQuery,Returns) {
    var TabMaster = Class.create();
    TabMaster.prototype = {
        tab: '',
        beforeInitFunc: $H({}),
        afterInitFunc: $H({}),
        beforeValidateFunc: $H({}),
        afterValidateFunc: $H({}),
        beforeSaveFunc: $H({}),
        beforeNextStepFunc: $H({}),
        addBeforeInitFunction: function (code, func) {
            this.beforeInitFunc.set(code, func);
        },
        beforeInit: function () {
            (this.beforeInitFunc).each(function (init) {
                (init.value)();
                ;
            });
        },
        init: function () {
            this.beforeInit();
            var elements = Form.getElements(this.form);
            if ($(this.form)) {
                $(this.form).observe('submit', function (event) {
                    this.save();
                    Event.stop(event);
                }.bind(this));
            }
            for (var i = 0; i < elements.length; i++) {
                elements[i].setAttribute('autocomplete', 'off');
            }
            this.afterInit();
        },
        initialize: function (form) {
            this.form = form;
            if (form != undefined) {
                this.saveUrl = $(form).readAttribute('action');
            }
            this.onSave = this.nextStep.bindAsEventListener(this);
            this.onComplete = this.resetLoadWaiting.bindAsEventListener(this)

        },
        addAfterInitFunction: function (code, func) {
            this.afterInitFunc.set(code, func);
        },
        afterInit: function () {
            (this.afterInitFunc).each(function (init) {
                (init.value)();
            });
        },
        addBeforeValidateFunction: function (code, func) {
            this.beforeValidateFunc.set(code, func);
        },
        beforeValidate: function () {
            var validateResult = true;
            var hasValidation = false;
            (this.beforeValidateFunc).each(function (validate) {
                hasValidation = true;
                if ((validate.value)() == false) {
                    validateResult = false;
                }
            }.bind(this));
            if (!hasValidation) {
                validateResult = false;
            }
            return validateResult;
        },
        validate: function () {
            return true;
        },
        addAfterValidateFunction: function (code, func) {
            this.afterValidateFunc.set(code, func);
        },
        afterValidate: function () {
            var validateResult = true;
            var hasValidation = false;
            (this.afterValidateFunc).each(function (validate) {
                hasValidation = true;
                if ((validate.value)() == false) {
                    validateResult = false;
                }
            }.bind(this));
            if (!hasValidation) {
                validateResult = false;
            }
            return validateResult;
        },
        addBeforeSaveFunction: function (code, func) {
            this.beforeSaveFunc.set(code, func);
        },
        beforeSave: function () {
            (this.beforeSaveFunc).each(function (bSave) {
                (bSave.value)();
            });
        },
        save: function () {

            if (returns.loadWaiting != false) {
                return;
            }

            var validator = new Validation(this.form);
            if (this.validate() && validator.validate()) {
                this.beforeSave();
                returns.setLoadWaiting(this.tab);

                if (this.tab != 'lines') {
                    var request = new Ajax.Request(
                        this.saveUrl,
                        {
                            method: 'post',
                            onComplete: this.onComplete,
                            onSuccess: this.onSave,
                            onFailure: returns.ajaxFailure.bind(returns),
                            parameters: Form.serialize(this.form)
                        }
                    );
                }
            }
        },
        resetLoadWaiting: function () {
            returns.setLoadWaiting(false);
        },
        addBeforeNextStepFunction: function (code, func) {
            this.beforeNextStepFunc.set(code, func);
        },
        beforeNextStep: function (response) {
            (this.beforeNextStepFunc).each(function (bNext) {
                (bNext.value)(response);
            });
        },
        nextStep: function (transport) {
            if (transport && transport.responseText) {
                try {
                    response = eval('(' + transport.responseText + ')');
                }
                catch (e) {
                    response = {};
                }
            }

            this.beforeNextStep(response);

            /*
             * if there is an error in payment, need to show error message
             */
            if (response.errors) {
                errorMessage = '';
                join = '';
                for (var i = 0; i < response.errors.length; i++) {
                    errorMessage += join + response.errors[i];
                    join = '\n';
                }
                alert(errorMessage);
                return;
            }

            returns.setStepResponse(response);
        }
    }

  return TabMaster;
});
