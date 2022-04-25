/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */
define([
    'jquery',
    'uiComponent',
    'uiRegistry',
    'underscore'
], function ($, Component, registry, _) {
    'use strict';

    $.Products = function (data) {
        this.productCode = data.productCode;
        this.description = data.description;
        this.productimage = data.productimage;
        this.producturl = data.producturl;
        this.price = data.price;
        this.rating = data.rating;
    };

    $.Seeall = function (data) {
        this.seeallcount = data.seeallcount;
        this.seeallurl = data.seeallurl;
    };

    $.Didyoumean = function (data) {
        this.queryText = data.queryText;
        this.searchResultURL = data.searchResultURL;
    };
    $.Category = function (data) {
        this.breadcrumb = data.breadcrumb;
        this.queryText = data.queryText;
        this.categoryurl = data.categoryurl;
    };
    $.Cmspages = function (data) {
        this.queryText = data.queryText;
        this.url = data.url;
        this.content = data.content;
    };

    return Component.extend({
        defaults: {
            searchTerm: ''
        },

        load: function () {
            let self = this;
            if (this.xhr) {
                this.xhr.abort();
            }
            this.xhr = $.ajax({
                method: "get",
                dataType: "json",
                url: this.url,
                data: {q: this.searchTerm},
                beforeSend: function () {
                    self.spinnerShow();
                },
                success: $.proxy(function (response) {
                    self.parseResponseData(response);
                    self.spinnerHide();
                    self.showAutoSuggestBox();
// Sections Arrangements
        $("#ecc-autosuggest").removeClass("sixth-item-in-elsrc");
        var elprod = $(".srcprodsecactive").length;
        var elothersec = $(".srcsecactive").length;
        var srcsecactive = $(".srcsecactive");
        var elsearchsec = $(".elsearchsec ");
        elsearchsec.removeClass('srcsecactive1');
        elsearchsec.removeClass('srcsecactive2');
        elsearchsec.removeClass('srcsecactive3');
        elsearchsec.removeClass('srcsecactive4');

        if(elprod == 1 && elothersec == 0){
            $(".srcprodsecactive .elsearchsec ").addClass("recautoheight");
        }
        else{
            $(".srcprodsecactive .elsearchsec").removeClass("recautoheight");
        }

        if(elprod == 0 && (elothersec ==  1 || elothersec ==  2)){
            $(".srcsecactive ").addClass("recautoheight");
        }
        else{
            $(".srcsecactive ").removeClass("recautoheight");
        }
        var i = 1;
        $('.srcsecactive').each(function(){
          $(this).addClass('srcsecactive'+i);
          i++;
        });

     //1 Col
        if(elprod == 0 && (elothersec > 0 && elothersec < 3) || (elothersec == 0 &&  elprod == 1)){
             $("#ecc-autosuggest").removeClass("elsrctwcol");
             $("#ecc-autosuggest").removeClass("elsrcthrcol");
             $(".srcsecactive1").removeClass("thcolfirstsec");
             $(".srcsecactive2").removeClass("thcolsecondsec");
             $(".elsrchcol2col2").css("margin-right", "0px");
             $(".elsrchcol2").removeClass("elsrchcol2col2");
        }

        // 2 Cols
       else if((elprod == 1 && elothersec > 0 && elothersec < 3) || (elprod == 0 && elothersec > 2 )){
            $("#ecc-autosuggest").addClass("elsrctwcol");
            $("#ecc-autosuggest.elsrcthrcol").removeClass("elsrcthrcol");
            $(".srcsecactive1").removeClass("thcolfirstsec");
            $(".srcsecactive2").removeClass("thcolsecondsec");
            $(".elsrchcol2col2").css("margin-right", "0px");
            $(".elsrchcol2").removeClass("elsrchcol2col2");
            $(".srcsecactive2").removeClass("thcolthrdsec");
            $(".srcsecactive2").removeClass("thcolfoursec");
            $(".srcsecactive3").removeClass("thcolfoursec");
            $(".srcsecactive4").removeClass("thcolthrdsec");
            $(".srcsecactive4").removeClass("srcsecactive5");
            if(elothersec > 2){
                $(".elsrchcol2").addClass("elsrchcol2col2");
                $(".srcsecactive1").addClass("thcolfirstsec");
                $(".srcsecactive2").addClass("thcolsecondsec");
                $(".srcsecactive3").addClass("thcolthrdsec");
                $(".srcsecactive4").addClass("thcolfoursec");
                $(".srcsecactive2").removeClass("thcolthrdsec");
                $(".srcsecactive2").removeClass("thcolfoursec");
                $(".srcsecactive3").removeClass("thcolfoursec");
                $(".srcsecactive4").removeClass("thcolthrdsec");
                $(".srcsecactive4").removeClass("srcsecactive5");
                if(elprod == 0){
                    $(".elsrchcol2col2").css("margin-right", "15px");
                }
            }
            else if(elprod == 0 && elothersec > 2){
                $(".elsrchcol2col2").css("margin-right", "15px");
            }
            else {
                $(".elsrchcol2col2").css("margin-right", "0px");
            }

        }
        //3 Cols
        else if(elprod == 1 && elothersec > 2){
            $("#ecc-autosuggest").removeClass("elsrctwcol");
            $("#ecc-autosuggest").addClass("elsrcthrcol");
            $(".elsrchcol2").addClass("elsrchcol2col2");
            $(".srcsecactive1").addClass("thcolfirstsec");
            $(".srcsecactive2").addClass("thcolsecondsec");
            $(".srcsecactive3").addClass("thcolthrdsec");
            $(".srcsecactive4").addClass("thcolfoursec");
            $(".srcsecactive2").removeClass("thcolthrdsec");
            $(".srcsecactive2").removeClass("thcolfoursec");
            $(".srcsecactive3").removeClass("thcolfoursec");
            $(".srcsecactive4").removeClass("thcolthrdsec");
            $(".srcsecactive4").removeClass("srcsecactive5");
            var lastsec = $(".srcsecactive5").length;
            if(lastsec == 1){
                $("#ecc-autosuggest").addClass("sixth-item-in-elsrc");
            }
            else {
                $("#ecc-autosuggest").removeClass("sixth-item-in-elsrc");
            }
        }
                    $("#search_mini_form #search").focus(function(){
                        var srcact = $(".srcsecactive").length;
                        if(srcact < 3){
                            $(".srcsecactive2").removeClass("thcolsecondsec");
                        }
                        if(elprod == 0 && elothersec > 2){
                            $(".elsrchcol2col2").css("margin-right", "15px");
                        }
                        else {
                            $(".elsrchcol2col2").css("margin-right", "0px");
                        }

                    });
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
                })
            });
        },

        showAutoSuggestBox: function () {
            registry.get('eccAutoSuggestBindEvents', function (binder) {
                binder.showAutoSuggestBox();
            });
        },

        spinnerShow: function () {
            registry.get('eccAutoSuggestBindEvents', function (binder) {
                binder.spinnerShow();
            });
        },

        spinnerHide: function () {
            registry.get('eccAutoSuggestBindEvents', function (binder) {
                binder.spinnerHide();
            });
        },

        parseResponseData: function (response) {
            this.setProducts(this.getResponseData(response, 'products'));
            this.setSeeAll(this.getResponseData(response, 'seeall'));
            this.setDidyoumean(this.getResponseData(response, 'didyoumean'));
            this.setCategory(this.getResponseData(response, 'category'));
            this.setCmspages(this.getResponseData(response, 'cmspages'));
        },

        getResponseData: function (response, code) {
            let data = [];
            if (_.isUndefined(response.result)) {
                return data;
            }
            $.each(response.result, function (index, obj) {
                switch (code) {
                    case 'products':
                        if (obj.products) {
                            data = obj.products;
                        }
                        break;
                    case 'seeall':
                        if (obj.seeall) {
                            data = obj.seeall;
                        }
                        break;
                    case 'didyoumean':
                        if (obj.didyoumean) {
                            data = obj.didyoumean;
                        }
                        break;
                    case 'category':
                        if (obj.category) {
                            data = obj.category;
                        }
                        break;
                    case 'cmspages':
                        if (obj.cmspages) {
                            data = obj.cmspages;
                        }
                        break;
                }

            });
            return data;
        },

        setProducts: function (productsData) {
            let products = [];
            if (!_.isUndefined(productsData)) {
                products = $.map(productsData, function (products) {
                    return new $.Products(products) });
            }
            registry.get('eccAutoSuggestForm', function (autosuggest) {
                autosuggest.result.products.data(products);
            });
        },

        setSeeAll: function (seeallData) {
            let seeall = [];
            if (!_.isUndefined(seeallData)) {
                seeall = $.map(seeallData, function (seeall) {
                    return new $.Seeall(seeall) });
            }
            registry.get('eccAutoSuggestForm', function (autosuggest) {
                autosuggest.result.seeall.data(seeall);
            });
        },

        setDidyoumean: function (didyoumeanData) {
            let didyoumean = [];
            if (!_.isUndefined(didyoumeanData)) {
                didyoumean = $.map(didyoumeanData, function (didyoumean) {
                    return new $.Didyoumean(didyoumean) });
            }
            registry.get('eccAutoSuggestForm', function (autosuggest) {
                autosuggest.result.didyoumean.data(didyoumean);
            });
        },

        setCategory: function (categoryData) {
            let category = [];
            if (!_.isUndefined(categoryData)) {
                category = $.map(categoryData, function (category) {
                    return new $.Category(category) });
            }
            registry.get('eccAutoSuggestForm', function (autosuggest) {
                autosuggest.result.category.data(category);
            });
        },

        setCmspages: function (cmspagesData) {
            let cmspages = [];
            if (!_.isUndefined(cmspagesData)) {
                cmspages = $.map(cmspagesData, function (cmspages) {
                    return new $.Cmspages(cmspages) });
            }
            registry.get('eccAutoSuggestForm', function (autosuggest) {
                autosuggest.result.cmspages.data(cmspages);
            });
        }
    });
});