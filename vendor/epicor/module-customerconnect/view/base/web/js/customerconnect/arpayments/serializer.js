require([
        'prototype',
        'mage/adminhtml/grid'
    ],
    function() {
        serializerController.prototype.rowInit = function(grid, row) {
            var checkbox, selectors, inputs, i;
            if (this.multidimensionalMode) {
                checkbox = $(row).select('.checkbox')[0];
                selectors = this.inputsToManage.map(function(name) {
                    return ['input[name=\"' + name + '\"]', 'select[name=\"' + name + '\"]'];
                });
                inputs = $(row).select.apply($(row), selectors.flatten());
                if (checkbox && inputs.length > 0) {
                    checkbox.inputElements = inputs;
                    /* eslint-disable max-depth */
                    for (i = 0; i < inputs.length; i++) {
                        inputs[i].checkboxElement = checkbox;
                        if (this.gridData.get(checkbox.value) && this.gridData.get(checkbox.value)[inputs[i].name]) {
                            inputs[i].value = this.gridData.get(checkbox.value)[inputs[i].name];
                        }
                        inputs[i].disabled = false;
                        inputs[i].tabIndex = this.tabIndex++;
                        Event.observe(inputs[i], 'keyup', this.inputChange.bind(this));
                        Event.observe(inputs[i], 'change', this.inputChange.bind(this));
                    }
                }
            }

            /* eslint-enable max-depth */
            this.getOldCallback('init_row')(grid, row);
        };
        serializerController.prototype.rowClick = serializerController.prototype.rowClick.wrap(function(o, grid, event) {
            var tagName = Event.element(event).tagName;
            //Ignore this conditions for AR Payments
            isSelect = (tagName == 'DIV' || tagName == 'TEXTAREA' || tagName == 'SELECT' || tagName == 'OPTION' || tagName == 'TD' || tagName =='A' || tagName =='SPAN');
            if (!isSelect) {
                o(grid, event);
            }
        });
        serializerController.prototype.registerData = function(grid, element, checked) {
            var i;
            if (this.multidimensionalMode) {
                if (checked) {
                    /*eslint-disable max-depth*/
                    if (element.inputElements) {
                        this.gridData.set(element.value, {});

                        for (i = 0; i < element.inputElements.length; i++) {
                            element.inputElements[i].disabled = false;
                            this.gridData.get(element.value)[element.inputElements[i].name] =
                                element.inputElements[i].value;
                        }
                    }
                } else {
                    if (element.inputElements) {
                        for (i = 0; i < element.inputElements.length; i++) {
                            element.inputElements[i].disabled = false;
                        }
                    }
                    this.gridData.unset(element.value);
                }
            } else {
                if (checked) { //eslint-disable-line no-lonely-if
                    this.gridData.set(element.value, element.value);
                } else {
                    this.gridData.unset(element.value);
                }
            }
            this.hiddenDataHolder.value = this.serializeObject();
            this.grid.reloadParams = {};
            this.grid.reloadParams[this.reloadParamName + '[]'] = this.getDataForReloadParam();
            this.getOldCallback('checkbox_check')(grid, element, checked);
        };
    });