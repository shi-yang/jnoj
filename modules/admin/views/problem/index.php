<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\bootstrap\Modal;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Problems');
?>
<div class="problem-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a(Yii::t('app', 'Create Problem'), ['create'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Polygon Problem'), ['create-from-polygon'], ['class' => 'btn btn-success']) ?>
        <?= Html::a(Yii::t('app', 'Import Problem'), ['import'], ['class' => 'btn btn-success']) ?>
    </p>
    <hr>
    <p>
        选中项：<?= Html::a('设为可见', "javascript:void(0);", ['id' => 'available', 'class' => 'btn btn-success']) ?>
        <?= Html::a('设为隐藏', "javascript:void(0);", ['id' => 'reserved', 'class' => 'btn btn-success']) ?>
    </p>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'options' => ['id' => 'grid'],
        'columns' => [
            [
                'class' => 'yii\grid\CheckboxColumn',
                'name' => 'id',
            ],
            [
                'attribute' => 'problem_id',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a($model->id, ['problem/view', 'id' => $key]);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'title',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a($model->title, ['problem/view', 'id' => $key]);
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'status',
                'value' => function ($model, $key, $index, $column) {
                    if ($model->status) {
                        return Yii::t('app', 'Visible');
                    } else {
                        return Yii::t('app', 'Hidden');
                    }
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'test_status',
                'value' => function ($model, $kye, $index, $column) {
                    $res = Html::a(
                        '<span class="glyphicon glyphicon-eye-open"></span> '.Yii::t('app', 'View'),
                        ['problem/test-status', 'id' => $model->id],
                        ['onclick' => 'return false', 'data-click' => "test"]
                    );
                    $res .= ' '. Html::a(
                        '<span class="glyphicon glyphicon-send"></span> '.Yii::t('app', 'Submit'),
                        ['problem/test-submit', 'id' => $model->id],
                        ['onclick' => 'return false', 'data-click' => "test"]
                    );
                    return $res;
                },
                'format' => 'raw'
            ],
            [
                'attribute' => 'created_by',
                'value' => function ($model, $key, $index, $column) {
                    if ($model->user) {
                        return Html::a($model->user->nickname, ['/user/view', 'id' => $model->user->id]);
                    }
                    return '';
                },
                'format' => 'raw',
            ],
            [
                'attribute' => 'polygon_problem_id',
                'value' => function ($model, $key, $index, $column) {
                    return Html::a($model->polygon_problem_id, ['/polygon/problem/view', 'id' => $model->polygon_problem_id]);
                },
                'format' => 'raw',
            ],
            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]);
    $this->registerJs('
    $("#available").on("click", function () {
        var keys = $("#grid").yiiGridView("getSelectedRows");
        $.post({
           url: "'.\yii\helpers\Url::to(['/admin/problem/index', 'action' => \app\models\Problem::STATUS_VISIBLE]).'", 
           dataType: \'json\',
           data: {keylist: keys}
        });
    });
    $("#reserved").on("click", function () {
        var keys = $("#grid").yiiGridView("getSelectedRows");
        $.post({
           url: "'.\yii\helpers\Url::to(['/admin/problem/index', 'action' => \app\models\Problem::STATUS_HIDDEN]).'", 
           dataType: \'json\',
           data: {keylist: keys}
        });
    });
    ');
    ?>
</div>
<?php Modal::begin([
    'header' => '<h3>'.Yii::t('app','Information').'</h3>',
    'options' => ['id' => 'solution-info'],
    'size' => Modal::SIZE_LARGE
]); ?>
<div id="solution-content">
</div>
<?php Modal::end(); ?>
<?php
$js = "
$('[data-click=test]').click(function() {
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
