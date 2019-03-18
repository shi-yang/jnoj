<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\modules\polygon\models\Problem */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['model'] = $model;

$model->setSamples();
?>
<p>
    如果题目需要配置子任务的，可以在下面填写子任务的配置。参考：<?= Html::a('子任务配置要求', ['/wiki/oi']) ?>
</p>

<?php if (Yii::$app->setting->get('oiMode')): ?>
    <hr>

    <p>
        注：当前尚未开发验证配置文件正确性的功能，在 polygon 的验题中也尚未支持子任务运行，如需测试子任务的运行情况，需要同步到后台题库中测试。
    </p>

    <?= Html::beginForm() ?>

    <div class="form-group">
        <?= Html::label(Yii::t('app', 'Subtask'), 'subtaskContent', ['class' => 'sr-only']) ?>

        <?= \app\widgets\codemirror\CodeMirror::widget(['name' => 'subtaskContent', 'value' => $subtaskContent]);  ?>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?= Html::endForm(); ?>
<?php else: ?>
    <p>当前 OJ 运行模式不是 OI 模式，要启用子任务编辑，需要在后台设置页面启用 OI 模式。</p>
<?php endif; ?>
