<?php

use app\models\Solution;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use yii\bootstrap\Nav;
use yii\helpers\Markdown;

/* @var $this yii\web\View */
/* @var $model app\models\Contest */
/* @var $solution app\models\Solution */
/* @var $problem array */
/* @var $submissions array */

$this->title = Html::encode($model->title) . ' - ' . $problem['title'];
$this->params['model'] = $model;

if (!Yii::$app->user->isGuest) {
    $solution->language = Yii::$app->user->identity->language;
}

$problems = $model->problems;

$nav = [];
foreach ($problems as $key => $p) {
    $nav[] = [
        'label' => chr(65 + $key),
        'url' => [
            'problem',
            'id' => $model->id,
            'pid' => $key,
        ]
    ];
}
$sample_input = unserialize($problem['sample_input']);
$sample_output = unserialize($problem['sample_output']);
?>
<div class="problem-view">
    <div class="text-center">
        <?= Nav::widget([
            'items' => $nav,
            'options' => ['class' => 'pagination']
        ]) ?>
    </div>
    <div class="row">
        <div class="col-md-8 problem-view">
            <h1><?= Html::encode(chr(65 + $problem['num']) . '. ' . $problem['title']) ?></h1>

            <h3><?= Yii::t('app', 'Description') ?></h3>
            <div class="content-wrapper">
                <?= Markdown::process($problem['description'], 'gfm') ?>
            </div>

            <h3><?= Yii::t('app', 'Input') ?></h3>
            <div class="content-wrapper">
                <?= Markdown::process($problem['input'], 'gfm') ?>
            </div>

            <h3><?= Yii::t('app', 'Output') ?></h3>
            <div class="content-wrapper">
                <?= Markdown::process($problem['output'], 'gfm') ?>
            </div>

            <h3><?= Yii::t('app', 'Examples') ?></h3>
            <div class="content-wrapper">
                <div class="sample-test">
                    <div class="input">
                        <h4><?= Yii::t('app', 'Input') ?></h4>
                        <pre><?= $sample_input[0] ?></pre>
                    </div>
                    <div class="output">
                        <h4><?= Yii::t('app', 'Output') ?></h4>
                        <pre><?= $sample_output[0] ?></pre>
                    </div>

                    <?php if (!empty($sample_input[1])):?>
                        <div class="input">
                            <h4><?= Yii::t('app', 'Input') ?></h4>
                            <pre><?= $sample_input[1] ?></pre>
                        </div>
                        <div class="output">
                            <h4><?= Yii::t('app', 'Output') ?></h4>
                            <pre><?= $sample_output[1] ?></pre>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($sample_input[2])):?>
                        <div class="input">
                            <h4><?= Yii::t('app', 'Input') ?></h4>
                            <pre><?= $sample_input[2] ?></pre>
                        </div>
                        <div class="output">
                            <h4><?= Yii::t('app', 'Output') ?></h4>
                            <pre><?= $sample_output[2] ?></pre>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (!empty($problem['hint'])): ?>
                <h3><?= Yii::t('app', 'Hint') ?></h3>
                <div class="content-wrapper">
                    <?= Markdown::process($problem['hint'], 'gfm') ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="col-md-4 problem-info">
            <div class="panel panel-default">
                <div class="panel-heading">Information</div>
                <!-- Table -->
                <table class="table">
                    <tbody>
                    <tr>
                        <td>Time limit</td>
                        <td><?= $problem['time_limit'] ?> Second (Java:<?= $problem['time_limit'] + 2 ?> Second)</td>
                    </tr>
                    <tr>
                        <td>Memory limit</td>
                        <td><?= $problem['memory_limit'] ?> MB</td>
                    </tr>
                    </tbody>
                </table>
            </div>

            <button type="button" class="btn btn-success" data-toggle="modal" data-target="#submit-solution"><span class="glyphicon glyphicon-plus"></span> Submit a solution</button>

            <?php if (!Yii::$app->user->isGuest): ?>
            <div class="panel panel-default" style="margin-top: 40px">
                <div class="panel-heading">Submissions</div>
                <!-- Table -->
                <table class="table">
                    <tbody>
                    <?php foreach($submissions as $sub): ?>
                    <tr>
                        <td><?= $sub['created_at'] ?></td>
                        <td>
                            <?php
                                if ($sub['result'] == Solution::OJ_AC) {
                                    $span = '<span class="label label-success">' . Solution::getResultList($sub['result']) . '</span>';
                                    echo Html::a($span,
                                        ['/solution/source', 'id' => $sub['id']],
                                        ['onclick' => 'return false', 'data-click' => "solution_info", 'data-pjax' => 0]
                                    );
                                } else if ($sub['result'] == Solution::OJ_CE) {
                                    $span = '<span class="label label-default">' . Solution::getResultList($sub['result']) . '</span>';
                                    echo Html::a($span,
                                        ['/solution/result', 'id' => $sub['id']],
                                        ['onclick' => 'return false', 'data-click' => "solution_info", 'data-pjax' => 0]
                                    );
                                } else {
                                    $span = '<span class="label label-default">' . Solution::getResultList($sub['result']) . '</span>';
                                    echo Html::a($span,
                                        ['/solution/source', 'id' => $sub['id']],
                                        ['onclick' => 'return false', 'data-click' => "solution_info", 'data-pjax' => 0]
                                    );
                                }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php Modal::begin([
    'options' => ['id' => 'solution-info']
]); ?>
<div id="solution-content">
</div>
<?php Modal::end(); ?>

<?php Modal::begin([
    'header' => '<h3>'.Yii::t('app','Submit a solution').'</h3>',
    'size' => Modal::SIZE_LARGE,
    'options' => ['id' => 'submit-solution']
]); ?>
<?php if ($model->getRunStatus() == app\models\Contest::STATUS_ENDED): ?>
    <?= Yii::t('app', 'The contest has ended.') ?>
<?php else: ?>

    <?php if (Yii::$app->user->isGuest): ?>
        <?= app\widgets\login\Login::widget(); ?>
    <?php else: ?>
        <?php $form = ActiveForm::begin(); ?>

        <?= $form->field($solution, 'language')->dropDownList($solution::getLanguageList()) ?>

        <?= $form->field($solution, 'source')->textarea(['rows' => 20, 'autocomplete'=>'off']) ?>

        <div class="form-group">
            <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
        </div>
        <?php ActiveForm::end(); ?>
    <?php endif; ?>

<?php endif; ?>

<?php Modal::end(); ?>

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

MathJax.Hub.Config({
    showProcessingMessages: false,
    messageStyle: \"none\",
    extensions: [\"tex2jax.js\"],
    jax: [\"input/TeX\", \"output/HTML-CSS\"],
    tex2jax: {
        inlineMath:  [ [\"$\", \"$\"] ],
        displayMath: [ [\"$$\",\"$$\"] ],
        skipTags: ['script', 'noscript', 'style', 'textarea', 'pre','code','a'],
    },
    \"HTML-CSS\": {
        showMathMenu: false
    }
});
MathJax.Hub.Queue([\"Typeset\",MathJax.Hub]);
";
$this->registerJs($js);
?>
