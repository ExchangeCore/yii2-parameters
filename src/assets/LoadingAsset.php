<?php
namespace exchangecore\yii2\parameters\assets;

use yii\web\AssetBundle;

class LoadingAsset extends AssetBundle
{
    public $css = [
        'css/loading.css',
    ];
    public $js = [
        'js/loading.js'
    ];
    public $depends = [
        'yii\jui\JuiAsset'
    ];

    public function init() {
        $this->sourcePath = __DIR__ . '/loading';
        parent::init();
    }
}
