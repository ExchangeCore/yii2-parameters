<?php
namespace exchangecore\yii2\parameters\widgets\loading;

use Yii;
use exchangecore\yii2\parameters\assets\LoadingAsset;
use yii\base\Widget;
use yii\helpers\Json;

class LoadingWidget extends Widget
{

    public $clientOptions = [
        'progressBarOptions' => [
            'value' => false
        ],
        'dialogOptions' => [
            'modal' => false,
            'closeOnEscape' => false,
            'resizable' => false,
            'autoOpen' => false,
            'width' => 450,
            'dialogClass' => 'loader',
            'position' => [
                'my' => "center top",
                'at' => "center top+5%"
            ]
        ]
    ];

    public function run()
    {
        $view = $this->getview();
        LoadingAsset::register($view);
        $options = empty($this->clientOptions) ? '' : Json::htmlEncode($this->clientOptions);
        $js = '$("#' . $this->getId() . '").LoadingWidget(' . $options . ');';

        $view->registerJs($js, $view::POS_END);

        return $this->render(
            'loading',
            [
                'widgetID' => $this->getId(),
            ]
        );
    }
} 