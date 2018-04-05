<?php
namespace app\widgets\laydate;

use yii\helpers\Html;
use yii\widgets\InputWidget;
use yii\helpers\ArrayHelper;
use yii\helpers\Json;

/**
 * @author Shiyang <dr@shiyang.me>
 */
class LayDate extends InputWidget
{
    /**
     * laydate 的配置
     * @see http://sentsin.com/layui/laydate/api.html
     */
    public $clientOptions = [];
    /**
     * 默认input配置
     */
    protected $_options;
    public function init()
    {
        $this->_options = [
            'class' => 'form-control laydate-icon'
        ];
        $this->options = ArrayHelper::merge($this->_options, $this->options);
        if (!isset($this->options['id'])) {
            $this->options['id'] = $this->hasModel() ? Html::getInputId($this->model, $this->attribute) : $this->getId();
        }
        parent::init();
    }

    public function run()
    {
        if ($this->hasModel()) {
            echo Html::activeTextInput($this->model, $this->attribute, $this->options);
        } else {
            echo Html::textInput($this->name, $this->value, $this->options);
        }
        $this->registerClientScript();
    }

    /**
     * 注册脚本
     */
    protected function registerClientScript()
    {
        $view = $this->getView();
        LayDateAsset::register($view);
        $id = $this->options['id'];
        $jsOptions = Json::encode(ArrayHelper::merge(['elem' => "#{$id}"], $this->clientOptions));
        $script = "laydate.render({$jsOptions});";
        $view->registerJs($script, $view::POS_READY);
    }
}
