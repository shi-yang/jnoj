<?php

use yii\helpers\Html;
use app\models\GroupUser;

/* @var $this yii\web\View */
/* @var $model app\models\Group */
/* @var $groupUser app\models\GroupUser */
?>

<h3>管理用户：<?= Html::a(Html::encode($groupUser->user->nickname), ['/group/view', 'id' => $groupUser->user->id]) ?></h3>
<hr>
<h4>当前角色：<?= $groupUser->getRole() ?></h4>
<?php if ($groupUser->role == GroupUser::ROLE_APPLICATION): ?>
    <?= Html::a('同意加入', ['/group/user-update', 'id' => $groupUser->id, 'role' => 1], ['class' => 'btn btn-success']); ?>
    <?= Html::a('拒绝加入', ['/group/user-update', 'id' => $groupUser->id, 'role' => 2], ['class' => 'btn btn-danger']); ?>
<?php elseif ($groupUser->role == GroupUser::ROLE_REUSE_INVITATION): ?>
    <?= Html::a('重新邀请', ['/group/user-update', 'id' => $groupUser->id, 'role' => 3], ['class' => 'btn btn-default']); ?>
<?php elseif ($groupUser->role == GroupUser::ROLE_MEMBER && $model->getRole() == GroupUser::ROLE_LEADER): ?>
    <?= Html::a('设为管理员', ['/group/user-update', 'id' => $groupUser->id, 'role' => 4], ['class' => 'btn btn-default']); ?>
<?php elseif ($groupUser->role == GroupUser::ROLE_MANAGER && $model->getRole() == GroupUser::ROLE_LEADER): ?>
    <?= Html::a('设为普通成员', ['/group/user-update', 'id' => $groupUser->id, 'role' => 5], ['class' => 'btn btn-default']); ?>
<?php endif; ?>
