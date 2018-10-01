<?php

/* @var $this \yii\web\View */
/* @var $result array */

use yii\widgets\Pjax;

?>

<?php Pjax::begin([
    'id' => 'process'
]); ?>

<div class="btn-control">
    <h1>Processing control</h1>
    <br>
    <button class="btn btn-success" data-action="start">Start</button>
    <button class="btn btn-warning" data-action="pause">Pause</button>
    <button class="btn btn-danger" data-action="reset">Reset</button>
</div>
<br>

<div class="all-progress">

    <?= $this->render('_processing', ['result' => $result]) ?>

</div>
<?php Pjax::end(); ?>
