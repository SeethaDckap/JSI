/**
 * Copyright © 2010-2018 Epicor Software Corporation: All Rights Reserved
 */

if (typeof Solarsoft == 'undefined') {
    var Solarsoft = {};
}

function populateMasqueradeSelect(row, event) {
   var trElement = event.findElement('tr');
    var rowTD = trElement.select('td');
    var rowId = rowTD[0].innerHTML;
    window.parent.populateMasqueradeSelectParent(rowId);  
}

Solarsoft.masqueradeSearch = Class.create();
Solarsoft.masqueradeSearch.prototype = {
    target: null,
    wrapperId: "selectMasqueradeSearchWrapperWindow",
    height: 0,
    boxAccountPaddingHeight: 0,
    boxAccountPaddingBottom: 0,
    initialize: function() {
//        if(!$('window-overlay')){
//            $(document.body).insert('<div id="window-overlay" class="window-overlay" style="display:none;"></div>');
//        }
    },
    openpopup: function(newtarget, path, search) {

        this.target = newtarget;
        this.path = path;

        if ($(this.wrapperId))
            $(this.wrapperId).remove();
        // create Popup Wrapper
        var wrappingDiv = new Element('div');
        wrappingDiv.id = this.wrapperId;


        $(document.body).insert(wrappingDiv);
        $('loading-mask').show();
        $(this.wrapperId).hide();
        baseurl = location.protocol + "//" + location.hostname;
        url = baseurl + '/comm/onepage/' + path;
        url = url + (url.match(new RegExp('\\?')) ? '&isAjax=true' : '?isAjax=true');
        this.ajaxRequest = new Ajax.Request(url, {
            method: 'post',
            onComplete: function(request) {
                this.ajaxRequest = false;
            }.bind(this),
            onSuccess: function(request) {
                $(this.wrapperId).insert(request.responseText);
                $('window-overlay').insert($(this.wrapperId));
                $(this.wrapperId).show();
                $('window-overlay').show();
                this.height = $(this.wrapperId).getHeight();
                $('loading-mask').hide();
                this.updateWrapper();


            }.bind(this),
            onFailure: function(request) {
                alert('Error occured loading masquerade search grid');
                this.closepopup();
            }.bind(this),
            onException: function(request, e) {
                alert('Error occured loading masquerade search grid');
                this.closepopup();
            }.bind(this)
        });
    },
    updateWrapper: function() {
        var availableHeight = $(document.viewport).getHeight();
        var elementHeight = 0;
        if (this.height) {
            if (this.height < availableHeight * .6) {
                elementHeight = availableHeight * .6;     //  make display always at least 60% of available height
            } else {
                elementHeight = this.height;
            }
        } else {
            elementHeight = parseInt(availableHeight * .8);
        }
        var elementWidth = $(this.wrapperId).getWidth();
        $(this.wrapperId).select('.box-account').each(function(z) {
            layout = new Element.Layout(z);
            this.boxAccountPaddingHeight = layout.get('padding-top');
            this.boxAccountPaddingBottom = layout.get('padding-bottom');
            elementHeight += this.boxAccountPaddingHeight;
        });
        $(this.wrapperId).setStyle({'height': elementHeight + 'px'});          // reset wrapper height if changed above
        elementWidth = $(document.viewport).getWidth() * .8;
        var availableWidth = $(document.viewport).getWidth();
        if ((availableWidth - elementWidth) < 0) {
            var left = 0;
        } else {
            var left = (availableWidth - elementWidth) / 2;
        }
        if ((availableHeight - elementHeight) < 0) {
            var top = 20;
        } else {
            var top = (availableHeight - elementHeight) / 2;
        }

        if ($(this.wrapperId)) {
            var height = 22;
            $$('#' + this.wrapperId).each(function(item) {
                height += item.getHeight();
            });

            if (height > ($(document.viewport).getHeight() - 40))
                height = $(document.viewport).getHeight() - 40;

            if (height < 35) {
                height = 35;
                top:0;
            }
            $(this.wrapperId).setStyle({
                'height': height + 'px',
                'width': elementWidth + 'px',
                'marginTop': top + 'px',
                'marginLeft': left + 'px',
            });
        }

    },
    closepopup: function() {
        $(this.wrapperId).remove();
        $('window-overlay').hide();
    },
};
var masqueradeSearch = new Solarsoft.masqueradeSearch();
document.observe('dom:loaded', function() {
    var masqueradeSearch = new Solarsoft.masqueradeSearch();
    if ($('masquerade-search-button')) {
        $('masquerade-search-button').observe('click', function(event) {
            masqueradeSearch.openpopup('masquerade_search', 'masqueradeSearchpopup');
        });
    }

    if ($('masquerade-search-button')) {
        Event.observe(window, "resize", function() {
            masqueradeSearch.updateWrapper();
        });
    }
});
