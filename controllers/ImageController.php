<?php

namespace app\controllers;

use app\components\BaseController;
use Yii;
use yii\web\ForbiddenHttpException;
use app\components\Uploader;

/**
 * 用来接收 CKeditor 编辑器上传的图片
 */
class ImageController extends BaseController
{
    public $enableCsrfValidation = false;
    public function actionUpload()
    {
        if (Yii::$app->request->isPost && !Yii::$app->user->isGuest) {
            $up = new Uploader('upload');
            $info = $up->getFileInfo();
            if ($info['state'] == 'SUCCESS') {
                $info['url'] = Yii::getAlias('@web') . '/' . $info['url'];
                $info['uploaded'] = true;
            } else {
                $info['uploaded'] = false;
            }
            echo json_encode($info);
        } else {
            throw new ForbiddenHttpException('You are not allowed to perform this action.');
        }
    }
}
