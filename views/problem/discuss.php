<?php

use app\models\User;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;
use shiyang\infinitescroll\InfiniteScrollPager;

/* @var $this yii\web\View */
/* @var $model app\models\Problem */
/* @var $discusses app\models\Discuss */
/* @var $newDiscuss app\models\Discuss */
/* @var $pages yii\data\Pagination */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<h1><?= Html::a(Html::encode($model->title), ['/problem/view', 'id' => $model->id]) ?></h1>
<?php if (Yii::$app->user->isGuest): ?>
    Login before discuss
<?php else: ?>
    <div class="discuss-form">
        <?php $form = ActiveForm::begin(); ?>
        <?= $form->field($newDiscuss, 'title', [
            'template' => "<div class=\"input-group\"><span class=\"input-group-addon\">". Yii::t('app', 'Title') ."</span>{input}</div>",
        ])->textInput(['maxlength' => 128, 'autocomplete'=>'off'])
        ?>

        <?= $form->field($newDiscuss, 'content')->widget('app\widgets\ckeditor\CKeditor'); ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Create'), ['class' => 'btn btn-success']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    </div>
<?php endif; ?>

<div id="content">
    <?php foreach ($discusses as $discuss): ?>
        <article class="thread-item" id="<?= $discuss->id ?>">
            <table cellpadding="0" cellspacing="0" border="0" width="100%">
                <tbody>
                <tr>
                    <td width="10"></td>
                    <td width="auto" valign="middle">
                        <h2><?= Html::a(Html::encode($discuss->title), ['/discuss/view', 'id' => $discuss->id]) ?></h2>
                        <small style="color: #aaa">
                            <strong><?= Html::a(Html::encode($discuss->user->username), ['/user/view', 'id' => $discuss->user->username], ['class' => 'thread-nickname', 'rel' => 'author']); ?></strong>
                            &nbsp;•&nbsp;
                            <time title="<?= Yii::t('app', 'Last Reply Time') ?>">
                                <span class="glyphicon glyphicon-time"></span> <?= Yii::$app->formatter->asRelativeTime($discuss->updated_at)?>
                            </time>
                            <?php if (!Yii::$app->user->isGuest && (Yii::$app->user->id === $discuss->created_by || Yii::$app->user->identity->role == User::ROLE_ADMIN)): ?>
                                &nbsp;•&nbsp;
                                <span class="glyphicon glyphicon-edit"></span> <?= Html::a(Yii::t('app', 'Edit'), ['/discuss/update', 'id' => $discuss->id]) ?>
                                &nbsp;•&nbsp;
                                <span class="glyphicon glyphicon-trash"></span>
                                <?= Html::a(Yii::t('app', 'Delete'), ['/discuss/delete', 'id' => $discuss->id], [
                                    'data' => [
                                        'confirm' => Yii::t('yii', 'Are you sure you want to delete this item?'),
                                        'method' => 'post',
                                    ],
                                ]) ?>
                            <?php endif; ?>
                        </small>
                    </td>
                    <td width="50" align="right" valign="middle" title="<?= Yii::t('app', 'Reply') ?>">
                        <?= Html::a('<span class="glyphicon glyphicon-comment"></span> ', ['/forum/thread/view', 'id' => $discuss->id], ['class' => 'badge']); ?>
                    </td>
                </tr>
                </tbody>
            </table>
        </article>
    <?php endforeach; ?>
</div>

<?= \yii\widgets\LinkPager::widget([
    'pagination' => $pages,
    //'widgetId' => '#content',
]); ?>