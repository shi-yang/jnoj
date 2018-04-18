<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Problem */

$this->title = Html::encode($model->title);
$files = $model->getDataFiles();
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
?>
<div class="problem-header">
    <?= \yii\bootstrap\Nav::widget([
        'options' => ['class' => 'nav nav-pills'],
        'items' => [
            ['label' => Yii::t('app', 'Preview'), 'url' => ['/admin/problem/view', 'id' => $model->id]],
            ['label' => Yii::t('app', 'Edit'), 'url' => ['/admin/problem/update', 'id' => $model->id]],
            ['label' => Yii::t('app', 'Tests Data'), 'url' => ['/admin/problem/test-data', 'id' => $model->id]],
        ],
    ]) ?>
</div>
<div class="solutions-view">
    <h1>
        <?= Html::encode($model->title) ?>
    </h1>

    <p class="bg-danger">
        一个标准输入文件对应一个标准输出文件，输入文件以＂.in＂结尾，输出文件以＂.out＂结尾，文件名任意取，
        但输入文件跟输出文件的文件名必须一一对应．比如一组样例: 输入文件文件名"apple.in"，输出文件文件名"apple.out"．
        如有多个测试点，可以分开不同的文件上传
    </p>

    <div class="row table-responsive">
        <div class="col-md-6">
            <table class="table table-bordered table-rank">
                <caption>标准输入文件</caption>
                <tr>
                    <th>文件名</th>
                    <th>大小(bytes)</th>
                    <th>修改时间</th>
                    <th>操作</th>
                </tr>
                <?php foreach ($files as $file): ?>
                    <?php
                    if (!strpos($file['name'], '.in'))
                        continue;
                    ?>
                    <tr>
                        <th><?= $file['name'] ?></th>
                        <th><?= $file['size'] ?></th>
                        <th><?= date('Y-m-d H:i', $file['time']) ?></th>
                        <th>
                            <a href="<?= Url::toRoute(['/admin/problem/viewfile', 'id' => $model->id, 'name' => $file['name']]) ?>" target="_blank">
                                <span class="glyphicon glyphicon-eye-open"></span>
                                <?= Yii::t('app', 'View') ?>
                            </a>
                            <a href="<?= Url::toRoute(['/admin/problem/deletefile', 'id' => $model->id, 'name' => $file['name']]) ?>">
                                <span class="glyphicon glyphicon-remove"></span>
                                <?= Yii::t('app', 'Delete') ?>
                            </a>
                        </th>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-bordered table-rank">
                <caption>标准输出文件</caption>
                <tr>
                    <th>文件名</th>
                    <th>大小(bytes)</th>
                    <th>修改时间</th>
                    <th>操作</th>
                </tr>
                <?php foreach ($files as $file): ?>
                    <?php
                    if (!strpos($file['name'], '.out'))
                        continue;
                    ?>
                    <tr>
                        <th><?= $file['name'] ?></th>
                        <th><?= $file['size'] ?></th>
                        <th><?= date('Y-m-d H:i', $file['time']) ?></th>
                        <th>
                            <a href="<?= Url::toRoute(['/admin/problem/viewfile', 'id' => $model->id, 'name' => $file['name']]) ?>" target="_blank">
                                <span class="glyphicon glyphicon-eye-open"></span>
                                <?= Yii::t('app', 'View') ?>
                            </a>
                            <a href="<?= Url::toRoute(['/admin/problem/deletefile', 'id' => $model->id, 'name' => $file['name']]) ?>">
                                <span class="glyphicon glyphicon-remove"></span>
                                <?= Yii::t('app', 'Delete') ?>
                            </a>
                        </th>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
