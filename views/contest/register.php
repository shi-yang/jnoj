<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contest'), 'url' => ['/contest/index']];
$this->params['breadcrumbs'][] = $this->title;
?>

<h2>即将参加：<?= Html::encode($model->title) ?></h2>

<h4>参赛协议</h4>
<p>1. 不与其他人分享解决方案</p>
<p>2. 不以任何形式破坏和攻击测评系统</p>

<?= Html::a(Yii::t('app', 'Agree above and register'), ['/contest/register', 'id' => $model->id, 'register' => 1]) ?>
