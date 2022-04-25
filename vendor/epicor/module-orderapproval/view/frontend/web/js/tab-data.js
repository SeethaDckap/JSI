/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define([
    'jquery'
], function ($) {
    'use strict';
    return {
        customerTabOpened: 0,
        rulesTabOpened: 0,
        budgetsTabOpened: 0,
        hierarchyTabOpened: 0,
        primaryTabOpened: 1,
        tabTypes: {
            'customer': {
                'tabSelector': '#customers-tab',
                'dataSelector': '#customer_grid'
            },
            'rules': {
                'tabSelector': '#rules-tab',
                'dataSelector': '#rules_grid'
            },
            'budgets': {
                'tabSelector': '#budgets-tab',
                'dataSelector': '#budgets_grid'
            },
            'hierarchy': {
                'tabSelector': '#hierarchy-tab',
                'dataSelector': '#hierarchy_grid'
            },
            'primary': {
                'tabSelector': '#primary_details',
                'dataSelector': '#primary_detail_content'
            }
        },
        tabDataUrl: '',
        getTab: function (type, url = '') {
            this.tabDataUrl = url;
            this.switchTab(this.getTabSelector(type));
            this.hideOtherTabs(type);
            if (!this.isTabOpened(type)) {
                this.updateSectionData(type);
                this.setTabOpened(type);
            } else {
                $(this.getDataSelector(type)).show();
            }
        },
        getTabSelector: function (type) {
            return this.tabTypes[type].tabSelector;
        },
        getDataSelector: function (type) {
            return this.tabTypes[type].dataSelector;
        },
        hideOtherTabs: function (type) {
            let configSection = $('fieldset#budget_config_form');
            switch(type) {
                case 'customer':
                    $('#primary_detail_content').hide();
                    $('#hierarchy_grid').hide();
                    $('#rules_grid').hide();
                    $('#budgets_grid').hide();
                    configSection.hide();
                    break;
                case 'rules':
                    $('#primary_detail_content').hide();
                    $('#hierarchy_grid').hide();
                    $('#customer_grid').hide();
                    $('#budgets_grid').hide();
                    configSection.hide();
                    break;
                case 'budgets':
                    $('#primary_detail_content').hide();
                    $('#hierarchy_grid').hide();
                    $('#customer_grid').hide();
                    $('#rules_grid').hide();
                    break;
                case 'hierarchy':
                    $('#rules_grid').hide();
                    $('#primary_detail_content').hide();
                    $('#customer_grid').hide();
                    $('#budgets_grid').hide();
                    configSection.hide();
                    break;
                case 'primary':
                    $('#rules_grid').hide();
                    $('#hierarchy_grid').hide();
                    $('#customer_grid').hide();
                    $('#budgets_grid').hide();
                    configSection.hide();
                    break;
                default:
                    return false;
            }
        },
        switchTab: function (tabSelector) {
            let mainTabs = $('ul.toggle-tabs');
            let currentTab = mainTabs.find('li.current');
            currentTab.removeClass('current');
            let customerTab = $(tabSelector);
            customerTab.addClass('current');
            customerTab.show();
        },
        isTabOpened: function (type) {
            switch(type) {
                case 'customer':
                    return this.customerTabOpened === 1;
                case 'rules':
                    return this.rulesTabOpened === 1;
                case 'budgets':
                    return this.budgetsTabOpened === 1;
                case 'hierarchy':
                    return this.hierarchyTabOpened === 1;
                case 'primary':
                    return this.primaryTabOpened === 1;
                default:
                    return false;
            }
        },
        setTabOpened: function(type){
            switch(type) {
                case 'customer':
                    this.customerTabOpened = 1;
                    break;
                case 'rules':
                    this.rulesTabOpened = 1;
                    break;
                case 'budgets':
                    this.budgetsTabOpened = 1;
                    break;
                case 'hierarchy':
                    this.hierarchyTabOpened = 1;
                    break;
                default:
            }
        },
        updateSectionData: function (type) {
            $.ajax({
                url: this.tabDataUrl,
                type: "POST",
                showLoader: true,
            }).done(function (data) {
                $('#please-wait').hide();
                let tabContent = $(this.getDataSelector(type));
                tabContent.show();
                tabContent.html(data);
                $('body').loader('hide');
            }.bind(this));
        }
    };
});
