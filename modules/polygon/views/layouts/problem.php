<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\bootstrap\Nav;
$problem = $this->params['model'];
?>
<?php $this->beginContent('@app/views/layouts/main.php'); ?>
<div class="polygon-header">
    <?= Nav::widget([
        'options' => ['class' => 'nav nav-pills'],
        'items' => [
            ['label' => Yii::t('app', 'Preview'), 'url' => ['/polygon/problem/view', 'id' => $problem->id]],
            ['label' => Yii::t('app', 'Edit'), 'url' => ['/polygon/problem/update', 'id' => $problem->id]],
            ['label' => Yii::t('app', 'Solution'), 'url' => ['/polygon/problem/solution', 'id' => $problem->id]],
            ['label' => Yii::t('app', 'Special Judge'), 'url' => ['/polygon/problem/spj', 'id' => $problem->id]],
            ['label' => Yii::t('app', 'Tests Data'), 'url' => ['/polygon/problem/tests', 'id' => $problem->id]],
        ],
    ]) ?>
</div>
<hr>
<?= $content ?>
<?php $this->endContent(); ?>
