<?php

/* @var $users \app\models\User */
/* @var $top3users \app\models\User */
/* @var $pages \yii\data\Pagination */
/* @var $currentPage integer */
/* @var $defaultPageSize integer */

use yii\helpers\Html;

$this->title = Yii::t('app', 'Rating');
?>
<p style="text-align: center">
    该页面正在开发中，此排序为用户注册先后顺序，仅供示例，并非实际榜单。
</p>
<div class="rating-index">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="row rating-top">
                <div class="col-md-4 col-xs-4">
                    <div class="rating-two">
                        2
                    </div>
                    <h3 class="rating-two-name"><?= $top3users[1]->nickname ?></h3>
                    <span>最强王者</span>
                </div>
                <div class="col-md-4 col-xs-4">
                    <div class="rating-one">
                        1
                    </div>
                    <h3 class="rating-one-name"><?= $top3users[0]->nickname ?></h3>
                    <span>超凡大师</span>
                </div>
                <div class="col-md-4 col-xs-4">
                    <div class="rating-three">
                        3
                    </div>
                    <h3 class="rating-three-name"><?= $top3users[2]->nickname ?></h3>
                    <span>璀璨钻石</span>
                </div>
            </div>
            <hr>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Who</th>
                        <th>=</th>
                        <th>Level</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($users as $k => $user): ?>
                        <?php $num = $k + $currentPage * $defaultPageSize + 4; ?>
                        <tr>
                            <th scope="row"><?= $num ?></th>
                            <td>
                                <?= Html::encode($user->nickname) ?>
                            </td>
                            <td>
                                1500
                            </td>
                            <td>
                                英勇青铜
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?= \yii\widgets\LinkPager::widget(['pagination' => $pages]) ?>
        </div>
    </div>
</div>