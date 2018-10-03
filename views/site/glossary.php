<?php

use app\models\Glossary;
use app\models\search\GlossarySearch;
use yii\widgets\Pjax;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */

$this->title = 'Glossary';

if (empty($searchModel) || empty($dataProvider)) {
    $searchModel = new GlossarySearch();
    $dataProvider = $searchModel->search(Yii::$app->request->queryParams);
}
?>
<div class="site-index">

    <div class="body-content">

        <a href="<?= Url::to(['site/glossary-edit']) ?>" class="btn btn-success show-modal" data-id="glossary-edit">Add Glossary</a>
        <br>
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
                        return Html::a($model->headword, ['site/glossary-edit', 'headword' => $model->headword], ['data-id' => 'glossary-edit', 'class' => 'show-modal']);
                    }
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

