<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model app\models\Solution */

$this->title = $model->id;
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Status'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;

?>
<div class="solution-view">
    <h3>Run id: <?= Html::encode($this->title) ?></h3>
    <div class="pre"><p><?= Html::encode($model->source) ?></p></div>
</div>
<script type="text/javascript">
    (function ($) {
        $(document).ready(function () {
            $('.pre p').each(function(i, block) {  // use <pre><p>
                hljs.highlightBlock(block);
            });
        })
    })(jQuery);
</script>