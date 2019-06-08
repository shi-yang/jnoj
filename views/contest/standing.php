<?php

use app\models\Contest;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $form yii\widgets\ActiveForm */
/* @var $showStandingBeforeEnd bool */
/* @var $rankResult array */

$this->title = $model->title;
$this->params['model'] = $model;

$js =<<<EOT
$(".toggle-show-contest-standing input[name='showStandingBeforeEnd']").change(function () {
    $(".toggle-show-contest-standing").submit();
});
EOT;
$this->registerJs($js);
?>
<div class="contest-overview text-center center-block">
    <?php if ($model->type != Contest::TYPE_OI || $model->getRunStatus() == Contest::STATUS_ENDED): ?>
    <div class="legend-strip">
        <?php if ($model->getRunStatus() == Contest::STATUS_ENDED): ?>
            <?= Html::beginForm(
                    ['/contest/standing', 'id' => $model->id],
                    'get',
                    ['class' => 'toggle-show-contest-standing pull-left', 'style' => 'margin-top: 6px;']
            ); ?>
            <div class="checkbox">
                <label>
                    <?= Html::checkbox('showStandingBeforeEnd', $showStandingBeforeEnd) ?>
                    显示比赛期间榜单
                </label>
            </div>
            <?= Html::endForm(); ?>
        <?php endif; ?>
        <div class="pull-right table-legend">
            <?php if ($model->type != Contest::TYPE_OI && $model->type != Contest::TYPE_IOI): ?>
                <div>
                    <span class="solved-first legend-status"></span>
                    <p class="legend-label"> First to solve problem</p>
                </div>
                <div>
                    <span class="solved legend-status"></span>
                    <p class="legend-label"> Solved problem</p>
                </div>
            <?php else: ?>
                <div>
                    <span class="solved-first legend-status"></span>
                    <p class="legend-label"> All correct</p>
                </div>
                <div>
                    <span class="solved legend-status"></span>
                    <p class="legend-label"> Partially correct</p>
                </div>
            <?php endif; ?>
            <div>
                <span class="attempted legend-status"></span>
                <p class="legend-label"> Attempted problem</p>
            </div>
            <div>
                <span class="pending legend-status"></span>
                <p class="legend-label"> Pending judgement</p>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <div class="clearfix"></div>
    <div class="table-responsive">
        <?php
            if ($model->type == $model::TYPE_RANK_SINGLE) {
                echo $this->render('_standing_single', [
                    'model' => $model,
                    'showStandingBeforeEnd' => $showStandingBeforeEnd,
                    'rankResult' => $rankResult
                ]);
            } else if ($model->type == $model::TYPE_OI || $model->type == $model::TYPE_IOI) {
                echo $this->render('_standing_oi', [
                    'model' => $model,
                    'showStandingBeforeEnd' => $showStandingBeforeEnd,
                    'rankResult' => $rankResult
                ]);
            } else {
                echo $this->render('_standing_group', [
                    'model' => $model,
                    'showStandingBeforeEnd' => $showStandingBeforeEnd,
                    'rankResult' => $rankResult
                ]);
            }
        ?>
    </div>
</div>
