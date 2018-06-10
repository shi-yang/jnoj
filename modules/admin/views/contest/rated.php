<?php

use yii\helpers\Html;
use app\models\Contest;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contests'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['view', 'id' => $model->id]];
?>
<div class="contest-view">

    <h1><?= Html::encode($this->title) ?></h1>
    <hr>
    <p>
        点击下方按钮将计算参加该场比赛的用户在该场比赛所的积分。计算出来的积分用于在排行榜排名。重复点击只会计算一次。
    </p>
    <?php if ($model->getRunStatus() == Contest::STATUS_ENDED): ?>
        <?= Html::a('Rated', ['rated', 'id' => $model->id, 'cal' => 1], ['class' => 'btn btn-success']) ?>
        <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                [
                    'attribute' => 'who',
                    'value' => function ($model, $key, $index, $column) {
                        return Html::a(Html::encode($model->user->nickname), ['/user/view', 'id' => $key]);
                    },
                    'format' => 'raw'
                ],
                'rating_change'
            ],
        ]); ?>
    <?php else: ?>
        <p>比赛尚未结束，请在比赛结束后再来计算积分。</p>
    <?php endif; ?>
</div>
