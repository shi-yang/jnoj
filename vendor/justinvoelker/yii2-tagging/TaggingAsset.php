<?php

namespace justinvoelker\tagging;

use yii\web\AssetBundle;

class TaggingAsset extends AssetBundle
{
    public $sourcePath = '@vendor/justinvoelker/yii2-tagging';
    public $css = [
        'css/tagging.css',
    ];
}
