<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;


/* @var $this yii\web\View */
/* @var $model app\models\Problem */

$this->title = Yii::t('app', 'Import Problem');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$maxFileSize = min(ini_get("upload_max_filesize"),ini_get("post_max_size"));
?>
<div class="problem-import">

    <h1><?= Html::encode($this->title) ?></h1>
    <hr>
    <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data', 'target' => '_blank']]) ?>

    <?= $form->field($model, 'problemFile')->fileInput()
        ->hint("提交文件为zip或者xml格式，目前只支持从hustoj导出的题目。
        文件限制大小：{$maxFileSize}，该限制为系统限制，如需修改该大小限制，请修改php.ini文件的post_max_size、upload_max_filesize选项。")?>

    <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-success']) ?>

    <?php ActiveForm::end() ?>

</div>
