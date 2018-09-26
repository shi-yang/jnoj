<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $form yii\widgets\ActiveForm */
/* @var $data array */

$this->title = Html::encode($model->title);
$this->params['model'] = $model;
?>
<div class="contest-editorial">
    <div style="padding: 50px">
        <?php
        if ($model->editorial != NULL) {
            echo Yii::$app->formatter->asHtml($model->editorial);
        } else {
            echo '出题人去火星旅游了，这里什么也没有～';
        }
        ?>
    </div>
</div>
