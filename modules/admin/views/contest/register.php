<?php

use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\bootstrap\Modal;
use yii\widgets\ActiveForm;
use app\models\Contest;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $generatorForm app\modules\admin\models\GenerateUserForm */

$this->title = $model->title;
$contest_id = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Contests'), 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['view', 'id' => $model->id]];
?>
<h1><?= Html::encode($model->title) ?></h1>

<?php Modal::begin([
    'header' => '<h2>Add a user to the contest</h2>',
    'toggleButton' => ['label' => 'Add User', 'class' => 'btn btn-success'],
]);?>
<?= Html::beginForm(['contest/register', 'id' => $model->id]) ?>
    <div class="form-group">
        <?= Html::label(Yii::t('app', 'User ID'), 'user') ?>
        <?= Html::textInput('user', '',['class' => 'form-control', 'placeholder' => 'User Id or Username or Email']) ?>
    </div>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?= Html::endForm(); ?>
<?php Modal::end(); ?>

<?php if ($model->scenario == Contest::SCENARIO_OFFLINE): ?>
    <?php Modal::begin([
        'header' => '<h2>' . Yii::t('app', 'Generate user for the contest') . '</h2>',
        'toggleButton' => ['label' => Yii::t('app', 'Generate user for the contest'), 'class' => 'btn btn-success'],
    ]);?>
        <p class="text-muted">在线下举行比赛时，可在此处批量创建账号。</p>
        <p class="text-danger">注意：在同一场比赛中，重复使用此功能会删除之前已经生成的帐号，请勿在比赛开始后进行此操作。</p>
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($generatorForm, 'prefix')->textInput(['maxlength' => true, 'value' => 't' . $model->id, 'disabled' => true]) ?>

        <?= $form->field($generatorForm, 'team_number')->textInput(['maxlength' => true, 'value' => '50']) ?>

        <?= $form->field($generatorForm, 'names')->textarea(['rows' => 10])->hint('请把所有队伍名称复制到此处，一个名字占据一行，请自行删除多余的空行')  ?>

        <div class="form-group">
            <p>生成账户过程需等待一段时间，在此期间请勿刷新页面</p>
            <?= Html::submitButton(Yii::t('app', 'Generate'), ['class' => 'btn btn-success']) ?>
        </div>

        <?php ActiveForm::end(); ?>
    <?php Modal::end(); ?>
    <?= Html::a('Print User and Password', ['contest/printuser', 'id' => $model->id], ['class' => 'btn btn-default', 'target' => '_blank']) ?>
<?php endif; ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'columns' => [
        ['class' => 'yii\grid\SerialColumn'],
        [
            'attribute' => Yii::t('app', 'Username'),
            'value' => function ($model, $key, $index, $column) {
                return Html::a($model->user->username, ['/user/view', 'id' => $model->user->id]);
            },
            'format' => 'raw'
        ],
        [
            'attribute' => Yii::t('app', 'Nickname'),
            'value' => function ($model, $key, $index, $column) {
                return Html::a($model->user->nickname, ['/user/view', 'id' => $model->user->id]);
            },
            'format' => 'raw'
        ],
        [
            'attribute' => 'user_password',
            'value' => function ($contestUser, $key, $index, $column) use ($model) {
                if ($model->scenario == Contest::SCENARIO_OFFLINE) {
                    return $contestUser->user_password;
                } else {
                    return '线上赛无法提供密码';
                }
            },
            'format' => 'raw',
            'visible' => $model->scenario == Contest::SCENARIO_OFFLINE
        ],
        [
            'class' => 'yii\grid\ActionColumn',
            'template' => '{delete}',
            'buttons' => [
                'delete' => function ($url, $model, $key) use ($contest_id) {
                    $options = [
                        'title' => Yii::t('yii', 'Delete'),
                        'aria-label' => Yii::t('yii', 'Delete'),
                        'data-confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                        'data-method' => 'post',
                        'data-pjax' => '0',
                    ];
                    return Html::a('<span class="glyphicon glyphicon-trash"></span>', Url::toRoute(['contest/register', 'id' => $contest_id, 'uid' => $model->user->id]), $options);
                },
            ]
        ],
    ],
]); ?>
