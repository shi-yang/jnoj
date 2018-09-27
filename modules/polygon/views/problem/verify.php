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
$model->spj_lang = Yii::$app->user->identity->language;
?>
<p>
    该页面用于验证测试数据。该功能尚在开发中。。。
</p>
<hr>
<?php $form = ActiveForm::begin(); ?>

<?= $form->field($model, 'spj_lang')->dropDownList(Solution::getLanguageList()) ?>

<?= $form->field($model, 'spj_source')->textarea(['rows' => 20, 'autocomplete'=>'off']) ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
    </div>
<?php ActiveForm::end(); ?>
