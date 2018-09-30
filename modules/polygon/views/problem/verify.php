<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use app\models\Solution;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model app\modules\polygon\models\Problem */
/* @var $solution \app\modules\polygon\models\PolygonStatus */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
$this->params['model'] = $model;
$solution->language = Yii::$app->user->identity->language;
?>
<p>
    该页面用于给验题人验证题目数据的准确性，验题前需在
    <?= Html::a(Yii::t('app', 'Tests Data'), ['/polygon/problem/tests', 'id' => $model->id]) ?>
    页面中生成标程的标准输出文件。
</p>
<hr>
<?= GridView::widget([
    'layout' => '{items}{pager}',
    'dataProvider' => $dataProvider,
    'options' => ['class' => 'table-responsive problem-index-list'],
    'columns' => [
        'id',
        [
            'attribute' => 'who',
            'value' => function ($model, $key, $index, $column) {
                return Html::a(Html::encode($model->user->nickname), ['/user/view', 'id' => $model->created_by]);
            },
            'format' => 'raw'
        ],
        [
            'attribute' => 'result',
            'value' => function ($model, $key, $index, $column) {
                if ($model->result == Solution::OJ_CE || $model->result == Solution::OJ_WA
                    || $model->result == Solution::OJ_RE) {
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
        [
            'attribute' => 'created_at',
            'value' => function ($model, $key, $index, $column) {
                return Html::tag('span', Yii::$app->formatter->asRelativeTime($model->created_at), ['title' => $model->created_at]);
            },
            'format' => 'raw'
        ]
    ],
]); ?>
<hr>
<?php if (!$model->spj): ?>
    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($solution, 'language')->dropDownList(Solution::getLanguageList()) ?>

    <?= $form->field($solution, 'source')->widget('app\widgets\codemirror\CodeMirror'); ?>

    <div class="form-group">
        <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
    </div>
    <?php ActiveForm::end(); ?>
<?php else: ?>
    <p>
        当前验题功能尚未支持用SPJ来进行验证的题目。
    </p>
<?php endif; ?>
