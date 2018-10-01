<?php

use yii\data\ActiveDataProvider;
use app\models\Text;
use app\models\search\WordSearch;

/* @var $this yii\web\View */

$this->title = 'A Semantic Frequency Dictionary of Information Technologies';
?>
<div class="site-index">

    <div class="body-content">

        <div class="row">
            <div class="col-lg-2">
                <?= $this->render('_texts', [
                    'dataProvider' => new ActiveDataProvider([
                        'query' => Text::find()->andWhere('0=1'),
                        'pagination' => false,
                    ])
                ]) ?>
            </div>
            <div class="col-lg-10">
                <?= $this->render('_words', [
                    'searchModel' => $searchModel = new WordSearch(),
                    'dataProvider' => $searchModel->search(['id' => -1]),
                ]) ?>
            </div>
        </div>

    </div>
</div>

