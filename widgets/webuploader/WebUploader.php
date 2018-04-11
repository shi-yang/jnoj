<?php
/**
 * @link http://www.iisns.com/
 * @copyright Copyright (c) 2015 iiSNS
 * @license http://www.iisns.com/license/
 */

namespace app\widgets\webuploader;

use Yii;
use yii\base\Widget;

/**
 * @author Shiyang <dr@shiyang.me>
 */
class WebUploader extends Widget
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        if (!isset(Yii::$app->i18n->translations['webuploader'])) {
            Yii::$app->i18n->translations['webuploader'] = [
                'class' => 'yii\i18n\PhpMessageSource',
                'sourceLanguage' => 'en',
                'basePath' => '@app/widgets/webuploader/messages'
            ];
        }
    }
}
