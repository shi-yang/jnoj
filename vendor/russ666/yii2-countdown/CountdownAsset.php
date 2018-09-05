<?php

namespace russ666\widgets;

use yii\web\AssetBundle;

class CountdownAsset extends AssetBundle
{
    public $sourcePath = '@bower/jquery.countdown/dist';
    public $css = [];
    public $js = ['jquery.countdown.min.js'];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
