<?php
namespace app\widgets\ckeditor;

use yii\web\AssetBundle;

/**
 * @author Shiyang <dr@shiyang.me>
 */
class CKeditorAsset extends AssetBundle
{
    public $sourcePath = '@app/widgets/ckeditor/assets';
    public $js = [
        'ckeditor.js'
    ];
    public $css = [
    ];
    public $depends = [
        'yii\web\JqueryAsset',
    ];
}
