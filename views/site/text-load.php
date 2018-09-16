<?php

/* @var $this \yii\web\View */

use mihaildev\elfinder\ElFinder;
use yii\widgets\Pjax;

?>

<?php Pjax::begin([
    'id' => 'modal'
]); ?>

<div style="height: 100%; margin: auto">
    <?= ElFinder::widget([
        'language'     => 'ru',
        'controller'   => 'elfinder',
        'filter'       => 'text',
        'frameOptions' => ['style'=>'min-height: 500px; width: 100%; border: 0'],
//    'callbackFunction' => new JsExpression('function(file, id){}') // id - id виджета
    ]); ?>
</div>

<?php Pjax::end(); ?>

