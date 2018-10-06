<?php

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */

use app\models\Text;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\data\ActiveDataProvider;

if (empty($dataProvider)) {
    $dataProvider = new ActiveDataProvider([
        'query' => Text::find(),
        'pagination' => false,
    ]);
}

?>

<?php Pjax::begin([
    'id' => 'text-list'
]); ?>
<?php //$totalSize = Yii::$app->formatter->asShortSize(1000*Text::find()->sum('length')) ?>
<?php $totalSize = (int)(Text::find()->sum('length') / 1024) ?>
<?php $totalWords = Text::find()->sum('count_words') ?>
<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'summary' => "Total items <strong>$totalSize Kb</strong> / words <strong>$totalWords</strong>",
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
