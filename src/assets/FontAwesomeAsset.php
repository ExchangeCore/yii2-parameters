<?php
namespace exchangecore\yii2\parameters\assets;

use yii\web\AssetBundle;

class FontAwesomeAsset extends AssetBundle
{
    public $sourcePath = '@bower/components-font-awesome/';

    public $publishOptions = [
        'only' => [
            'css/*',
            'fonts/*',
        ],
    ];

    public $css = [
        'css/font-awesome.min.css',
    ];
}
