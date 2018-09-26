<?php

namespace app\widgets\ckeditor;

use Yii;
use yii\base\Action;
use yii\helpers\ArrayHelper;
use app\components\Uploader;

class CKeditorAction extends Action
{
    /**
     * @var array
     */
    public $config = [];
    /**
     * Default config
     */
    public $_config = [];
    public function init()
    {
        //close csrf
        Yii::$app->request->enableCsrfValidation = false;
        $this->_config = [
            'savePath' => 'uploads/' ,             //存储文件夹
            'maxSize' => 2048 ,//允许的文件最大尺寸，单位KB
            'allowFiles' => ['.gif' , '.png' , '.jpg' , '.jpeg' , '.bmp'],  //允许的文件格式
        ];
        //load config file
        $this->config = ArrayHelper::merge($this->_config, $this->config);
        parent::init();
    }

    public function run()
    {
        $up = new Uploader('editormd-image-file', $this->config);
        $callback = Yii::$app->request->get('callback');
        $info = $up->getFileInfo();
        if ($info['state'] == 'SUCCESS') {
            $info['url'] = Yii::$app->request->hostInfo . Yii::getAlias('@web') . '/' . $info['url'];
            $info['message'] = '上传成功！';
            $info['success'] = 1;
        } else {
            $info['message'] = '上传失败！';
            $info['success'] = 0;
        }
        /**
         * return data
         */
        if($callback) {
            echo '<script>'.$callback.'('.json_encode($info).')</script>';
        } else {
            echo json_encode($info);
        }
    }
}
