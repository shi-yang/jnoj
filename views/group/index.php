<?php

use yii\bootstrap\Nav;
use yii\widgets\ListView;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Groups');
?>
<?= Nav::widget([
    'items' => [
        [
            'label' => Yii::t('app', 'My Groups'),
            'url' => ['group/my-group'],
            'visible' => !Yii::$app->user->isGuest
        ],
        [
            'label' => Yii::t('app', 'Explore'),
            'url' => ['group/index']
        ],
        [
            'label' => Yii::t('app', 'Create'),
            'url' => 'create',
            'visible' => !Yii::$app->user->isGuest,
            'options' => ['class' => 'pull-right']
        ]
    ],
    'options' => ['class' => 'nav-tabs', 'style' => 'margin-bottom: 15px']
]) ?>

<?= ListView::widget([
    'dataProvider' => $dataProvider,
    'itemView' => '_group_item',
    'layout' => '<div class="card-columns">{items}</div>{summary}{pager}'
])?>
