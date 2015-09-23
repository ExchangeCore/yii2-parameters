<?php
namespace exchangecore\yii2\parameters;

use Yii;

class Module extends \yii\base\Module
{

    public function init()
    {
        parent::init();
        $this->registerTranslations();
        $this->setComponents(
            [
                'loadingWidget' => [
                    'class' => 'exchangecore\yii2\parameters\widgets\loading\LoadingWidget'
                ]
            ]
        );
    }

    public function registerTranslations()
    {
        Yii::$app->i18n->translations['modules/parameters*'] = [
            'class' => 'yii\i18n\PhpMessageSource',
            'sourceLanguage' => 'en-US',
            'basePath' => __DIR__ . '/messages',
            'fileMap' => [
                'modules/parameters' => 'parameters.php',
            ],
        ];
    }

} 