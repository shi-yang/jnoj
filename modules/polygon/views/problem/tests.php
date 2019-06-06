<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\ActiveForm;
use app\modules\polygon\models\Problem;

/* @var $this yii\web\View */
/* @var $model app\modules\polygon\models\Problem */
/* @var $solutionStatus array */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['model'] = $model;

$files = $model->getDataFiles();
?>
<p>
    该页面用于生成、编辑程序的测试数据。
</p>
<hr>
<?php if (extension_loaded('zip')): ?>
    <p>
        <?= Html::a('下载全部数据', ['download-data', 'id' => $model->id], ['class' => 'btn btn-success']); ?>
    </p>
<?php else: ?>
    <p>
        服务器未启用 php-zip 扩展，如需下载测试数据，请安装 php-zip　扩展。
    </p>
<?php endif; ?>
<div class="table-responsive">
    <table class="table table-bordered table-rank">
        <thead>
        <tr>
            <th width="80px">ID</th>
            <th>Verdict</th>
            <th>Time</th>
            <th>Memory</th>
            <th>Submit Time</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th><?= Html::a($solutionStatus['id'], ['/polygon/problem/solution-detail', 'id' => $model->id, 'sid' => $solutionStatus['id']]) ?></th>
            <th><?= Problem::getResultList($solutionStatus['result']) ?></th>
            <th><?= $solutionStatus['time'] ?>MS</th>
            <th><?= $solutionStatus['memory'] ?>KB</th>
            <th><?= $solutionStatus['created_at'] ?></th>
        </tr>
        </tbody>
    </table>
</div>
<div class="row">
    <div class="col-md-4">
        <p>
            测试的输入文件需自行制作(<a href="<?= Url::toRoute(['/wiki/problem']) ?>#infile" target="_blank">如何快速生成？</a>)，
            然后在下边表格上传。为文本文件，文件名称必须以 <code>in</code> 最为后缀，例如 <code>apple.in</code>。</p>
        <p>
            测试的输出文件在上传输入文件后，点击此处
            <?= Html::a(Yii::t('app', 'Run'), ['/polygon/problem/run', 'id' => $model->id], ['class' => 'btn btn-success']) ?>
            按钮，
            会根据提供的“<?= Html::a(Yii::t('app', 'Solution'), ['/polygon/problem/solution', 'id' => $model->id]) ?>”自行生成。
        </p>
        <p class="text-info">
            上传完成后刷新页面查看结果
        </p>
        <hr>
        <?= \app\widgets\webuploader\MultiImage::widget() ?>
    </div>
    <div class="col-md-8">
        <div class="row">
            <div class="col-md-6">
                <table class="table">
                    <caption>
                        标准输入文件
                        <a href="<?= Url::toRoute(['/polygon/problem/deletefile', 'id' => $model->id,'name' => 'in']) ?>" onclick="return confirm('确定删除全部输入文件？');">
                            删除全部输入文件
                        </a>
                    </caption>
                    <tr>
                        <th>文件名</th>
                        <th>大小</th>
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
                                <a href="<?= Url::toRoute(['/polygon/problem/viewfile', 'id' => $model->id,'name' => $file['name']]) ?>"
                                   target="_blank"
                                   title="<?= Yii::t('app', 'View') ?>">
                                    <span class="glyphicon glyphicon-eye-open"></span>
                                </a>
                                &nbsp;
                                <a href="<?= Url::toRoute(['/polygon/problem/deletefile', 'id' => $model->id,'name' => $file['name']]) ?>"
                                   title="<?= Yii::t('app', 'Delete') ?>">
                                    <span class="glyphicon glyphicon-remove"></span>
                                </a>
                            </th>
                        </tr>
                    <?php endforeach; ?>
                </table>
            </div>
            <div class="col-md-6">
                <table class="table">
                    <caption>
                        标准输出文件
                        <a href="<?= Url::toRoute(['/polygon/problem/deletefile', 'id' => $model->id, 'name' => 'out']) ?>" onclick="return confirm('确定删除全部输出文件？');">
                            删除全部输出文件
                        </a>
                    </caption>
                    <tr>
                        <th>文件名</th>
                        <th>大小</th>
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
                                <a href="<?= Url::toRoute(['/polygon/problem/viewfile', 'id' => $model->id,'name' => $file['name']]) ?>"
                                   target="_blank"
                                   title="<?= Yii::t('app', 'View') ?>">
                                    <span class="glyphicon glyphicon-eye-open"></span>
                                </a>
                                &nbsp;
                                <a href="<?= Url::toRoute(['/polygon/problem/deletefile', 'id' => $model->id,'name' => $file['name']]) ?>"
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
</div>
