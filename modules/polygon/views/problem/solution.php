<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Solution;

/* @var $this yii\web\View */
/* @var $model app\modules\polygon\models\Problem */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['model'] = $model;

$model->setSamples();
?>
<p>
    请在此页面提供一个“标程”（即解答该问题的正确代码程序）。它将被用来生成测试数据的标准输出。
</p>
<?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'solution_lang')->dropDownList(Solution::getLanguageList()) ?>

    <?= $form->field($model, 'solution_source')->widget('app\widgets\codemirror\CodeMirror'); ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-primary']) ?>
    </div>
<?php ActiveForm::end(); ?>
