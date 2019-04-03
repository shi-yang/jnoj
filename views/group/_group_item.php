<?php
use yii\helpers\Html;
?>
<div class="card">
    <div class="card-header">
        <?= Html::a(Html::encode($model->name), ['/group/view', 'id' => $model->id]) ?>
    </div>
    <div class="card-body">
        <p class="card-text">
            <?= Html::encode($model->description) ?>
        </p>
    </div>
</div>

