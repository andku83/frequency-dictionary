<?php

use yii\bootstrap\ActiveForm;
use yii\helpers\Html;
use yii\widgets\Pjax;

/* @var $this \yii\web\View */
/* @var $model \app\models\Glossary */

$this->title = yii::t('app', 'Create Glossary');
?>
<div class="glossary-edit">
<?php Pjax::begin(['id' => 'glossary-edit']) ?>
    <h1><?= Html::encode($this->title) ?></h1>

    <div class="glossary-form">

        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($model, 'headword')->textInput(['maxlength' => true]) ?>

        <?= $form->field($model, 'description')->textarea() ?>

        <div class="form-group">
            <?= Html::submitButton($model->isNewRecord ? yii::t('app', 'Create') : yii::t('app', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
        </div>

        <?php ActiveForm::end(); ?>

    </div>

    <?php Pjax::end() ?>
</div>
