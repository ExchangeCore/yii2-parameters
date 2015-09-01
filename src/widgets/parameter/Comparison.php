<?php
namespace exchangecore\yii2\parameters\widgets\parameter;

use Yii;

class Comparison 
{
    const EQUALS = 1;
    const NOT_EQUALS = 2;
    const NULL = 4;
    const NOT_NULL = 8;
    const STARTS_WITH = 16;
    const ENDS_WITH = 32;
    const CONTAINS = 64;
    const BEFORE = 128;
    const AFTER = 256;
    const BETWEEN = 512;
    const GREATER_THAN = 1024;
    const LESS_THAN = 2048;

    const VALUE_NONE = 'none';
    const VALUE_NORMAL = 'normal';
    const VALUE_DOUBLE = 'two';
    //const VALUE_MODAL = 'modal'; todo: someday implement a modal lookup option

    public static function getComparisonList()
    {
        return [
            static::EQUALS => [
                'label' => Yii::t('app', 'equal to'),
                'valueType' => static::VALUE_NORMAL,
            ],
            static::NOT_EQUALS => [
                'label' => Yii::t('app', 'not equal to'),
                'valueType' => static::VALUE_NORMAL,
            ],
            static::NULL => [
                'label' => Yii::t('app', 'is null'),
                'valueType' => static::VALUE_NONE,
            ],
            static::NOT_NULL => [
                'label' => Yii::t('app', 'is not null'),
                'valueType' => static::VALUE_NONE,
            ],
            static::STARTS_WITH => [
                'label' => Yii::t('app', 'starts with'),
                'valueType' => static::VALUE_NORMAL,
            ],
            static::ENDS_WITH => [
                'label' => Yii::t('app', 'ends with'),
                'valueType' => static::VALUE_NORMAL,
            ],
            static::CONTAINS => [
                'label' => Yii::t('app', 'contains'),
                'valueType' => static::VALUE_NORMAL,
            ],
            static::BEFORE => [
                'label' => Yii::t('app', 'before'),
                'valueType' => static::VALUE_NORMAL,
            ],
            static::AFTER => [
                'label' => Yii::t('app', 'after'),
                'valueType' => static::VALUE_NORMAL,
            ],
            static::BETWEEN => [
                'label' => Yii::t('app', 'between'),
                'valueType' => static::VALUE_DOUBLE,
            ],
            static::GREATER_THAN => [
                'label' => Yii::t('app', 'is greater than'),
                'valueType' => static::VALUE_NORMAL,
            ],
            static::LESS_THAN => [
                'label' => Yii::t('app', 'is less than'),
                'valueType' => static::VALUE_NORMAL,
            ]
        ];
    }

    public static function getComparisonLabel($type)
    {
        return static::getComparisonList()[$type]['label'];
    }

    public static function getComparisonValueType($type)
    {
        return static::getComparisonList()[$type]['valueType'];
    }
} 