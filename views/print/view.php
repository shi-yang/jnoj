<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model app\models\ContestPrint */

$this->title = $model->user->nickname;
$this->params['breadcrumbs'][] = ['label' => $model->contest->title, 'url' => ['/contest/view', 'id' => $model->contest_id]];
$this->params['breadcrumbs'][] = ['label' => 'Print Sources', 'url' => ['index', 'id' => $model->contest_id]];
$this->params['breadcrumbs'][] = $this->title;

$this->registerCss('
@media print {
.breadcrumb {
    display: none;
}
}
');
?>
<div class="print-source-view">
    <h1>
        <span class="glyphicon glyphicon-user"></span> <?= Html::encode($this->title) ?>[<?= Html::encode($model->user->username) ?>]
    </h1>
    <p class="hidden-print">
        <?= Html::a('Update', ['update', 'id' => $model->id], ['class' => 'btn btn-primary']) ?>
        <?= Html::a('Delete', ['delete', 'id' => $model->id], [
            'class' => 'btn btn-danger',
            'data' => [
                'confirm' => 'Are you sure you want to delete this item?',
                'method' => 'post',
            ],
        ]) ?>
    </p>
    <?php if (Yii::$app->user->identity->role === \app\models\User::ROLE_ADMIN): ?>
        <h3 class="hidden-print text-muted">
            注：可以使用浏览器打印功能来快速打印（对于 Chrome 浏览器：页面上右键-打印。对于火狐浏览器：点击在右上角菜单按钮，然后选择打印）。
        </h3>
    <?php endif; ?>
    <hr>
    <p><span class="glyphicon glyphicon-time"></span> <?= Yii::$app->formatter->asRelativeTime($model->created_at) ?></p>
    <pre><p><?= Html::encode($model->source) ?></p></pre>

</div>
