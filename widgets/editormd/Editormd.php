<?php
namespace app\widgets\editormd;

use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
use yii\helpers\Url;
use yii\widgets\InputWidget;

/**
 * @author Shiyang <dr@shiyang.me>
 */
class Editormd extends InputWidget
{
    /**
     * Markdown options you want to override
     * See https://github.com/pandao/editor.md
     * @var array
     */
    public $clientOptions = [];
    /**
     * Default options that will be passed to the editor
     */
    protected $_options;
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
        $this->_options = [
            'placeholder' => 'hint',
            'height' => 300,
            'imageUpload' => true,
            'tex' => true,
            'flowChart' => true,
            'sequenceDiagram' => true,
            'imageUploadURL' => Url::to(['img_upload']),
            'autoFocus' => false,
        ];
        $this->clientOptions = ArrayHelper::merge($this->_options, $this->clientOptions);
        $id = $this->options['id'];
        $options = ArrayHelper::merge($this->options, ['style' => 'display:none']);
        echo "<div id=\"{$id}\">";
        if ($this->hasModel()) {
            echo Html::activeTextArea($this->model, $this->attribute, $options);
        } else {
            echo Html::textArea($this->name, $this->value, $options);
        }
        echo "</div>";
        $this->registerScripts();
    }
    /**
     * Registers simplemde markdown assets
     */
    public function registerScripts()
    {
        EditormdAsset::register($this->view);
        $id = $this->options['id'];
        $jsonOptions = Json::encode($this->clientOptions);
        $varName = Inflector::classify('editor' . $id);
        $script = "var {$varName} = editormd('{$id}', {$jsonOptions});";
        $this->view->registerJs($script);
    }
}
