<?php

use yii\helpers\Html;
use app\models\Solution;

/* @var $this yii\web\View */
/* @var $status app\modules\polygon\models\PolygonStatus */

$this->title = $status->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['model'] = $model;
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
            <th>Submit Time</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th><?= $status->id ?></th>
            <th><?= Html::a(Html::encode($status->user->nickname), ['/user/view', 'id' => $status->created_by]) ?></th>
            <th><?= Html::encode($model->title) ?></th>
            <th><?= Solution::getLanguageList($status->language) ?></th>
            <th><?= Solution::getResultList($status->result) ?></th>
            <th><?= $status->time ?> MS</th>
            <th><?= $status->memory ?> KB</th>
            <th><?= $status->created_at ?></th>
        </tr>
        </tbody>
    </table>
</div>
<hr>
<h3>Source:</h3>
<div class="pre"><p><?= Html::encode($status->source) ?></p></div>

<?php if ($status->info != null): ?>
    <hr>
    <h3>Run Info:</h3>
    <pre><?= \yii\helpers\HtmlPurifier::process($status->info) ?></pre>
<?php endif; ?>
