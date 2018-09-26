<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\bootstrap\Modal;
use app\models\Solution;

/* @var $this yii\web\View */
/* @var $model app\models\Problem */
/* @var $solution app\models\Solution */
/* @var $submissions array */

$this->title = $model->title;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Problems'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

if (!Yii::$app->user->isGuest) {
    $solution->language = Yii::$app->user->identity->language;
}
$model->setSamples();
?>
<div class="row">
    <div class="col-md-9 problem-view">

        <h1><?= Html::encode($this->title) ?></h1>

        <div class="content-wrapper">
            <?= Yii::$app->formatter->asHtml($model->description) ?>
        </div>

        <h3><?= Yii::t('app', 'Input') ?></h3>
        <div class="content-wrapper">
            <?= Yii::$app->formatter->asHtml($model->input) ?>
        </div>

        <h3><?= Yii::t('app', 'Output') ?></h3>
        <div class="content-wrapper">
            <?= Yii::$app->formatter->asHtml($model->output) ?>
        </div>

        <h3><?= Yii::t('app', 'Examples') ?></h3>
        <div class="content-wrapper">
            <div class="sample-test">
                <div class="input">
                    <h4><?= Yii::t('app', 'Input') ?></h4>
                    <pre><?= $model->sample_input ?></pre>
                </div>
                <div class="output">
                    <h4><?= Yii::t('app', 'Output') ?></h4>
                    <pre><?= $model->sample_output ?></pre>
                </div>

                <?php if (!empty($model->sample_input_2)):?>
                    <div class="input">
                        <h4><?= Yii::t('app', 'Input') ?></h4>
                        <pre><?= $model->sample_input_2 ?></pre>
                    </div>
                    <div class="output">
                        <h4><?= Yii::t('app', 'Output') ?></h4>
                        <pre><?= $model->sample_output_2 ?></pre>
                    </div>
                <?php endif; ?>

                <?php if (!empty($model->sample_input_3)):?>
                    <div class="input">
                        <h4><?= Yii::t('app', 'Input') ?></h4>
                        <pre><?= $model->sample_input_3 ?></pre>
                    </div>
                    <div class="output">
                        <h4><?= Yii::t('app', 'Output') ?></h4>
                        <pre><?= $model->sample_output_3 ?></pre>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($model->hint)): ?>
            <h3><?= Yii::t('app', 'Hint') ?></h3>
            <div class="content-wrapper">
                <?= Yii::$app->formatter->asHtml($model->hint) ?>
            </div>
        <?php endif; ?>
    </div>
    <div class="col-md-3 problem-info">
        <div class="panel panel-default">
            <!-- Table -->
            <table class="table">
                <tbody>
                <tr>
                    <td>Time limit</td>
                    <td>
                        <?= Yii::t('app', '{t, plural, =1{# second} other{# seconds}}', ['t' => intval($model->time_limit)]); ?>
                    </td>
                </tr>
                <tr>
                    <td>Memory limit</td>
                    <td><?= $model->memory_limit ?> MB</td>
                </tr>
                </tbody>
            </table>
        </div>

        <?php Modal::begin([
            'header' => '<h3>'.Yii::t('app','Submit a solution').'</h3>',
            'size' => Modal::SIZE_LARGE,
            'toggleButton' => [
                'label' => '<span class="glyphicon glyphicon-plus"></span> ' . Yii::t('app', 'Submit'),
                'class' => 'btn btn-success'
            ]
        ]); ?>
            <?php if (Yii::$app->user->isGuest): ?>
                <?= app\widgets\login\Login::widget(); ?>
            <?php else: ?>
                <?php $form = ActiveForm::begin(); ?>

                <h3><?= Yii::t('app', 'Problem : {name}', ['name' => $model->title]) ?></h3>

                <?= $form->field($solution, 'language')->dropDownList($solution::getLanguageList()) ?>

                <?= $form->field($solution, 'source')->textarea(['rows' => 20, 'autocomplete'=>'off']) ?>

                <div class="form-group">
                    <?= Html::submitButton(Yii::t('app', 'Submit'), ['class' => 'btn btn-primary']) ?>
                </div>
                <?php ActiveForm::end(); ?>
            <?php endif; ?>
        <?php Modal::end(); ?>

        <?= Html::a('<span class="glyphicon glyphicon-comment"></span> ' . Yii::t('app', 'Discuss'),
            ['/problem/discuss', 'id' => $model->id],
            ['class' => 'btn btn-default'])
        ?>
        <?= Html::a('<span class="glyphicon glyphicon-signal"></span> ' . Yii::t('app', 'Stats'),
            ['/problem/statistics', 'id' => $model->id],
            ['class' => 'btn btn-default', 'data-toggle' => 'tooltip', 'data-placement' => 'top', 'title' => 'Problem statistics']
        )?>

        <?php if (!Yii::$app->user->isGuest && !empty($submissions)): ?>
            <div class="panel panel-default" style="margin-top: 40px">
            <div class="panel-heading">Submissions</div>
            <!-- Table -->
            <table class="table">
                <tbody>
                <?php foreach ($submissions as $sub): ?>
                    <tr>
                        <td title="<?= $sub['created_at'] ?>">
                            <?= Yii::$app->formatter->asRelativeTime($sub['created_at']) ?>
                        </td>
                        <td>
                            <?php
                            if ($sub['result'] == Solution::OJ_AC) {
                                $span = '<span class="label label-success">' . Solution::getResultList($sub['result']) . '</span>';
                                echo Html::a($span,
                                    ['/solution/source', 'id' => $sub['id']],
                                    ['onclick' => 'return false', 'data-click' => "solution_info", 'data-pjax' => 0]
                                );
                            } else {
                                $span = '<span class="label label-default">' . Solution::getResultList($sub['result']) . '</span>';
                                echo Html::a($span,
                                    ['/solution/result', 'id' => $sub['id']],
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

<?php Modal::begin([
    'options' => ['id' => 'solution-info']
]); ?>
    <div id="solution-content">
    </div>
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
";
$this->registerJs($js);
?>
