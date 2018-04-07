<?php

use yii\helpers\Html;
use app\models\Solution;

/* @var $this yii\web\View */
/* @var $model app\models\Solution */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Status'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="table-responsive">
    <table class="table table-bordered table-rank">
        <thead>
        <tr>
            <th width="120px">Run ID</th>
            <th width="120px">Author</th>
            <th width="200px">Problem</th>
            <th width="80px">Lang</th>
            <th>Verdict</th>
            <th>Time</th>
            <th>Memory</th>
            <th>Code Length</th>
            <th>Submit Time</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th><?= $model->id ?></th>
            <th><?= Html::a(Html::encode($model->user->nickname), ['/user/view', 'id' => $model->user_id]) ?></th>
            <th><?= Html::a(Html::encode($model->problem->title), ['/problem/view', 'id' => $model->problem_id]) ?></th>
            <th><?= Solution::getLanguageList($model->language) ?></th>
            <th><?= Solution::getResultList($model->result) ?></th>
            <th><?= $model->time ?> MS</th>
            <th><?= $model->memory ?> KB</th>
            <th><?= $model->code_length ?></th>
            <th><?= $model->created_at ?></th>
        </tr>
        </tbody>
    </table>
</div>
<hr>

<h3>Tests(<?= $model->getPassedTestCount() ?>/<?= $model->getTestCount() ?>):</h3>
<h3>
<?php for ($i = 1; $i <= $model->getPassedTestCount(); $i++): ?>
    <?php if ($i <= $model->getTestCount()) :?>
        <span class="glyphicon glyphicon-ok-circle text-success"></span>
    <?php else: ?>
        <span class="glyphicon glyphicon-remove-circle text-danger"></span>
    <?php endif; ?>
<?php endfor; ?>
<?php if ($model->getPassedTestCount() < $model->getTestCount()) :?>
    <span class="glyphicon glyphicon-remove-circle text-danger"></span>
<?php endif; ?>
</h3>

<hr>
<h3>Source:</h3>
<pre><?= Html::encode($model->source) ?></pre>

<?php if ($model->solutionInfo != null): ?>
    <hr>
    <h3>Run Info:</h3>
    <pre><?= \yii\helpers\HtmlPurifier::process($model->solutionInfo->error) ?></pre>
<?php endif; ?>
