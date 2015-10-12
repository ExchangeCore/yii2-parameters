;
(function ($, window, document, undefined) {
    "use strict";
    if (!$.ec) {
        $.ec = {};
    }

    $.ec.parameterWidget = {};
    $.ec.parameterWidget.name = "ParameterWidget";
    $.ec.parameterWidget.defaults = {
        "autoRun": false,
        "autoShow": false,
        "reloadPage": false,
        "collapseOnRun": true,
        "runUrl": "#",
        "loaderElement": null,
        "parameters": {},
        "comparisons": {},
        "types": {},
        "language": {
            "sNoFiltersApplied": "No filters applied",
            "sNoFiltersAvailable": "No filters available",
            "sRemoveFilter": "Remove Filter",
            "sCancel": "Cancel",
            "sRequired": "You cannot remove this filter, it is required",
            "sUnknownError": "An unknown error has occurred"
        },
        "ajaxSettings": {
            "type": "POST",
            "url": "#",
            "cache": false,
            "data": {}
        },
        "onInit": function (self) {
        },
        "onComparisonChange": function (options) {
        },
        "onValueChange": function (options) {
        },
        "onFilterAdd": function (options) {
        },
        "onFilterRemove": function (options) {
        },
        "onSubmit": function (options) {
        },
        "onAjaxComplete": function (options) {
        },
        "onAjaxSuccess": function (options) {
        },
        "onAjaxError": function (options) {
        }
    };

    $.ec.parameterWidget.obj = function (element, options) {
        this.element = $(element);
        this.parametersElement = this.element.find('.parameters');

        this.settings = $.extend(true, {}, $.ec.parameterWidget.defaults, options);
        this._defaults = $.ec.parameterWidget.defaults;
        this._parameters = {};
        this._events = {};
        this._loaderElement = null;
        this.init();
    };

    $.extend($.ec.parameterWidget.obj.prototype, {
        init: function () {
            var self = this;
            //create an array of parameters
            var tempParams = [];
            $.each(self.settings.parameters, function (key, object) {
                tempParams.push(object);
            });
            //sort the parameters
            tempParams.sort(function (a, b) {
                a = a.displayName.toLowerCase();
                b = b.displayName.toLowerCase();
                return ((a < b) ? -1 : ((a > b) ? 1 : 0));
            });
            //add keys back to the parameters and initialize necessary settings on each parameter
            $.each(tempParams, function (key, object) {
                object.shown = false;
                object.row = null;
                self._parameters[object.key] = object;
            });


            if (self.settings.loaderElement !== null) {
                self._loaderElement = $(self.settings.loaderElement);
            }

            self.subscribeEvent('init', self.settings.onInit);
            self.subscribeEvent('comparisonChange', self.settings.onComparisonChange);
            self.subscribeEvent('valueChange', self.settings.onValueChange);
            self.subscribeEvent('filterAdd', self.settings.onFilterAdd);
            self.subscribeEvent('filterRemove', self.settings.onFilterRemove);
            self.subscribeEvent('submit', self.settings.onSubmit);
            self.subscribeEvent('ajaxComplete', self.settings.onAjaxComplete);
            self.subscribeEvent('ajaxSuccess', self.settings.onAjaxSuccess);
            self.subscribeEvent('ajaxError', self.settings.onAjaxError);

            $.each(self._parameters, function (parameterKey, parameter) {
                if (parameter.initialize || parameter.required) {
                    self.renderParameter(parameter);
                }
            });

            self.element.find('.parameter-form').on('submit', function (e) {
                e.preventDefault();
                self.startSubmit();
            });

            self.triggerEvent('init', self);

            self.parametersElement.find('.action-add-filter').on('click', function () {
                self.addFilter();
            });

            self.parametersElement.on('click', '.action-remove', function () {
                var row = $(this).closest('.row');
                self.removeParameter(self._parameters[row.find('.parameter-key').val()]);
            });

            self.element.find('.btn-parameters-collapse').on('click', function () {
                self.parametersElement.collapse("toggle");
            });

            if (self.getNextUnusedParameter() === null) {
                self.parametersElement.find('.action-add-filter').addClass('hidden');
            }

            if(self._parameters.length == 0) {
                self.parametersElement.find('.panel-body').html(
                    '<strong>' + self.settings.language.sNoFiltersAvailable + '</strong>'
                );
            }

            if (self.settings.autoRun) {
                self.processSubmit();
            }
        },

        startSubmit: function () {
            var self = this;
            if (self.settings.reloadPage !== false) {
                var url = self.settings.reloadPage;
                var queryString = $.param(self.getQueryStringData());
                if (url.indexOf('?') > -1){
                    url += '&' + queryString;
                }else{
                    url += '?' + queryString;
                }
                window.location.href = url;
                return;
            }
            self.processSubmit();
        },

        getQueryStringData: function() {
            var self = this;
            var data = {};
            var paramCounter = 0;
            $.each(self._parameters, function (key, param) {
                if (param.shown) {
                    data['param' + paramCounter] = {
                        "key": param.key,
                        "comparison": param.comparison,
                        "values": param.value
                    };
                    paramCounter++;
                }
            });
            return data;
        },

        processSubmit: function () {
            var self = this;
            self.element.find('.btn-submit').prop('disabled', true);
            if (self._loaderElement !== null) {
                self._loaderElement.data('LoadingWidget')._dialog
                    .append('<div class="btn btn-danger cancel">' + self.settings.language.sCancel + '</div>');
                self._loaderElement.data('LoadingWidget').startLoading();
            }
            self.triggerEvent('submit', self);

            if (self.settings.collapseOnRun) {
                self.parametersElement.collapse("hide");
            }

            self.parametersElement.find('.alert').alert('close');

            var data = self.getQueryStringData();
            var printCriteriaHtml = '<ul>';
            $.each(self._parameters, function (key, param) {
                if (param.shown) {
                    printCriteriaHtml += '<li>' +
                        param.displayName + ' ' +
                        self.getParameterComparison(param).label + ' ' +
                        self.getPrintValueHtml(param) +
                        '</li>';
                }
            });
            printCriteriaHtml += '</ul>';

            self.element.find('.print-selection-criteria').html(printCriteriaHtml);

            var opts = $.extend({}, self.settings.ajaxSettings);
            opts['data'] = {};
            $.extend(true, opts, {
                "success": function (data, textStatus, jqXHR) {
                    var options = {
                        "data": data,
                        "textStatus": textStatus,
                        "jqXHR": jqXHR,
                        "self": self
                    };

                    self.triggerEvent('ajaxSuccess', options);
                },
                "error": function (jqXHR, textStatus, errorThrown) {
                    var options = {
                        "jqXHR": jqXHR,
                        "textStatus": textStatus,
                        "errorThrown": errorThrown,
                        "self": self
                    };

                    var jsonResponse = {};
                    try{
                        jsonResponse = JSON.parse(jqXHR.responseText);
                    }catch(e){
                    }

                    var errorsList = '<li>' + self.settings.language.sUnknownError + '</li>';
                    var errorHtml = '<div class="alert alert-danger alert-dismissible fade in">' +
                        '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                        '<span aria-hidden="true">&times;</span>' +
                        '</button><ul>';
                    if(typeof jsonResponse.errors !== 'undefined') {
                        errorsList = '';
                        $.each(jsonResponse.errors, function() {
                            errorsList += '<li>' + this + '</li>'
                        });
                    }

                    errorHtml += errorsList + '</ul></div>';

                    var alertArea = self.parametersElement.find('.alert-area');
                    alertArea.removeClass('hidden');
                    alertArea.html(errorHtml);
                    alertArea.children('.alert').on('closed.bs.alert', function(){
                        alertArea.addClass('hidden');
                    });
                    if ( !self.parametersElement.hasClass("collapse")) {
                        self.parametersElement.on('hidden.bs.collapse', function(e) {
                            self.parametersElement.collapse("show");
                            self.parametersElement.off(e);
                        });
                    } else {
                        self.parametersElement.collapse("show");
                    }
                    self.triggerEvent('ajaxError', options);
                },
                "complete": function (jqXHR, textStatus) {
                    var options = {
                        "jqXHR": jqXHR,
                        "textStatus": textStatus,
                        "self": self
                    };

                    self.triggerEvent('ajaxComplete', options);
                    if (self._loaderElement !== null) {
                        self._loaderElement.data('LoadingWidget').finishLoading();
                    }
                    self._loaderElement.data('LoadingWidget')._dialog.find('.cancel').remove();
                    self.element.find('.btn-submit').prop('disabled', false);
                }
            });
            $.extend(opts.data, data);

            var request = $.ajax(opts);
            self._loaderElement.data('LoadingWidget')._dialog.find('.cancel').on('click', function(){
                request.abort();
            });
        },

        removeParameter: function (parameter) {
            var self = this;

            if (parameter.required) {
                alert(self.settings.language.sRequired);
                return;
            }

            if (parameter.row !== null) {
                parameter.shown = false;
                parameter.row.next('.divider').remove();
                parameter.row.remove();
                parameter.row = null;
                var hasShown = false;
                $.each(self._parameters, function (key, param) {
                    if (param.shown) {
                        hasShown = true;
                        param.row.find('.parameter-key').html(self.getParameterOptionsHtml(param));
                    }
                });
                if (!hasShown) {
                    self.parametersElement.find('.panel-body').html(
                        '<strong>' + self.settings.language.sNoFiltersApplied + '</strong>'
                    );
                }
                self.triggerEvent("filterRemove", parameter);
            }

            self.parametersElement.find('.action-add-filter').removeClass('hidden');
        },

        addFilter: function (parameter, insertAfterRow) {
            var self = this;
            if (typeof parameter === 'undefined') {
                parameter = self.getNextUnusedParameter();
            }

            if (parameter !== null) {
                self.renderParameter(parameter, insertAfterRow);
            }

            if (self.getNextUnusedParameter() === null) {
                self.parametersElement.find('.action-add-filter').addClass('hidden');
            }
        },

        getNextUnusedParameter: function () {
            var self = this;
            var nextParameter = null;
            $.each(self._parameters, function (parameterKey, parameter) {
                if (!parameter.shown) {
                    nextParameter = parameter;
                    return false;
                }
                return true;
            });
            return nextParameter;
        },

        getParameterType: function (parameter) {
            return this.settings.types[parameter.type];
        },

        getParameterComparison: function (parameter) {
            return this.settings.comparisons[parameter.comparison];
        },

        getParameterComparisons: function (parameter) {
            if (parameter.comparisons === null) {
                parameter.comparisons = this.getParameterType(parameter)['comparisons'];
            }
            return parameter.comparisons;
        },

        getParameterOptionsHtml: function (parameter) {
            var optionsHtml = '';
            $.each(this._parameters, function (key, param) {
                if (!param.shown || key == parameter.key) {
                    var selected = (parameter.key == param.key) ? ' selected="selected"' : '';
                    optionsHtml += '<option value="' + key + '"' + selected + '>' + param.displayName + '</option>';
                }
            });
            return optionsHtml;
        },

        getParameterComparisonOptions: function (comparisonsInt, defaultValue) {
            var optionsHtml = '';
            $.each(this.settings.comparisons, function (key, value) {
                if ((comparisonsInt & key) == key) {
                    var selected = (key == defaultValue) ? ' selected="selected"' : '';
                    optionsHtml += '<option value="' + key + '"' + selected + '>' + value.label + '</option>';
                }
            });
            return optionsHtml;
        },

        getParameterValueHtml: function (parameter) {
            var parameterHtml = '';

            if (this.getParameterComparison(parameter).valueType == 'two') {
                parameterHtml += '<div class="col-md-6 col-sm-12 row">';
                parameterHtml += '<div class="col-md-6 col-sm-12 form-group" data-valueindex="0">';
                parameterHtml += this.getValueHtml(parameter, 0);
                parameterHtml += '</div><div class="col-md-6 col-sm-12 form-group" data-valueindex="1">';
                parameterHtml += this.getValueHtml(parameter, 1);
                parameterHtml += '</div>';
                parameterHtml += '</div>';
            } else if (this.getParameterComparison(parameter).valueType == 'none') {
                parameterHtml += '<div class="col-md-6 col-sm-12 form-group"></div>';
            } else if (this.getParameterComparison(parameter).valueType == 'multiple') {
                parameterHtml += '<div class="col-md-6 col-sm-12 form-group">';
                parameterHtml += this.getValueHtml(parameter);
                parameterHtml += '</div>';
            } else {
                parameterHtml += '<div class="col-md-6 col-sm-12 form-group" data-valueindex="0">';
                parameterHtml += this.getValueHtml(parameter);
                parameterHtml += '</div>';
            }

            return parameterHtml;
        },

        getValueHtml: function (parameter, valueIndex) {
            if (typeof valueIndex === 'undefined') {
                valueIndex = 0;
            }

            var type = this.getParameterType(parameter);
            var typeOptions = type['options'];
            var readOnlyHtml = parameter.modifiable ? '' : ' disabled="disabled"';

            var fieldHtml = '';

            if (typeOptions.inputType == 'select') {
                //load the default select values
                var options = typeOptions.valueOptions;

                //load the parameter specified options if they exist
                if (parameter.valueOptions) {
                    options = parameter.valueOptions
                }

                var multiple = '';

                if(this.getParameterComparison(parameter).valueType == 'multiple') {
                    multiple = ' multiple="multiple"';
                }

                fieldHtml += '<select class="form-control parameter-value" name="' + parameter.key + '"' + readOnlyHtml + multiple + '>';
                $.each(options, function (valueKey, valueText) {
                    var selected = $.inArray(valueKey, parameter.value) > -1 ? ' selected="selected"' : '';
                    fieldHtml += '<option value="' + valueKey + '"' + selected + '>' + valueText + '</option>';
                });
                fieldHtml += '</select>'
            } else if (typeOptions.inputType == 'date') {
                fieldHtml += '<input class="parameter-value" type="hidden" name="' + parameter.key + '[value][]" />';
                fieldHtml += '<input class="form-control datepicker" type="text" value="' +
                    parameter.value[valueIndex] + '" ' + readOnlyHtml + '/>';
            } else {
                fieldHtml += '<input class="form-control parameter-value" type="text" ' +
                    'name="' + parameter.key + '[value][]" value="' +
                    parameter.value[valueIndex] + '" ' + readOnlyHtml + '/>'
            }


            return fieldHtml;
        },

        getPrintValueHtml: function(parameter) {
            var self = this;
            var html = '';
            if (self.getParameterComparison(parameter).valueType == 'two') {
                html += parameter.value[0] + ' - ' + parameter.value[1];
            } else if (self.getParameterComparison(parameter).valueType == 'none') {
            } else if (self.getParameterComparison(parameter).valueType == 'multiple') {
                html += parameter.value
            } else {
                html += parameter.value[0]
            }

            return html;
        },

        getParameterHtml: function (parameter) {
            var self = this;
            var parameterHtml = '<div class="row parameter">';
            var readOnlyHtml = parameter.modifiable ? '' : ' disabled="disabled"';
            var requiredClass = parameter.required ? ' has-error' : '';
            var requiredKey = parameter.required ? ' disabled="disabled"' : '';
            parameterHtml += '<div class="col-xs-11' + requiredClass + '">';

            parameterHtml += '<div class="col-md-3 col-sm-6 form-group">' +
                '<select class="form-control parameter-key" name="' + parameter.key + '[key]"' + requiredKey + '>' +
                self.getParameterOptionsHtml(parameter) +
                '</select>' +
                '</div>';


            var comparisons = self.getParameterComparisons(parameter);
            parameterHtml += '<div class="col-md-3 col-sm-6 form-group">' +
                '<select class="form-control parameter-comparison" name="' + parameter.key + '[comparison]"' + readOnlyHtml + '>' +
                self.getParameterComparisonOptions(comparisons, parameter.comparison) +
                '</select>' +
                '</div>';

            parameterHtml += self.getParameterValueHtml(parameter);

            parameterHtml += '</div>';

            var removeDisabled = (!parameter.required) ? '' : ' disabled="disabled"';

            parameterHtml += '<div class="col-xs-1 text-right action-buttons">' +
                '<span class="btn btn-danger action-remove" ' +
                '   title="' + self.settings.language.sRemoveFilter + '"' + removeDisabled + '>' +
                '   <i class="fa fa-lg fa-remove"></i>' +
                '</span> ' +
                '</div>';

            parameterHtml += '</div><hr class="divider visible-xs visible-sm"/>';

            return parameterHtml;
        },

        runParameterScripts: function (parameter) {
            var self = this;
            var type = this.getParameterType(parameter);
            if (typeof type.options.renderScript === 'function') {
                type.options.renderScript(parameter);
            }
            if (typeof parameter.renderScript === 'function') {
                parameter.renderScript(parameter);
            }

            parameter.row.find('.parameter-key').on('change', function () {
                var previousRow = $(parameter.row[0]).prev('.divider');
                self.removeParameter(parameter);
                if (previousRow.length > 0) {
                    self.addFilter(self._parameters[$(this).val()], previousRow);
                } else {
                    self.addFilter(self._parameters[$(this).val()]);
                }
            });

            parameter.row.find('.parameter-comparison').on('change', function () {
                var options = {
                    "parameter": parameter
                };

                parameter.comparison = $(this).val();

                self.triggerEvent("comparisonChange", options);
                self.renderParameter(parameter);
            });

            parameter.row.find('.parameter-value').on('change keyup', function () {
                var options = {
                    "parameter": parameter
                };

                var formGroup = $(this).closest('.form-group');

                if (formGroup.attr('data-valueindex')) {
                    parameter.value[formGroup.data('valueindex')] = $(this).val();
                } else {
                    parameter.value = $(this).val()
                }

                self.triggerEvent("valueChange", options);
            });

            if (typeof type.options.postRenderScript === 'function') {
                type.options.postRenderScript(parameter);
            }
            if (typeof parameter.postRenderScript === 'function') {
                parameter.postRenderScript(parameter);
            }
        },

        renderParameter: function (parameter, insertAfterRow) {
            var self = this;
            var panelBody = self.parametersElement.find('.panel-body');
            var parameterHtml = $(this.getParameterHtml(parameter));
            if (parameter.row !== null) {
                $(parameter.row[0]).replaceWith(parameterHtml);
                parameter.row.remove();
                parameter.row = parameterHtml;
            } else {
                parameter.row = parameterHtml;
                if (typeof insertAfterRow !== 'undefined') {
                    insertAfterRow.after(parameter.row);
                } else {
                    panelBody.append(parameterHtml);
                }
                parameter.shown = true;
            }
            $('.parameter-key option[value="' + parameter.key + '"]:not(:selected)').remove();
            self.runParameterScripts(parameter);
            self.triggerEvent("filterAdd", parameter);
        },

        subscribeEvent: function (eventKey, closure) {
            if (!this._events[eventKey]) {
                this._events[eventKey] = [];
            }
            this._events[eventKey].push(closure);
        },

        triggerEvent: function (eventKey, parameters) {
            $.each(this._events[eventKey], function () {
                this(parameters);
            });
        }
    });

    $.fn.ParameterWidget = function (options) {
        return this.each(function () {
            if (!$.data(this, $.ec.parameterWidget.name)) {
                $.data(this, $.ec.parameterWidget.name, new $.ec.parameterWidget.obj(this, options));
            }
        });
    };

})(jQuery, window, document);

