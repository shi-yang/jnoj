<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;
use yii\bootstrap\Modal;
/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $searchModel app\models\SolutionSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
/* @var $data array */

$this->title = $model->title;
$this->params['model'] = $model;
$problems = $model->problems;

$nav = [];
$nav[''] = 'All';
foreach ($problems as $key => $p) {
    $nav[$p['problem_id']] = chr(65 + $key) . '-' . $p['title'];
}
?>
<div class="solution-index" style="margin-top: 20px">
    <?php Pjax::begin() ?>
    <?= $this->render('_status_search', ['model' => $searchModel, 'nav' => $nav, 'contest_id' => $model->id]); ?>

    <?= GridView::widget([
        'layout' => '{items}{pager}',
        'dataProvider' => $dataProvider,
        'options' => ['class' => 'table-responsive'],
        'tableOptions' => ['class' => 'table table-striped table-bordered'],
        'columns' => [
            'id',
            [
                'attribute' => 'who',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a(Html::encode($model->user->nickname), ['/user/view', 'id' => $model->user_id]);
                },
                'format' => 'raw'
            ],
            [
                'label' => Yii::t('app', 'Problem'),
                'value' => function ($model, $key, $index, $column) {
                    $res = $model->getProblemInContest();
                    return Html::a(chr(65 + $res->num),
                        ['/contest/problem', 'id' => $res->id, 'pid' => $res->num]);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'result',
                'value' => function ($solution, $key, $index, $column) use ($model) {
                    if ($solution->result == $solution::OJ_CE || $solution->result == $solution::OJ_WA
                        || $solution->result == $solution::OJ_RE) {
                    if ($solution->status == 1
                        || (!Yii::$app->user->isGuest && ($model->created_by == Yii::$app->user->id
                        || ($solution->result == $solution::OJ_CE && Yii::$app->user->id == $solution->user_id)))) {
                        return Html::a($solution->getResult(),
                            ['/solution/result', 'id' => $solution->id],
                            ['onclick' => 'return false', 'data-click' => "solution_info", 'data-pjax' => 0]
                        );
                    }
                    } else {
                        return $solution->getResult();
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
                'value' => function ($solution, $key, $index, $column) use ($model) {
                    if ($solution->status == 1
                        || (!Yii::$app->user->isGuest && ($model->created_by == Yii::$app->user->id || $solution->user_id == Yii::$app->user->id))) {
                        return Html::a($solution->getLang(),
                            ['/solution/source', 'id' => $solution->id],
                            ['onclick' => 'return false', 'data-click' => "solution_info", 'data-pjax' => 0]
                        );
                    } else {
                        return $solution->getLang();
                    }
                },
                'format' => 'raw'
            ],
            'code_length',
            'created_at:datetime',
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
    'header' => '<h3>'.Yii::t('app','Information').'</h3>',
    'options' => ['id' => 'solution-info']
]); ?>
<div id="solution-content">
</div>
<?php Modal::end(); ?>
