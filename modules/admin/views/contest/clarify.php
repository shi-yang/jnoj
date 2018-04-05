<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $form yii\widgets\ActiveForm */
/* @var $new_clarify app\models\Discuss */

$this->title = Html::encode($model->title);
$this->params['model'] = $model;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contests'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['view', 'id' => $model->id]];

if ($discuss != null) {
    echo $this->render('_clarify_view', [
        'clarify' => $discuss,
        'new_clarify' => $new_clarify
    ]);
    return;
}
?>
<h3><?= Html::encode($model->title) ?></h3>
<div style="padding-top: 20px">

    <?= GridView::widget([
        'dataProvider' => $clarifies,
        'columns' => [
            [
                'attribute' => 'username',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a($model->user->username, ['/user/view', 'id' => $model->user->id]);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'title',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a($model->title, [
                        'contest/clarify',
                        'id' => $model->entity_id,
                        'cid' => $model->id
                    ]);
                },
                'format' => 'raw'
            ],
            'created_at',
            'updated_at'
        ]
    ]); ?>

</div>
