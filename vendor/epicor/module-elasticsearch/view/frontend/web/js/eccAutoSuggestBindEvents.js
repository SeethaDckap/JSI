/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define([
    'jquery',
    'uiComponent',
    'uiRegistry',
    'mageUtils'
], function ($, Component, registry, utils) {
    'use strict';
    return Component.extend({
        initialize: function () {
            this._super();
            utils.limit(this, 'load', 300);
            $(this.searchTermInputSelector)
                .unbind('input')
                .on('input', $.proxy(this.load, this))
                .on('input', $.proxy(this.searchTermButtonStatus, this))
                .on('focus', $.proxy(this.showAutoSuggestBox, this));
            $(document).on('click', $.proxy(this.hideAutoSuggestBox, this));
            $(document).ready($.proxy(this.searchTermButtonStatus, this));
        },

        load: function (event) {
            let self = this;
            let searchTerm = $(self.searchTermInputSelector).val();
            if (searchTerm.length < self.searchTermMinLength) {
                return false;
            }
            registry.get('eccAutoSuggestDataProvider', function (dataProvider) {
                dataProvider.searchTerm = searchTerm;
                dataProvider.load();
            });
        },

        searchTermButtonStatus: function (event) {
            let self = this,
                searchTermField = $(self.searchTermInputSelector),
                searchTermButton = $(self.searchTermFormSelector + ' ' + self.searchTermButtonSelector),
                searchTermButtonDisabled = (searchTermField.val().length > 0) ? false : true;

            searchTermButton.attr('disabled', searchTermButtonDisabled);
        },

        showAutoSuggestBox: function (event) {
            var self = this,
                searchTermField = $(self.searchTermInputSelector),
                searchTermFieldHasFocus = searchTermField.is(':focus');
            self.aggregatedResultCount();
            self.showHotSearch();
            self.showRecentSearch();
            registry.get('eccAutoSuggestForm', function (autosuggest) {
                if(searchTermField.val().length == 0 && autosuggest.showAutoSuggestBox() === true) {
                    autosuggest.aggregatedResultCount(false);
                    autosuggest.result.products.data([]);
                    autosuggest.result.didyoumean.data([]);
                    autosuggest.result.category.data([]);
                    autosuggest.result.cmspages.data([]);
                    if (!autosuggest.showRecentSearch() && !autosuggest.showHotSearch()) {
                        autosuggest.showAutoSuggestBox(false);
                    }
                } else {
                    let showauto,
                        showAuto = searchTermFieldHasFocus
                            && (autosuggest.prodsResultCount()
                                || autosuggest.catResultCount()
                                || autosuggest.dymResultCount()
                                || autosuggest.pagesResultCount()
                                || autosuggest.showRecentSearch()
                                || autosuggest.showHotSearch()
                                || autosuggest.aggregatedResultCount()
                            );
                    autosuggest.showAutoSuggestBox(showAuto);
                }
            });

            var pos = $("#search_mini_form #search").position();
            var winwid = $(window).width();
            var pos1 = pos.top + 50;

            if (winwid < 767){
                $("#ecc-autosuggest").css("top", pos1+'px');
            }

			   $(".elsrchcol2").css("margin-right","0px");
				var srcsecactiveclass = $(".srcsecactive").length;
				if (srcsecactiveclass == 1){
					$(".recsearch").addClass("recautoheight");
				}
				else {
					$(".recsearch").removeClass("recautoheight");
				}
            $("#search_mini_form #search").blur(function(){
                var elsrc = $("#ecc-autosuggest").css("display");
                if(elsrc == "block" || elsrc == "inline-block"){
                    $(this).addClass("activetxtbox");
                } else {
                    $(this).val("");
                    $("#search_mini_form .action.search").attr("disabled", "disabled");
                    $(this).removeClass("activetxtbox");
                }
            });
        },

        hideAutoSuggestBox: function (event) {
            if ($(this.searchTermFormSelector).has($(event.target)).length <= 0) {
                registry.get('eccAutoSuggestForm', function (autosuggest) {
                    autosuggest.showAutoSuggestBox(false);
                    autosuggest.result.products.data([]);
                    autosuggest.result.didyoumean.data([]);
                    autosuggest.result.category.data([]);
                    autosuggest.result.cmspages.data([]);
                    $(".elsrctwcol").removeClass("elsrctwcol");
                    $(".elsrcthrcol").removeClass("elsrcthrcol");
                    $(".elsrchcol2col2").removeClass("elsrchcol2col2");
                    $(".thcolfirstsec").removeClass("thcolfirstsec");
                    $("#search_mini_form #search").removeClass("activetxtbox");
                    $("#search_mini_form #search").val("");
                    $("#search_mini_form .action.search").attr("disabled", "disabled");
                });
            }
        },

        spinnerShow: function () {
            let spinner = $(this.searchTermFormSelector);
            spinner.addClass('loading');
        },

        spinnerHide: function () {
            let spinner = $(this.searchTermFormSelector);
            spinner.removeClass('loading');
        },

        aggregatedResultCount: function () {
            var self = this,
                searchTermField = $(self.searchTermInputSelector),
                searchTermFieldHasFocus = searchTermField.is(':focus') && searchTermField.val().length >= self.searchTermMinLength;
            registry.get('eccAutoSuggestForm', function (autosuggest) {
                let aggResult;
                aggResult = searchTermFieldHasFocus
                    && !autosuggest.prodsResultCount()
                    && !autosuggest.catResultCount()
                    && !autosuggest.dymResultCount()
                    && !autosuggest.pagesResultCount();
                autosuggest.aggregatedResultCount(aggResult);
            });
        },

        showRecentSearch: function () {
            var self = this,
                rsEnabled = self.rsEnabled,
                rsConfig = self.rsConfig,
                searchTermField = $(self.searchTermInputSelector),
                searchTermFieldHasFocus = searchTermField.is(':focus');
            registry.get('eccAutoSuggestForm', function (autosuggest) {
                switch (true) {
                    case (rsEnabled == 0):
                        autosuggest.showRecentSearch(false)
                        break;
                    case (rsEnabled == 1
                        && rsConfig.search("first_click") >= 0
                        && rsConfig.search("auto_suggest") >= 0):
                        autosuggest.showRecentSearch(true)
                        break;
                    case (rsEnabled == 1
                        && rsConfig.search("first_click") >= 0
                        && rsConfig.search("auto_suggest") < 0):
                        let aggResult;
                        aggResult = searchTermFieldHasFocus
                            && !autosuggest.prodsResultCount()
                            && !autosuggest.catResultCount()
                            && !autosuggest.dymResultCount()
                            && !autosuggest.pagesResultCount()
                            && !autosuggest.aggregatedResultCount();
                        autosuggest.showRecentSearch(aggResult)
                        break;
                    case (rsEnabled == 1
                        && rsConfig.search("first_click") < 0
                        && rsConfig.search("auto_suggest") >= 0):
                        let aggtResult;
                        aggtResult = searchTermFieldHasFocus
                            && (autosuggest.prodsResultCount()
                            || autosuggest.catResultCount()
                            || autosuggest.dymResultCount()
                            || autosuggest.pagesResultCount()
                            || autosuggest.aggregatedResultCount());
                        autosuggest.showRecentSearch(aggtResult)
                        break;
                }
            });
        },

        showHotSearch: function () {
            var self = this,
                hsEnabled = self.hsEnabled,
                hsConfig = self.hsConfig,
                searchTermField = $(self.searchTermInputSelector),
                searchTermFieldHasFocus = searchTermField.is(':focus');
            registry.get('eccAutoSuggestForm', function (autosuggest) {
                switch (true) {
                    case (hsEnabled == 0):
                        autosuggest.showHotSearch(false)
                        break;
                    case (hsEnabled == 1
                        && hsConfig.search("first_click") >= 0
                        && hsConfig.search("auto_suggest") >= 0):
                        autosuggest.showHotSearch(true)
                        break;
                    case (hsEnabled == 1
                        && hsConfig.search("first_click") >= 0
                        && hsConfig.search("auto_suggest") < 0):
                        let aggResult;
                        aggResult = searchTermFieldHasFocus
                            && !autosuggest.prodsResultCount()
                            && !autosuggest.catResultCount()
                            && !autosuggest.dymResultCount()
                            && !autosuggest.pagesResultCount()
                            && !autosuggest.aggregatedResultCount();
                        autosuggest.showHotSearch(aggResult)
                        break;
                    case (hsEnabled == 1
                        && hsConfig.search("first_click") < 0
                        && hsConfig.search("auto_suggest") >= 0):
                        let aggtResult;
                        aggtResult = searchTermFieldHasFocus
                            && (autosuggest.prodsResultCount()
                                || autosuggest.catResultCount()
                                || autosuggest.dymResultCount()
                                || autosuggest.pagesResultCount()
                                || autosuggest.aggregatedResultCount());
                        autosuggest.showHotSearch(aggtResult)
                        break;
                }
            });
        }
    });
});
