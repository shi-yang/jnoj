<?php
/**
 * @link https://github.com/borodulin/yii2-helpers
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-helpers/blob/master/LICENSE
 */

namespace conquer\helpers;

use yii\helpers\BaseJson;
use yii\web\JsExpression;

/**
 * Gives ability to using js: prefix
 * Class Json
 * @package conquer\helpers
 * @author Andrey Borodulin
 */
class Json extends BaseJson
{
    /**
     * @inheritdoc
     */
    public static function encode($value, $options = 320)
    {
        if (is_array($value)) {
            array_walk_recursive($value, function (&$item) {
                if (is_string($item) && (strncasecmp($item, 'js:', 3) === 0)) {
                    $item = new JsExpression(substr($item, 3));
                }
            });
        }
        return parent::encode($value, $options);
    }
}