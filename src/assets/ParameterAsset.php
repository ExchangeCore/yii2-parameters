<?php
namespace exchangecore\yii2\parameters\assets;

use yii\web\AssetBundle;

class ParameterAsset extends AssetBundle
{
    public $css = [
        'css/parameter.css'
    ];

    public $js = [
        'js/parameter.js',
    ];

    public $depends = [
        'yii\bootstrap\BootstrapAsset',
        'yii\bootstrap\BootstrapPluginAsset',
    ];

    public function init() {
        $this->sourcePath = __DIR__ . '/parameter';
        parent::init();
    }
}
