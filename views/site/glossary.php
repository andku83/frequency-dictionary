<?php

use app\models\Glossary;
use app\models\search\GlossarySearch;
use yii\widgets\Pjax;
use yii\grid\GridView;

/* @var $this yii\web\View */

$this->title = 'Glossary';

if (empty($searchModel) || empty($dataProvider)) {
    $searchModel = new GlossarySearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
}
?>
<div class="site-index">

    <div class="body-content">

        <div class="btn-control">
            <button class="btn btn-success" data-action="load-glossary">Load Glossary</button>
        </div>
        <br>

        <?php Pjax::begin([
            'id' => 'glossary-pjax'
        ]); ?>

        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'filterModel' => $searchModel,
            'columns' => [
                [
                    'attribute' => 'headword',
                    'format' => 'raw',
                    'value' => function (Glossary $model) {
                        return <<<HTML
                        <a href="#" class="modal-get">$model->headword</a>
                        <div class="hidden">$model->headword</div>
HTML;
                    },
                ],
                'description',
            ],
        ]); ?>

        <?php Pjax::end(); ?>

        <?= \yii\bootstrap\Modal::widget([
            'id' => 'popover-modal',
            'header' => 'Add description',
        ]) ?>

    </div>
</div>

