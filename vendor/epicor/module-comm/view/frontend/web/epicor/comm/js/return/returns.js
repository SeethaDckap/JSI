/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

define([
    'jquery',
    'prototype'
], function (jQuery) {
var Returns = Class.create();
Returns.prototype = {
    initialize: function (accordion, urls) {
        this.accordion = accordion;
        this.method = '';
        this.payment = '';
        this.loadWaiting = false;
        this.steps = ['login', 'return', 'products', 'attachments', 'notes', 'review'];
        this.currentStep = 'return';
        this.saveUrl = urls.saveMethod;
        this.failureUrl = urls.failure;

        this.accordion.sections.each(function (section) {
            Event.observe($(section).down('.step-title'), 'click', this._onSectionClick.bindAsEventListener(this));
        }.bind(this));

        this.accordion.disallowAccessToNextSections = true;
    },
    /**
     * Section header click handler
     *
     * @param event
     */
    _onSectionClick: function (event) {
        var section = $(Event.element(event).up().up());
        if (section.hasClassName('allow')) {
            Event.stop(event);
            this.gotoSection(section.readAttribute('id').replace('returns-', ''), false);
            return false;
        }
    },
    ajaxFailure: function () {
        location.href = this.failureUrl;
    },
    _disableEnableAll: function (element, isDisabled) {
        var descendants = element.descendants();
        for (var k in descendants) {
            descendants[k].disabled = isDisabled;
        }
        element.disabled = isDisabled;
    },
    setLoadWaiting: function (step, keepDisabled) {
        if (step) {
            if (this.loadWaiting) {
                this.setLoadWaiting(false);
            }
            var container = $(step + '-buttons-container');
            container.addClassName('disabled');
            container.setStyle({opacity: .5});
            this._disableEnableAll(container, true);
            Element.show(step + '-please-wait');
        } else {
            if (this.loadWaiting) {
                var container = $(this.loadWaiting + '-buttons-container');
                var isDisabled = (keepDisabled ? true : false);
                if (!isDisabled) {
                    container.removeClassName('disabled');
                    container.setStyle({opacity: 1});
                }
                this._disableEnableAll(container, isDisabled);
                Element.hide(this.loadWaiting + '-please-wait');
            }
        }
        this.loadWaiting = step;
    },
    gotoSection: function (section) {

        this.currentStep = section;
        var sectionElement = $('returns-' + section);
        sectionElement.addClassName('allow');
        this.accordion.openSection('returns-' + section);
    },
    changeSection: function (section) {
        var changeStep = section.replace('returns-', '');
        this.gotoSection(changeStep, false);
    },
    removeSection: function (section) {
        if ($('returns-' + section)) {
            number = parseInt($('returns-' + section).down('.step-title span.number').innerHTML);
            $('returns-' + section).remove();
            $$('.opc .section').each(function (e) {
                elNumber = parseInt(e.down('.step-title span.number').innerHTML);
                if (number < elNumber) {
                    elNumber = elNumber - 1;
                    e.down('.step-title span.number').update(elNumber);
                }
            });
        }
    },
    back: function () {
        if (this.loadWaiting)
            return;
        //Navigate back to the previous available step
        var stepIndex = this.steps.indexOf(this.currentStep);
        var section = this.steps[--stepIndex];
        var sectionElement = $('returns-' + section);

        //Traverse back to find the available section. Ex Virtual product does not have shipping section
        while (sectionElement === null && stepIndex > 0) {
            --stepIndex;
            section = this.steps[stepIndex];
            sectionElement = $('returns-' + section);
        }
        this.changeSection('returns-' + section);
    },
    setStepResponse: function (response) {

        if (response.errors) {
            errorMessage = '';
            join = '';
            for (var i = 0; i < response.errors.length; i++) {
                errorMessage += join + response.errors[i];
                join = '\n';
            }
            alert(errorMessage);
            //remove the load waiting sign and reinstate the submit button if required
            tab = response.tab;
            if(tab){
                this.loadWaiting = tab; 
                this.setLoadWaiting(false);  
                if ($(tab + '-submit')) {
                    $(tab + '-submit').show();
                }                
            }else{
                return false;                
            }
            
        }


        if (response.refresh_section) {
            $('returns-step-' + response.refresh_section.name).update(response.refresh_section.html);
        }

        if (response.update_section) {
            $('returns-step-' + response.update_section.name).update(response.update_section.html);
        }

        if (response.allow_sections) {
            response.allow_sections.each(function (e) {
                $('returns-' + e).addClassName('allow');
            });
        }

        if (response.remove_section) {
            this.removeSection(response.remove_section);
        }

        if (response.goto_section) {
            this.gotoSection(response.goto_section, true);
            return true;
        }

        if (response.redirect) {
            location.href = response.redirect;
            return true;
        }
        return false;
    }
}

  return Returns;
  
});