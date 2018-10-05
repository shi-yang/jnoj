<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use app\components\Uploader;

/**
 * 用来接收 CKeditor 编辑器上传的图片
 */
class ImageController extends Controller
{
    public $enableCsrfValidation = false;
    public function actionUpload()
    {
        if (!Yii::$app->request->isAjax && !Yii::$app->user->isGuest) {
            $up = new Uploader('upload');
            $info = $up->getFileInfo();
            if ($info['state'] == 'SUCCESS') {
                $info['url'] = Yii::$app->request->hostInfo . Yii::getAlias('@web') . '/' . $info['url'];
                $info['uploaded'] = true;
            } else {
                $info['uploaded'] = false;
            }
            echo json_encode($info);
        }
    }
}
