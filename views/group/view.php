<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use app\models\GroupUser;
use app\models\Contest;

/* @var $this yii\web\View */
/* @var $model app\models\Group */
/* @var $contestDataProvider yii\data\ActiveDataProvider */
/* @var $userDataProvider yii\data\ActiveDataProvider */
/* @var $newContest app\models\Contest */
/* @var $newGroupUser app\models\GroupUser */

$this->title = $model->name;
\yii\web\YiiAsset::register($this);
?>
<div class="group-view">
    <div class="row">
        <div class="col-md-3">
            <h1><?= Html::a(Html::encode($this->title), ['/group/view', 'id' => $model->id]) ?></h1>
            <?php if ($model->role == GroupUser::ROLE_LEADER): ?>
            <?= Html::a(Yii::t('app', 'Setting'), ['/group/update', 'id' => $model->id], ['class' => 'btn btn-default btn-block']) ?>
            <?php endif; ?>
            <hr>
            <p>
                <?= Yii::$app->formatter->asHtml($model->description); ?>
            </p>
            <hr>
            <p><?= Yii::t('app', 'Join Policy') ?>: <?= $model->getJoinPolicy() ?></p>
            <p><?= Yii::t('app', 'Status') ?>: <?= $model->getStatus() ?></p>
        </div>
        <div class="col-md-9">
            <div>
                <h2 style="display: inline">
                    <?= Yii::t('app', 'Homework'); ?>
                </h2>
                <?php if ($model->hasPermission()): ?>
                <?php Modal::begin([
                    'header' => '<h3>' . Yii::t('app', 'Create') . '</h3>',
                    'toggleButton' => [
                        'label' => Yii::t('app', 'Create'),
                        'tag' => 'a',
                        'style' => 'cursor:pointer;'
                    ]
                ]); ?>
                    <?php $form = ActiveForm::begin(); ?>
                    <?= $form->field($newContest, 'title')->textInput(['maxlength' => true, 'autocomplete' => 'off']) ?>
                    <?= $form->field($newContest, 'start_time')->widget('app\widgets\laydate\LayDate', [
                        'clientOptions' => [
                            'istoday' => true,
                            'type' => 'datetime'
                        ],
                        'options' => ['autocomplete' => 'off']
                    ]) ?>
                    <?= $form->field($newContest, 'end_time')->widget('app\widgets\laydate\LayDate', [
                        'clientOptions' => [
                            'istoday' => true,
                            'type' => 'datetime'
                        ],
                        'options' => ['autocomplete' => 'off']
                    ]) ?>
                    <?= $form->field($newContest, 'type')->radioList([
                        Contest::TYPE_RANK_SINGLE => Yii::t('app', 'Single Ranked'),
                        Contest::TYPE_RANK_GROUP => Yii::t('app', 'Group Ranked'),
                        Contest::TYPE_OI => Yii::t('app', 'OI'),
                    ])->hint('不同类型的区别只在于榜单的排名方式。详见：' . Html::a('比赛类型', ['/wiki/contest'], ['target' => '_blank']) . '。如需使用OI比赛，请在后台设置页面启用OI模式。') ?>
                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                <?php Modal::end(); ?>
                <?php endif; ?>
            </div>
            <?= GridView::widget([
                'layout' => '{items}{pager}',
                'dataProvider' => $contestDataProvider,
                'options' => ['class' => 'table-responsive'],
                'columns' => [
                    [
                        'attribute' => 'title',
                        'value' => function ($model, $key, $index, $column) {
                            return Html::a(Html::encode($model->title), ['/contest/view', 'id' => $key]);
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'status',
                        'value' => function ($model, $key, $index, $column) {
                            $link = Html::a(Yii::t('app', 'Register »'), ['/contest/register', 'id' => $model->id]);
                            if (!Yii::$app->user->isGuest && $model->isUserInContest()) {
                                $link = '<span class="well-done">' . Yii::t('app', 'Registration completed') . '</span>';
                            }
                            if ($model->status == Contest::STATUS_VISIBLE &&
                                $model->getRunStatus() != Contest::STATUS_ENDED &&
                                $model->scenario == Contest::SCENARIO_ONLINE) {
                                $column = $model->getRunStatus(true) . ' ' . $link;
                            } else {
                                $column = $model->getRunStatus(true);
                            }
                            $userCount = $model->getContestUserCount();
                            return $column . ' ' . Html::a(' <span class="glyphicon glyphicon-user"></span>x'. $userCount, ['/contest/user', 'id' => $model->id]);
                        },
                        'format' => 'raw',
                        'options' => ['width' => '220px']
                    ],
                    [
                        'attribute' => 'start_time',
                        'options' => ['width' => '150px']
                    ],
                    [
                        'attribute' => 'end_time',
                        'options' => ['width' => '150px']
                    ],
                ],
            ]); ?>

            <div>
                <h2 style="display: inline">
                    <?= Yii::t('app', 'Member'); ?>
                </h2>
                <?php if ($model->hasPermission()): ?>
                    <?php Modal::begin([
                        'header' => '<h3>' . Yii::t('app', 'Invite Member') . '</h3>',
                        'toggleButton' => [
                            'label' => Yii::t('app', 'Invite Member'),
                            'tag' => 'a',
                            'style' => 'cursor:pointer;'
                        ]
                    ]); ?>
                    <?php $form = ActiveForm::begin(); ?>
                    <?= $form->field($newGroupUser, 'username')->textInput(['maxlength' => true, 'autocomplete' => 'off']) ?>
                    <div class="form-group">
                        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
                    </div>
                    <?php ActiveForm::end(); ?>
                    <?php Modal::end(); ?>
                <?php endif; ?>
            </div>
            <?= GridView::widget([
                'layout' => '{items}{pager}',
                'dataProvider' => $userDataProvider,
                'options' => ['class' => 'table-responsive'],
                'columns' => [
                    [
                        'attribute' => 'role',
                        'value' => function ($model, $key, $index, $column) {
                            return $model->getRole(true);
                        },
                        'format' => 'raw',
                        'options' => ['width' => '150px']
                    ],
                    [
                        'attribute' => Yii::t('app', 'Nickname'),
                        'value' => function ($model, $key, $index, $column) {
                            return Html::a(Html::encode($model->user->nickname), ['/user/view', 'id' => $model->user->id]);
                        },
                        'format' => 'raw',
                    ],
                    [
                        'attribute' => 'created_at',
                        'value' => function ($model, $key, $index, $column) {
                            return Yii::$app->formatter->asRelativeTime($model->created_at);
                        },
                        'options' => ['width' => '150px']
                    ]
                ],
            ]); ?>
        </div>
    </div>
</div>
