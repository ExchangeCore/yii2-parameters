<?php
namespace exchangecore\yii2\parameters\assets;

use yii\web\AssetBundle;

class FontAwesomeAsset extends AssetBundle
{
    public $sourcePath = '@bower/font-awesome/';

    public $css = [
        'css/font-awesome.min.css',
    ];
}
