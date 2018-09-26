<?php
namespace app\widgets\ckeditor;

use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\InputWidget;

/**
 * @author Shiyang <dr@shiyang.me>
 */
class CKeditor extends InputWidget
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $options = ArrayHelper::merge($this->options, ['style' => 'display:none']);
        if ($this->hasModel()) {
            echo Html::activeTextArea($this->model, $this->attribute, $options);
        } else {
            echo Html::textArea($this->name, $this->value, $options);
        }
        $this->registerScripts();
    }

    /**
     * Registers assets
     */
    public function registerScripts()
    {
        CKeditorAsset::register($this->view);
        $id = $this->options['id'];
        $script = <<<EOF
        ClassicEditor.create( document.querySelector( '#{$id}' ) )
    .then( editor => {
        console.log( editor );
    })
    .catch( error => {
        console.error( error );
    });
EOF;
        $this->view->registerJs($script);
    }
}
