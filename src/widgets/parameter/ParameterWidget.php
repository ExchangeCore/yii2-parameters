<?php
namespace exchangecore\yii2\parameters\widgets\parameter;

use exchangecore\yii2\parameters\assets\ParameterAsset;
use exchangecore\yii2\parameters\Comparison;
use exchangecore\yii2\parameters\Parameter;
use exchangecore\yii2\parameters\Type;
use yii\base\Widget;
use yii\helpers\Json;
use yii\helpers\Url;
use yii\jui\DatePickerLanguageAsset;
use yii\jui\JuiAsset;

class ParameterWidget extends Widget
{

    public $url = '#';
    public $ajaxRequestType = 'POST';
    public $autoRun = false;
    public $autoShow = false;
    public $loadingWidgetID;
    public $parameters = [];

    public function init()
    {
        parent::init();
        if($this->loadingWidgetID === null) {
            $this->loadingWidgetID = \Yii::$app->get('loadingWidget')->getId();
        }
    }

    public function run()
    {
        $view = $this->getView();
        ParameterAsset::register($view);
        JuiAsset::register($view);
        if(substr(\Yii::$app->language, 0, 2) !== 'en') {
            $assetBundle = DatePickerLanguageAsset::register($view);
            $assetBundle->language = \Yii::$app->language;
        }

        $view->registerJs(
            "$('#parameter-" . $this->getId() . "').ParameterWidget(" . $this->getParameterWidgetOptions() . ");"
        );

        return $this->render(
            'parameter',
            [
                'widgetID' => $this->getId(),
                'autoShow' => $this->autoShow
            ]
        );
    }

    protected function getParameterWidgetOptions()
    {
        $options = [];
        $options['ajaxSettings']['url'] = Url::to($this->url);
        $options['ajaxSettings']['type'] = $this->ajaxRequestType;
        $options['autoRun'] = true;
        $options['autoShow'] = $this->autoShow;
        $options['comparisons'] = Comparison::getComparisonList();
        $options['types'] = Type::getTypeList();
        $options['loaderElement'] = '#' . $this->loadingWidgetID;
        foreach($this->parameters AS $parameter) {
            /** @var Parameter $parameter */
            if ($parameter->getKey() !== null) {
                $options['parameters'][$parameter->getKey()] = $parameter->getJsObject();
            } else {
                $options['parameters'][] = $parameter->getJsObject();
            }
        }

        return Json::htmlEncode($options);
    }

} 