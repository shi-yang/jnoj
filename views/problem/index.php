<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use justinvoelker\tagging\TaggingWidget;

/* @var $this yii\web\View */
/* @var $searchModel app\models\ProblemSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $solvedProblem array */

$this->title = Yii::t('app', 'Problems');
?>
<div class="row">

    <?php Pjax::begin(); ?>
    <div class="col-md-9">
        <?= GridView::widget([
            'layout' => '{items}{pager}',
            'dataProvider' => $dataProvider,
            'options' => ['class' => 'table-responsive problem-index-list'],
            'columns' => [
                [
                    'attribute' => 'problem_id',
                    'value' => function ($model, $key, $index, $column) use ($solvedProblem) {
                        $solve = '';
                        if (isset($solvedProblem[$model->id])) {
                            $solve = '<span class="glyphicon glyphicon-ok text-success" style="float:left"></span>';
                        }
                        return $solve . Html::a($model->id, ['/problem/view', 'id' => $key]);
                    },
                    'format' => 'raw',
                    'options' => ['width' => '100px']
                ],
                [
                    'attribute' => 'title',
                    'value' => function ($model, $key, $index, $column) {
                        $res = Html::a(Html::encode($model->title), ['/problem/view', 'id' => $key]);
                        $tags = !empty($model->tags) ? explode(',', $model->tags) : [];
                        $tagsCount = count($tags);
                        if ($tagsCount > 0) {
                            $res .= '<span class="problem-list-tags">';
                            foreach((array)$tags as $tag) {
                                $res .= Html::a('<span class="label label-default">' . Html::encode($tag) . '</span>', [
                                    '/problem/index', 'tag' => $tag
                                ]);
                            }
                            $res .= '</span>';
                        }
                        return $res;
                    },
                    'format' => 'raw',
                ],
                [
                    'attribute' => 'solved',
                    'value' => function ($model, $key, $index, $column) use ($solvedProblem) {
                        return Html::a($model->accepted, [
                            '/solution/index',
                            'SolutionSearch[problem_id]' => $model->id,
                            'SolutionSearch[result]' => 4
                        ], ['data-pjax' => 0]);
                    },
                    'format' => 'raw',
                    'options' => ['width' => '100px']
                ]
            ],
        ]); ?>
    </div>
    <div class="col-md-3">
        <div class="panel panel-default">
            <div class="panel-body">
                <?= Html::beginForm('', 'post', ['class' => 'form-inline']) ?>
                <div class="input-group">
                    <?= Html::label(Yii::t('app', 'Search'), 'q', ['class' => 'sr-only']) ?>
                    <?= Html::textInput('q', '', ['class' => 'form-control', 'placeholder' => 'Problem ID or Title']) ?>
                    <span class="input-group-btn">
                    <?= Html::submitButton('<span class="glyphicon glyphicon-search"></span>', ['class' => 'btn btn-default']) ?>
                    </span>
                </div>
                <?= Html::endForm() ?>
            </div>
        </div>

        <div class="panel panel-default">
            <div class="panel-heading"><?= Yii::t('app', 'Category') ?></div>
            <div class="panel-body">
                <?= TaggingWidget::widget([
                    'items' => $tags,
                    'url' => ['/problem/index'],
                    'format' => 'ul',
                    'urlParam' => 'tag',
                    'listOptions' => ['class' => 'tag-group'],
                    'liOptions' => ['class' => 'tag-group-item']
                ]) ?>
            </div>
        </div>
    </div>
    <?php Pjax::end(); ?>
</div>
