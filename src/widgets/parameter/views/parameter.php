<?php
/**
 * @var \yii\web\View $this
 * @var string $widgetID
 * @var bool $autoShow
 * @var \app\widgets\parameter\Parameter[] $displayParameters
 */
use yii\helpers\Html;

$submitButton = Html::submitButton(
    '<i class="fa fa-lg fa-play-circle"></i> ' . Yii::t('app', 'Run'),
    ['class' => 'btn btn-lg btn-primary btn-submit hidden-print']
);
?>
<div id="parameter-<?= $widgetID; ?>">
    <form class="parameter-form">
        <div class="row">
            <div class="col-sm-12">
                <?= $submitButton; ?>
                <?=
                Html::button(
                    '<i class="fa fa-lg fa-list-alt"></i> ' . Yii::t('app', 'Parameters'),
                    ['class' => 'btn btn-lg btn-primary btn-parameters-collapse hidden-print']
                ); ?>
            </div>
        </div>
        <div class="row visible-print-inline print-selection-criteria"></div>
        <br/>
        <div class="hidden-print parameters panel panel-primary collapse <?= $autoShow ? 'in' : '';?>">
            <div class="panel-heading"><?= Yii::t('app', 'Parameters') ?></div>
            <div class="panel-body">
                <div class="alert-area hidden">
                </div>
            </div>
            <div class="panel-footer">
                <div class="pull-right">
                    <?= Html::button(
                        '<i class="fa fa-lg fa-plus-circle"></i> ' . Yii::t('app', 'Add Filter'),
                        ['class' => 'btn btn-lg btn-primary action-add-filter']
                    );
                    ?>
                    <?= $submitButton; ?>
                </div>
                <div class="clearfix"></div>
            </div>
        </div>
    </form>
</div>