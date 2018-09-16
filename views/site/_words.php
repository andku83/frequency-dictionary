<?php

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \app\models\search\WordSearch */

use app\models\Word;
use app\models\search\WordSearch;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use yii\grid\GridView;

if (empty($searchModel) || empty($dataProvider)) {
    $searchModel = new WordSearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
}
?>

<?php Pjax::begin([
    'id' => 'word-list'
]); ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'filterModel' => $searchModel,
    'columns' => [
//        'id',
        'score',
        [
            'attribute' => 'lemma',
            'format' => 'raw',
            'value' => function (Word $model) {
                return Html::a($model->lemma, Url::to(['/site/show-word', 'id' => $model->id], ['class' => 'show-word']));
            },
        ],
//        'lemma',
        'frequency',
        'dispersion',
        //'context:ntext',
    ],
]); ?>

<?php Pjax::end(); ?>
