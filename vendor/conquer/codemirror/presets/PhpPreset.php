<?php
/**
 * @link https://github.com/borodulin/yii2-codemirror
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-codemirror/blob/master/LICENSE.md
 */
use conquer\codemirror\CodemirrorAsset;
use yii\web\JsExpression;
return [
	'assets'=>[
		CodemirrorAsset::MODE_CLIKE,
		CodemirrorAsset::MODE_PHP,
		CodemirrorAsset::ADDON_COMMENT,
		CodemirrorAsset::ADDON_DISPLAY_FULLSCREEN,
		CodemirrorAsset::THEME_ECLIPSE,
	],
	'settings'=>[
		'lineNumbers' => true,
		'matchBrackets' => true,
		'mode' => "application/x-httpd-php-open",
		'indentUnit' => 4,
		'indentWithTabs' => true,
		'extraKeys' => [
			"F11" => new JsExpression("function(cm){cm.setOption('fullScreen', !cm.getOption('fullScreen'));}"),
			"Esc" => new JsExpression("function(cm){if(cm.getOption('fullScreen')) cm.setOption('fullScreen', false);}"),
		],
	],
];