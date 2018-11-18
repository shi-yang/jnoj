<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model app\models\Problem */

$this->title = Yii::t('app', 'Create Problem');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="problem-create">
    <h1><?= Html::encode($this->title) ?><small><?= Html::a('建议使用Polygon来出题', ['create-from-polygon']) ?></small></h1>
    <hr>
    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>
</div>
