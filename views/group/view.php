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
$scoreboardFrozenTime = Yii::$app->setting->get('scoreboardFrozenTime') / 3600;
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

                    <?= $form->field($newContest, 'lock_board_time')->widget('app\widgets\laydate\LayDate', [
                        'clientOptions' => [
                            'istoday' => true,
                            'type' => 'datetime'
                        ]
                    ])->hint("如果不需要封榜请留空，当前会在比赛结束{$scoreboardFrozenTime}小时后才会自动在前台页面解除封榜限制。
                        如需提前结束封榜也可选择清空该表单项。使用封榜功能，后台管理界面的比赛榜单仍然处于实时榜单。
                        <p class='text-danger'>注意：比赛类型为OI时，如果不填写“封榜时间”则会成为实时榜单。如需要非实时榜单，则填写为开始时间或开始时间之前的时间即可。</p>
                    ") ?>

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
                    ],
                    [
                        'class' => 'yii\grid\ActionColumn',
                        'template' => '{user-update} {user-delete}',
                        'buttons' => [
                            'user-update' => function ($url, $model, $key) {
                                $options = [
                                    'title' => Yii::t('yii', 'Update'),
                                    'aria-label' => Yii::t('yii', 'Update'),
                                    'data-pjax' => '0',
                                    'onclick' => 'return false',
                                    'data-click' => "user-manager"
                                ];
                                return Html::a('<span class="glyphicon glyphicon-pencil"></span>', $url, $options);
                            },
                            'user-delete' => function ($url, $model, $key) {
                                $options = [
                                    'title' => Yii::t('yii', 'Delete'),
                                    'aria-label' => Yii::t('yii', 'Delete'),
                                    'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                    'data-method' => 'post',
                                    'data-pjax' => '0',
                                ];
                                return Html::a('<span class="glyphicon glyphicon-trash"></span>', $url, $options);
                            }
                        ],
                        'visible' => $model->hasPermission(),
                        'options' => ['width' => '90px']
                    ]
                ],
            ]); ?>
        </div>
    </div>
</div>
<?php
$js = <<<EOF
$('[data-click=user-manager]').click(function() {
    $.ajax({
        url: $(this).attr('href'),
        type:'post',
        error: function(){alert('error');},
        success:function(html){
        $('#solution-content').html(html);
        $('#solution-info').modal('show');
    }
    });
});
EOF;
$this->registerJs($js);
?>
<?php Modal::begin([
    'options' => ['id' => 'solution-info']
]); ?>
    <div id="solution-content">
    </div>
<?php Modal::end(); ?>