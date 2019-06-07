<?php

use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $model app\models\Problem */

$this->title = Html::encode($model->title);
$files = $model->getDataFiles();
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->title, 'url' => ['view', 'id' => $model->id]];
$this->params['model'] = $model;
?>
<div class="solutions-view">
    <h1>
        <?= Html::encode($model->title) ?>
    </h1>

    <p>
        一个标准输入文件对应一个标准输出文件，输入文件以<code>.in</code>结尾，输出文件以<code>.out</code>或者
        <code>.ans</code>结尾，文件名任意取，
        但输入文件跟输出文件的文件名必须一一对应．比如一组样例: 输入文件文件名<code>apple.in</code>，
        输出文件文件名<code>apple.out</code> 或者 <code>apple.ans</code>。
        如有多个测试点，可以分开不同的文件上传
    </p>

    <?= \app\widgets\webuploader\MultiImage::widget() ?>


    <div class="row table-responsive">
        <div class="col-md-12">
            <?php if (extension_loaded('zip')): ?>
                <p>
                    <?= Html::a('下载全部数据', ['download-data', 'id' => $model->id], ['class' => 'btn btn-success']); ?>
                </p>
            <?php else: ?>
                <p>
                    服务器未启用 php-zip 扩展，如需下载测试数据，请安装 php-zip　扩展。
                </p>
            <?php endif; ?>
        </div>
        <div class="col-md-6">
            <table class="table table-bordered table-rank">
                <caption>
                    标准输入文件
                    <a href="<?= Url::toRoute(['/admin/problem/deletefile', 'id' => $model->id,'name' => 'in']) ?>" onclick="return confirm('确定删除全部输入文件？');">
                        删除全部输入文件
                    </a>
                </caption>
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
                            <a href="<?= Url::toRoute(['/admin/problem/viewfile', 'id' => $model->id,'name' => $file['name']]) ?>"
                               target="_blank"
                               title="<?= Yii::t('app', 'View') ?>">
                                <span class="glyphicon glyphicon-eye-open"></span>
                            </a>
                            &nbsp;
                            <a href="<?= Url::toRoute(['/admin/problem/deletefile', 'id' => $model->id,'name' => $file['name']]) ?>"
                               title="<?= Yii::t('app', 'Delete') ?>">
                                <span class="glyphicon glyphicon-remove"></span>
                            </a>
                        </th>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
        <div class="col-md-6">
            <table class="table table-bordered table-rank">
                <caption>
                    标准输出文件
                    <a href="<?= Url::toRoute(['/admin/problem/deletefile', 'id' => $model->id, 'name' => 'out']) ?>" onclick="return confirm('确定删除全部输出文件？');">
                        删除全部输出文件
                    </a>
                </caption>
                <tr>
                    <th>文件名</th>
                    <th>大小(bytes)</th>
                    <th>修改时间</th>
                    <th>操作</th>
                </tr>
                <?php foreach ($files as $file): ?>
                    <?php
                    if (!strpos($file['name'], '.out') && !strpos($file['name'], '.ans'))
                        continue;
                    ?>
                    <tr>
                        <th><?= $file['name'] ?></th>
                        <th><?= $file['size'] ?></th>
                        <th><?= date('Y-m-d H:i', $file['time']) ?></th>
                        <th>
                            <a href="<?= Url::toRoute(['/admin/problem/viewfile', 'id' => $model->id,'name' => $file['name']]) ?>"
                               target="_blank"
                               title="<?= Yii::t('app', 'View') ?>">
                                <span class="glyphicon glyphicon-eye-open"></span>
                            </a>
                            &nbsp;
                            <a href="<?= Url::toRoute(['/admin/problem/deletefile', 'id' => $model->id,'name' => $file['name']]) ?>"
                               title="<?= Yii::t('app', 'Delete') ?>">
                                <span class="glyphicon glyphicon-remove"></span>
                            </a>
                        </th>
                    </tr>
                <?php endforeach; ?>
            </table>
        </div>
    </div>
</div>
