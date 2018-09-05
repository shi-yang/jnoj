<?php
/**
 * @link https://github.com/borodulin/yii2-helpers
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-helpers/blob/master/LICENSE
 */

namespace conquer\helpers;

/**
 * Class Array2Xml
 * @package conquer\helpers
 * @author Andrey Borodulin
 */
class Array2Xml
{
    private static function toXml($array)
    {
        $xml = '';
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                if (is_numeric($key)) {
                    $key = "element";
                }
                if (is_array($value)) {
                    $xml .= "<$key>" . static::toXml($value) . "</$key>";
                } elseif (strlen(trim($value)) === 0) {
                    $xml .= "<$key/>";
                } else {
                    $xml .= "<$key>" . htmlspecialchars($value) . "</$key>";
                }
            }
        }
        return $xml;
    }

    /**
     * Converts PHP array to xml.
     * @param array $array
     * @param string $rootTag
     * @return string
     */
    public static function encodeXml($array, $rootTag = 'root')
    {
        $xml = static::toXml($array);
        return "<$rootTag>$xml</$rootTag>";
    }
}