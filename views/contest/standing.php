<?php

use app\models\Contest;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $form yii\widgets\ActiveForm */
/* @var $data array */
$this->title = $model->title;
$this->params['model'] = $model;
?>
<div class="contest-overview text-center center-block">
    <?php if ($model->type != Contest::TYPE_OI || $model->getRunStatus() == Contest::STATUS_ENDED): ?>
    <div class="legend-strip">
        <div class="pull-right table-legend">
            <?php if ($model->type != Contest::TYPE_OI && $model->type == Contest::TYPE_IOI): ?>
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
                    'model' => $model
                ]);
            } else if ($model->type == $model::TYPE_OI || $model->type == $model::TYPE_IOI) {
                echo $this->render('_standing_oi', [
                    'model' => $model
                ]);
            } else {
                echo $this->render('_standing_group', [
                    'model' => $model
                ]);
            }
        ?>
    </div>
</div>
