<?php
namespace app\widgets\editormd;

use yii\helpers\Html;
use yii\helpers\Inflector;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;
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
