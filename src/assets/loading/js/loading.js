;(function ( $, window, document, undefined ) {
    "use strict";
    if (!$.ec) {
        $.ec = {};
    }

    $.ec.loadingWidget = {};
    $.ec.loadingWidget.name = "LoadingWidget";
    $.ec.loadingWidget.defaults = {
        dialogOptions: {},
        progressBarOptions: {}
    };

    $.ec.loadingWidget.obj = function ( element, options ) {
        this.element = element;
        this.$element = $(element);
        this.settings = $.extend( {}, $.ec.loadingWidget.defaults, options );
        this._dialog = null;
        this._progressBar = null;
        this._loadingStack = 0;
        this.init();
    };

    $.extend($.ec.loadingWidget.obj.prototype, {
        init: function () {
            this._progressBar = this.$element.find('.loading-progressbar');
            this._progressBar.progressbar(this.settings.progressBarOptions);

            this._dialog = this.$element.find('.loading-dialog');
            this._dialog.dialog(this.settings.dialogOptions);
        },
        startLoading: function () {
            this._loadingStack++;
            if(this._loadingStack < 2) {
                this._showLoader();
            }
        },
        finishLoading: function() {
            this._loadingStack--;
            if(this._loadingStack < 1) {
                this._hideLoader();
            }
        },
        _showLoader: function() {
            this._dialog.dialog('open');
        },
        _hideLoader: function() {
            this._dialog.dialog('close');
        }
    });

    $.fn.LoadingWidget = function (options) {
        return this.each(function () {
            if (!$.data(this, $.ec.loadingWidget.name)) {
                $.data(this, $.ec.loadingWidget.name, new $.ec.loadingWidget.obj(this, options));
            }
        });
    };

})( jQuery, window, document );