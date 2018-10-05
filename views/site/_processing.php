<?php

/* @var $this \yii\web\View */

/* @var $result array */

use yii\bootstrap\Progress;

?>

<div class="progress-load-text">

    <label>Load Files</label>
    <?= Progress::widget([
        'label' => $result['load_text']['percent'] . '%',
        'percent' => $result['load_text']['percent'],
        'barOptions' => ['class' => $result['load_text']['percent'] == 100 ? 'progress-bar-success' : 'progress-bar-info progress-bar-striped active'],
    ]) ?>
</div>
<div class="progress-process-text">

    <label>Processed Files</label>
    <?= Progress::widget([
        'label' => $result['processed_text']['percent'] . '%',
        'percent' => $result['processed_text']['percent'],
        'barOptions' => ['class' => $result['processed_text']['percent'] == 100 ? 'progress-bar-success' : 'progress-bar-info progress-bar-striped active'],
    ]) ?>
</div>
<div class="progress-process-text">

    <label>Filter Words</label>
    <?= Progress::widget([
        'label' => $result['filtering']['percent'] . '%',
        'percent' => $result['filtering']['percent'],
        'barOptions' => ['class' => $result['filtering']['percent'] == 100 ? 'progress-bar-success' : 'progress-bar-info progress-bar-striped active'],
    ]) ?>
</div>
