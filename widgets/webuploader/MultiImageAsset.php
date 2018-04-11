<?php

namespace app\widgets\webuploader;

use yii\web\AssetBundle;

/**
 * @author Shiyang <dr@shiyang.me>
 */
class MultiImageAsset extends AssetBundle
{
	public $sourcePath = '@app/widgets/webuploader/assets';

	public $css = [
	  	'webuploader.css',
		'multi.css',
	];

	public $js = [
		'dist/webuploader.min.js',
		'multi.upload.js',
	];

	public $depends = [
		'yii\web\JqueryAsset'
	];
}
