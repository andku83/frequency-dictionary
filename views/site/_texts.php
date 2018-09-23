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
<?php $totalSize = (int)(Text::find()->sum('length') / 1024) ?>
<?= GridView::widget([
    'dataProvider' => new ActiveDataProvider([
        'query' => Text::find(),
        'pagination' => false,
    ]),
    'summary' => "Total items <strong>$totalSize Kb</strong>",
    'filterModel' => false,
    'columns' => [
        [
            'attribute' => 'name',
            'label' => 'File',
            'format' => 'raw',
            'value' => function ($model) {
                return Html::a($model['name'], Url::to(['/site/show-text', 'id' => $model->id]), ['data-id' => 'show-text-'.$model->id, 'class' => 'show-modal']);
            },
        ],
    ],
]); ?>

<?php Pjax::end(); ?>
