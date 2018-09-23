<?php

/* @var $this yii\web\View */

$this->title = 'A Semantic Frequency Dictionary of Information Technologies';
?>
<div class="site-index">

    <div class="body-content">

        <div class="row">
            <div class="col-lg-2">
                <?= $this->render('_texts') ?>
            </div>
            <div class="col-lg-10">
                <?= $this->render('_words') ?>
            </div>
        </div>

    </div>
</div>

