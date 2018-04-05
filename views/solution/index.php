<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $searchModel app\models\SolutionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Status');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="solution-index">
    <?php Pjax::begin() ?>
    <?php echo $this->render('_search', ['model' => $searchModel]); ?>
    <?= GridView::widget([
        'layout' => '{items}{pager}',
        'dataProvider' => $dataProvider,
        'options' => ['class' => 'table-responsive'],
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'columns' => [
            [
                'attribute' => 'id',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a($model->id, ['/solution/detail', 'id' => $model->id], ['target' => '_blank', 'data-pjax' => 0]);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'who',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a(Html::encode($model->user->nickname), ['/user/view', 'id' => $model->user_id]);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'problem_id',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a($model->problem_id, ['/problem/view', 'id' => $model->problem_id]);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'result',
                'value' => function ($model, $key, $index, $column) {
                    if ($model->result == $model::OJ_CE || $model->result == $model::OJ_WA
                        || $model->result == $model::OJ_RE) {
                        return Html::a($model->getResult(),
                            ['/solution/result', 'id' => $model->id],
                            ['onclick' => 'return false', 'data-click' => "solution_info"]
                        );
                    } else {
                        return $model->getResult();
                    }
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'time',
                'value' => function ($model, $key, $index, $column) {
                    return $model->time . ' MS';
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'memory',
                'value' => function ($model, $key, $index, $column) {
                    return $model->memory . ' KB';
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'language',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a($model->getLang(),
                        ['/solution/source', 'id' => $model->id],
                        ['onclick' => 'return false', 'data-click' => "solution_info"]
                    );
                },
                'format' => 'raw'
            ],
            'code_length',
            [
                'attribute' => 'created_at',
                'value' => function ($model, $key, $index, $column) {
                    return Html::tag('span', Yii::$app->formatter->asRelativeTime($model->created_at), ['title' => $model->created_at]);
                },
                'format' => 'raw'
            ]
        ],
    ]); ?>

    <?php
    $js = "
    $('[data-click=solution_info]').click(function() {
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
    ";
    $this->registerJs($js);
    ?>

    <?php Pjax::end() ?>
</div>
<?php Modal::begin([
    'options' => ['id' => 'solution-info']
]); ?>
    <div id="solution-content">
    </div>
<?php Modal::end(); ?>
