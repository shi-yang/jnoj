<?php

use yii\bootstrap\Nav;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $searchModel app\models\GroupSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Groups');
?>
<?= Nav::widget([
    'items' => [
        [
            'label' => Yii::t('app', 'My Groups'),
            'url' => ['group/index']
        ],
        [
            'label' => Yii::t('app', 'Explore'),
            'url' => ['group/explore']
        ],
        [
            'label' => Yii::t('app', 'Create'),
            'url' => 'create',
            'options' => ['class' => 'pull-right']
        ]
    ],
    'options' => ['class' => 'nav-tabs', 'style' => 'margin-bottom: 15px']
]) ?>

<?= \yii\widgets\ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_group_item',
    'layout' => '<div class="card-columns">{items}</div>{summary}{pager}'
])?>
