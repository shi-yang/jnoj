<?php

use yii\helpers\Html;
use app\models\Solution;

/* @var $this yii\web\View */
/* @var $solutions array */
/* @var $model app\models\Problem */
?>
<div class="solutions-view">

    <h1>
        <?= Html::encode($model->title) ?>
    </h1>
    <p class="text-muted">提示：题目的测试状态将不会在前台展示．不会出现泄题情况</p>
    <div class="table-responsive">
        <table class="table table-bordered table-rank">
            <thead>
            <tr>
                <th width="60px">Submited Time</th>
                <th width="150px">Result</th>
                <th width="150px">Language</th>
                <th width="70px">Time</th>
                <th width="80px">Memory</th>
                <th width="150px">Code Length</th>
            </tr>
            </thead>
            <tbody>
            <?php foreach ($solutions as $solution): ?>
                <tr>
                    <th>
                        <?= date('Y-m-d h:i', $solution['created_at']) ?>
                    </th>
                    <th>
                        <?= Html::a(Solution::getResultList($solution['result']), ['problem/result', 'id' => $solution['solution_id']], ['target' => '_blank']); ?>
                    </th>
                    <th>
                        <?= Html::a(Solution::getLanguageList($solution['language']), ['problem/source', 'id' => $solution['solution_id']], ['target' => '_blank']) ?>
                    </th>
                    <th>
                        <?= $solution['time'] ?>
                    </th>
                    <th>
                        <?= $solution['memory'] ?>
                    </th>
                    <th>
                        <?= $solution['code_length'] ?>
                    </th>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
