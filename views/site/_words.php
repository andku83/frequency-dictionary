<?php

/* @var $this yii\web\View */
/* @var $dataProvider \yii\data\ActiveDataProvider */
/* @var $searchModel \app\models\search\WordSearch */

use app\models\Word;
use app\models\search\WordSearch;
use yii\helpers\ArrayHelper;
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
        'score:decimal',
        [
            'attribute' => 'headword',
            'format' => 'raw',
            'value' => function (Word $model) {
                return <<<HTML
        <a href="#" class="modal-get">$model->headword</a>
        <div class="hidden">$model->lemma</div>
HTML;
            },
        ],
//        'lemma',
        'frequency:decimal',
        'dispersion:decimal',
        [
            'attribute' => 'context',
            'format' => 'raw',
            'value' => function (Word $model) {
                return '<div class="context-block">' .
                    '<div class="text-name">' .
                        ArrayHelper::getValue($model->textWord, 'text.name') .
                    '</div><div class="context">' .
                        ArrayHelper::getValue($model->textWord, 'context') .
                    '</div></div>';
            },
],
    ],
]); ?>

<?php Pjax::end(); ?>

<?= \yii\bootstrap\Modal::widget([
    'id' => 'popover-modal',
    'header' => 'WordNet',
]) ?>

<?php $this->registerJs(<<<JS
$('body').on('click', '.modal-get', function (e) {
    e.preventDefault();
    let modal = $('#popover-modal');
    modal.find('.modal-body').html($(this).siblings('.hidden').html());
    modal.modal('show');
});
JS
); ?>
