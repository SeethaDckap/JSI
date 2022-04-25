/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define([
    'jquery',
    'uiComponent',
    'ko'
], function ($, Component, ko) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Epicor_Elasticsearch/autosuggest',
            showAutoSuggestBox: ko.observable(false),
            aggregatedResultCount: ko.observable(false),
            showRecentSearch: ko.observable(false),
            showHotSearch: ko.observable(false),
            result: {
                products: {
                    data: ko.observableArray([])
                },
                seeall: {
                    data: ko.observableArray([])
                },
                didyoumean: {
                    data: ko.observableArray([])
                },
                category: {
                    data: ko.observableArray([])
                },
                cmspages: {
                    data: ko.observableArray([])
                }
            },
            prodsResultCount: false,
            dymResultCount: false,
            catResultCount: false,
            pagesResultCount: false,
            seeallUrl: "#",
            seeallTitle: 'See All'
        },
        initialize: function () {
            let self = this;
            this._super();
            this.prodsResultCount = ko.computed(function () {
                let prodCount = self.result.products.data().length;
                if (prodCount > 0) {
                    return true; }
                return false;
            }, this);

            this.dymResultCount = ko.computed(function () {
                let dymCount = self.result.didyoumean.data().length;
                if (dymCount > 0) {
                    return true; }
                return false;
            }, this);

            this.catResultCount = ko.computed(function () {
                let catCount = self.result.category.data().length;
                if (catCount > 0) {
                    return true; }
                return false;
            }, this);

            this.pagesResultCount = ko.computed(function () {
                let pagesCount = self.result.cmspages.data().length;
                if (pagesCount > 0) {
                    return true; }
                return false;
            }, this);

            this.seeallUrl = ko.computed(function () {
                if (self.result.seeall.data().length > 0) {
                    return self.result.seeall.data()[0].seeallurl;
                }
                return;
            }, this);

            this.seeallTitle = ko.computed(function () {
                if (self.result.seeall.data().length > 0) {
                    return self.result.seeall.data()[0].seeallcount;
                }
                return;
            }, this);
        }
    });
});