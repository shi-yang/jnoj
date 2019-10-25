<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Modal;
use app\models\Solution;

/* @var $this yii\web\View */
/* @var $model app\models\Problem */
/* @var $searchModel app\models\SolutionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

$stats = $model->getStatisticsData();
?>
<h1><?= Html::a(Html::encode($model->title), ['/problem/view', 'id' => $model->id]) ?></h1>
<hr>
<div class="stats-content" style="padding: 0 50px">
    <h2>提交统计</h2>
    <div class="row">
        <div class="left-list col-md-6">
            <ul class="stat-list">
                <li>
                    <strong>提交总数</strong><span> <?= $stats['submission_count'] ?></span>
                </li>
                <li>
                    <strong>通过总数</strong><span> <?= $stats['accepted_count'] ?></span>
                </li>
                <li>
                    <strong>通过率</strong><span> <?= $stats['submission_count'] == 0 ? 0 : number_format($stats['accepted_count'] / $stats['submission_count'] * 100, 2) ?> %</span>
                </li>
                <li>
                    <strong>参与作者</strong><span> <?= $stats['user_count'] ?></span>
                </li>
            </ul>
        </div>
        <div class="right-list col-md-6">
            <ul class="stat-list">
                <li>
                    <strong>通过总数</strong><span> <?= $stats['accepted_count'] ?></span>
                </li>
                <li>
                    <strong>错误解答</strong><span> <?= $stats['wa_submission'] ?></span>
                </li>
                <li>
                    <strong>时间超限</strong><span> <?= $stats['tle_submission'] ?></span>
                </li>
                <li>
                    <strong>编译错误</strong><span> <?= $stats['ce_submission'] ?></span>
                </li>
            </ul>
        </div>
    </div>
</div>

<hr>
<div class="solution-index" style="padding: 0 50px">
    <h2>提交排行</h2>
    <?= GridView::widget([
        'layout' => '{items}{pager}',
        'dataProvider' => $dataProvider,
        'options' => ['class' => 'table-responsive'],
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'columns' => [
            [
                'attribute' => 'who',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a($model->username, ['/user/view', 'id' => $model->created_by]);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'result',
                'value' => function ($model, $key, $index, $column) {
                    if ($model->result == $model::OJ_CE || $model->result == $model::OJ_WA
                        || $model->result == $model::OJ_RE) {
                        return Html::a($model->getResult(),
                            ['/solution/result', 'id' => $model->solution_id],
                            ['onclick' => 'return false', 'data-click' => "solution_info"]
                        );
                    } else {
                        return $model->getResult();
                    }
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'time',
                'value' => function ($model, $key, $index, $column) {
                    return $model->time . ' MS';
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'memory',
                'value' => function ($model, $key, $index, $column) {
                    return $model->memory . ' KB';
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'language',
                'value' => function ($model, $key, $index, $column) {
                    return $model->getLang();
                },
                'format' => 'raw'
            ],
            'code_length',
            [
                'attribute' => 'created_at',
                'value' => function ($model, $key, $index, $column) {
                    return Html::tag('span', Yii::$app->formatter->asRelativeTime($model->created_at), ['title' => $model->created_at]);
                },
                'format' => 'raw'
            ]
        ],
    ]); ?>
</div>
