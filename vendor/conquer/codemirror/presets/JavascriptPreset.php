<?php
/**
 * @link https://github.com/borodulin/yii2-codemirror
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-codemirror/blob/master/LICENSE.md
 */
use conquer\codemirror\CodemirrorAsset;

return [
    'assets'=>[
        CodemirrorAsset::ADDON_EDIT_MATCHBRACKETS,
        CodemirrorAsset::ADDON_CONTINUECOMMENT,
        CodemirrorAsset::ADDON_COMMENT,
        CodemirrorAsset::MODE_JAVASCRIPT,
    ],
    'settings'=>[
        'lineNumbers'=> true,
        'matchBrackets'=>true,
        'continueComments' => "Enter",
        'extraKeys' => ["Ctrl-/"=> "toggleComment"],
    ],
];