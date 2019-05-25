<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\bootstrap\Nav;
use app\models\Contest;
use app\models\Homework;

/* @var $this yii\web\View */
/* @var $model app\models\Homework */

$this->title = Html::encode($model->title);
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Group'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => Html::encode($model->group->name), 'url' => ['/group/view', 'id' => $model->group->id]];
$this->params['breadcrumbs'][] = ['label' => Html::encode($model->title), 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = Yii::t('app', 'Setting');
$this->params['model'] = $model;
$problems = $model->problems;
?>
<div class="homework-update">
    <div class="col-md-9">

        <h1><?= Html::encode($model->title) ?></h1>

        <div class="homework-form">

            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

            <?= $form->field($model, 'start_time')->widget('app\widgets\laydate\LayDate', [
                'clientOptions' => [
                    'istoday' => true,
                    'type' => 'datetime'
                ]
            ]) ?>

            <?= $form->field($model, 'end_time')->widget('app\widgets\laydate\LayDate', [
                'clientOptions' => [
                    'istoday' => true,
                    'type' => 'datetime'
                ]
            ]) ?>

            <?= $form->field($model, 'description')->widget('app\widgets\ckeditor\CKeditor'); ?>

            <?= $form->field($model, 'editorial')->widget('app\widgets\ckeditor\CKeditor'); ?>

            <?= $form->field($model, 'type')->radioList([
                Contest::TYPE_RANK_SINGLE => Yii::t('app', 'Single Ranked'),
                Contest::TYPE_RANK_GROUP => Yii::t('app', 'ICPC'),
                Contest::TYPE_HOMEWORK => Yii::t('app', 'Homework'),
                Contest::TYPE_OI => Yii::t('app', 'OI'),
                Contest::TYPE_IOI => Yii::t('app', 'IOI'),
            ])->hint('不同类型的区别只在于榜单的排名方式。详见：' . Html::a('比赛类型', ['/wiki/contest'], ['target' => '_blank']) . '。如需使用OI比赛，请在后台设置页面启用OI模式。') ?>

            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Save'), ['class' => 'btn btn-success']) ?>
            </div>

            <?php ActiveForm::end(); ?>

        </div>

        <hr>
        <h3>
            <?= Yii::t('app', 'Announcements') ?>
            <?php Modal::begin([
                'header' => '<h3>'.Yii::t('app','Make an announcement').'</h3>',
                'toggleButton' => ['label' => Yii::t('app', 'Create'), 'class' => 'btn btn-success'],
            ]); ?>
            <?php $form = ActiveForm::begin(); ?>

            <?= $form->field($newAnnouncement, 'content')->textarea(['rows' => 6]) ?>

            <div class="form-group">
                <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
            </div>
            <?php ActiveForm::end(); ?>

            <?php Modal::end(); ?>
        </h3>

        <?= \yii\grid\GridView::widget([
            'dataProvider' => $announcements,
            'columns' => [
                'content:ntext',
                'created_at:datetime',
            ],
        ]) ?>

        <hr>
        <h3><?= Yii::t('app', 'Problems') ?></h3>
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th width="70px">#</th>
                    <th width="120px">Problem ID</th>
                    <th><?= Yii::t('app', 'Problem Name') ?></th>
                    <th width="200px"><?= Yii::t('app', 'Operation') ?></th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($problems as $key => $p): ?>
                    <tr>
                        <th><?= Html::a(chr(65 + $key), ['view', 'id' => $model->id, 'action' => 'problem', 'problem_id' => $key]) ?></th>
                        <th><?= Html::a($p['problem_id'], '') ?></th>
                        <td><?= Html::a(Html::encode($p['title']), ['view', 'id' => $model->id, 'action' => 'problem', 'problem_id' => $key]) ?></td>
                        <th>
                            <?php Modal::begin([
                                'header' => '<h3>'. Yii::t('app','Modify') . ' : ' . chr(65 + $key) . '</h3>',
                                'toggleButton' => ['label' => Yii::t('app','Modify'), 'class' => 'btn btn-success'],
                            ]); ?>

                            <?= Html::beginForm(['/homework/updateproblem', 'id' => $model->id]) ?>

                            <div class="form-group">
                                <?= Html::label(Yii::t('app', 'Current Problem ID'), 'problem_id') ?>
                                <?= Html::textInput('problem_id', $p['problem_id'],['class' => 'form-control', 'readonly' => 1]) ?>
                            </div>

                            <div class="form-group">
                                <?= Html::label(Yii::t('app', 'New Problem ID'), 'new_problem_id') ?>
                                <?= Html::textInput('new_problem_id', $p['problem_id'],['class' => 'form-control']) ?>
                            </div>

                            <div class="form-group">
                                <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
                            </div>
                            <?= Html::endForm(); ?>

                            <?php Modal::end(); ?>
                            <?php if ($key == count($problems) - 1): ?>
                                <?= Html::a(Yii::t('app', 'Delete'), [
                                    'deleteproblem',
                                    'id' => $model->id,
                                    'pid' => $p['problem_id']
                                ],[
                                    'class' => 'btn btn-danger',
                                    'data' => [
                                        'confirm' => Yii::t('app', 'Are you sure you want to delete this item?'),
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            <?php endif; ?>
                        </th>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <th></th>
                    <th></th>
                    <th>
                        <?php Modal::begin([
                            'header' => '<h3>' . Yii::t('app','Add a problem') . '</h3>',
                            'toggleButton' => ['label' => Yii::t('app','Add a problem'), 'class' => 'btn btn-success'],
                        ]); ?>

                        <?= Html::beginForm(['/homework/addproblem', 'id' => $model->id]) ?>

                        <div class="form-group">
                            <?= Html::label(Yii::t('app', 'Problem ID'), 'problem_id') ?>
                            <?= Html::textInput('problem_id', '',['class' => 'form-control']) ?>
                        </div>

                        <div class="form-group">
                            <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
                        </div>
                        <?= Html::endForm(); ?>

                        <?php Modal::end(); ?>
                    </th>
                    <th></th>
                </tr>
                </tbody>
            </table>
        </div>
    </div>

</div>
