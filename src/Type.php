<?php
namespace exchangecore\yii2\parameters;

use Yii;
use yii\base\Object;
use yii\helpers\FormatConverter;
use yii\web\JsExpression;

class Type extends Object
{
    const TEXT_TYPE = 'text';
    const LIST_TYPE = 'list';
    const DATE_TYPE = 'date';
    const NUMERIC_TYPE = 'numeric';
    const BOOL_TYPE = 'bool';

    public static function getTypeList()
    {
        $dateFormat = Yii::$app->formatter->dateFormat;
        if (strncmp(Yii::$app->formatter->dateFormat, 'php:', 4) === 0) {
            $dateFormat = FormatConverter::convertDatePhpToJui(substr($dateFormat, 4));
        } else {
            $dateFormat = FormatConverter::convertDateIcuToJui($dateFormat, 'date', Yii::$app->language);
        }

        return [
            static::TEXT_TYPE => [
                'comparisons' => static::getTextTypeComparisons(),
                'formatter' => 'raw',
                'options' => [
                    'inputType' => 'text',
                ],
            ],
            static::NUMERIC_TYPE => [
                'comparisons' => static::getNumericTypeComparisons(),
                'formatter' => 'integer',
                'options' => [
                    'inputType' => 'text',
                ],
            ],
            static::DATE_TYPE => [
                'comparisons' => static::getDateTypeComparisons(),
                'formatter' => ['date', 'php:Y-m-d'],
                'options' => [
                    'inputType' => 'date',
                    'language' => Yii::$app->language,
                    'renderScript' => new JsExpression('function(parameter) {
                        var self = this;
                        parameter.row.find(".datepicker").each(function(){
                            var datePicker = $(this);
                            var parameterValue = datePicker.prev(".parameter-value");

                            function parseISO8601(dateStringInRange) {
                                var isoExp = /^\s*(\d{4})-(\d\d)-(\d\d)\s*$/,
                                    date = new Date(NaN), month,
                                    parts = isoExp.exec(dateStringInRange);

                                if(parts) {
                                  month = +parts[2];
                                  date.setFullYear(parts[1], month - 1, parts[3]);
                                  if(month != date.getMonth() + 1) {
                                    date.setTime(NaN);
                                  }
                                }
                                return date;
                              }

                            var dt = parseISO8601(datePicker.val());
                            dt.setTime( dt.getTime() + dt.getTimezoneOffset()*60*1000 );
                            var options = $.extend(
                                {
                                    "defaultValue": dt,
                                    "altField": parameterValue,
                                    "onClose": function(dateText, inst) {
                                        if (dateText == "") {
                                            var altField = $(inst.settings["altField"]);
                                            if (altField.length) {
                                                altField.val(dateText);
                                            }
                                        }
                                        parameterValue.trigger("change");
                                    }
                                },
                                self.clientOptions
                            );

                            if (self.language !== "en-US") {
                                options = $.extend(options, $.datepicker.regional[self.language]);
                            }

                            datePicker.datepicker(options).datepicker("setDate", dt);
                        });
                    }'),
                    'postRenderScript' => new JsExpression('function(parameter) {
                        parameter.row.find(".datepicker").each(function(){
                            var datePicker = $(this);
                            var parameterValue = datePicker.prev(".parameter-value");
                            parameterValue.trigger("change");
                        });
                    }'),
                    'clientOptions' => [
                        'dateFormat' => $dateFormat,
                        'altFormat' => 'yy-mm-dd',
                    ]
                ],
            ],
            static::LIST_TYPE => [
                'comparisons' => static::getListTypeComparisons(),
                'formatter' => 'raw',
                'options' => [
                    'inputType' => 'select',
                    'renderScript' => new JsExpression('function(parameter) {
                        parameter.row.find(".parameter-value").select2({ "width": "100%" });
                    }'),
                    'valueOptions' => [
                        '' => ''
                    ]
                ]
            ],
            static::BOOL_TYPE => [
                'comparisons' => static::getBooleanTypeComparison(),
                'formatter' => 'integer',
                'options' => [
                    'inputType' => 'select',
                    'valueOptions' => [
                        '' => '',
                        '0' => Yii::t('modules/parameters', 'False'),
                        '1' => Yii::t('modules/parameters', 'True')
                    ]
                ],
            ]
        ];
    }

    protected static function getTextTypeComparisons()
    {
        return Comparison::EQUALS | Comparison::NOT_EQUALS | Comparison::NULL | Comparison::NOT_NULL
            | Comparison::STARTS_WITH | Comparison::CONTAINS | Comparison::ENDS_WITH;
    }

    protected static function getListTypeComparisons()
    {
        return Comparison::EQUALS | Comparison::ONE_OF;
    }

    protected static function getDateTypeComparisons()
    {
        return Comparison::EQUALS | Comparison::NULL | Comparison::NOT_NULL | Comparison::AFTER | Comparison::BEFORE
            | Comparison::BETWEEN;
    }

    protected static function getNumericTypeComparisons()
    {
        return Comparison::EQUALS | Comparison::NOT_EQUALS | Comparison::NULL | Comparison::NOT_NULL
        | Comparison::GREATER_THAN | Comparison::BETWEEN | Comparison::LESS_THAN;
    }

    protected static function getBooleanTypeComparison()
    {
        return Comparison::EQUALS;
    }

} 