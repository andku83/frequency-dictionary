<?php

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \app\models\search\WordSearch */

use app\models\Text;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;

?>

<?php Pjax::begin([
    'id' => 'text-list'
]); ?>

<?= GridView::widget([
    'dataProvider' => new ActiveDataProvider([
        'query' => Text::find(),
        'pagination' => false
    ]),
    'filterModel' => false,
    'columns' => [
        [
            'attribute' => 'name',
            'label' => 'File',
            'format' => 'raw',
            'value' => function ($model) {
                return Html::a($model['name'], Url::to(['/site/show-text', 'id' => $model->id]));
            },
        ],
    ],
]); ?>

<?php Pjax::end(); ?>
