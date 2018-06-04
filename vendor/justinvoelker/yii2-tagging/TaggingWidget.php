<?php

namespace justinvoelker\tagging;

use yii\helpers\Html;

/**
 * TaggingWidget renders an HTML tag cloud or as an ordered/unordered list
 *
 * For example:
 *
 * ```php
 * echo TaggingWidget::widget([
 *     'items' => $tags,
 *     'url' => ['post/index'],
 *     'urlParam' => 'tag',
 * ]);
 * ```
 *
 * @author Justin Voelker <justin@justinvoelker.com>
 */
class TaggingWidget extends \yii\base\Widget
{
    /**
     * @var array key=>value pairs of tags=>frequency. If multiple
     * TaggingWidgets will use the same data on the same page, TaggingQuery can
     * be used to first generate the item list which can then be passed to
     * TaggingWidget for display.
     */
    public $items;
    /**
     * @var string smallest size to be assigned to a tag in the tag cloud
     */
    public $smallest = '14';
    /**
     * @var string largest size to be assigned to a tag in the tag cloud
     */
    public $largest = '22';
    /**
     * @var string unit of measure for assigning the smallest and largest font sizes
     */
    public $unit = 'px';
    /**
     * @var string format of the returned tags. Options are 'cloud', 'ul', or
     * 'ol'. Cloud will return a tag cloud with font-sizes adjusted according
     * to their count (frequency). 'ul' and 'ol' will return the appropriate
     * list that can be formatted as desired. If 'ol' is specified, a 'type'
     * will likely be desired as a 'ulOptions' value.
     */
    public $format = 'cloud';
    /**
     * @var string the route that will be used as a base onto which 'urlParam'
     * and the tag name will be appended.
     */
    public $url;
    /**
     * @var string the URL parameter that will be used with the tag and
     * appended to URL.
     */
    public $urlParam;
    /**
     * @var string options that are to be assigned to the ul or ol in the tag
     * cloud or list.
     */
    public $listOptions = array();
    /**
     * @var string options that are to be assigned to each list item (li) in
     * the tag cloud or list.
     */
    public $liOptions = array();
    /**
     * @var string options that are to be assigned to each link (a) in the tag
     * cloud or list.
     */
    public $linkOptions = array();
    /**
     * @var array the minimum frequency found for all tags which is used as the
     * basis for increasing the font-size of more frequent tags.
     */
    private $_minCount;
    /**
     * @var string the calculated difference in font when indicating tag frequency.
     */
    private $_fontStep = 1;

    /**
     * Renders the widget
     * @return string the rendering result of the widget.
     */
    public function run()
    {
        return $this->renderItems();
    }

    /**
     * Renders widget items
     * @return string complete list of rendered items
     */
    public function renderItems()
    {
        if (!empty($this->items)) {
            $this->_fontStep = $this->getFontStep();
        }
        $items = [];
        foreach ($this->items as $name => $count) {
            $items[] = $this->renderItem(Html::encode($name), $count);
        }
        if ($this->format == 'cloud') {
            Html::addCssClass($this->listOptions, 'tagging_cloud');
        } else {
            Html::addCssClass($this->listOptions, 'tagging_list');
        }
        $listType = ($this->format == 'ol') ? 'ol' : 'ul';
        return Html::tag($listType, implode("\n", $items), $this->listOptions);

    }

    /**
     * Renders a widget's item
     * @param string the item name to be displayed
     * @param the count or frequency of the item to be displayed
     * @return string a single item within the complete list
     */
    public function renderItem($name, $count)
    {
        $fontSize = ($this->smallest + (($count - $this->_minCount) * $this->_fontStep));
        if (!empty($this->url)) {
            $url = array_merge($this->url, [$this->urlParam => $name]);
        }
        if ($this->format == 'cloud') {
            Html::addCssStyle($this->liOptions, 'font-size:' . $fontSize . $this->unit);
        }
        if (!empty($this->url)) {
            return Html::tag('li', Html::a($name, $url, $this->linkOptions), $this->liOptions);
        } else {
            return Html::tag('li', $name, $this->liOptions);
        }
    }

    /**
     * Determine the step size for font increases
     * @return decimal size of individual font step for increasingly frequent items
     */
    public function getFontStep()
    {
        $this->_minCount = min($this->items);
        $spread = max($this->items) - $this->_minCount;
        if ($spread <= 0) {
            $spread = 1;
        }
        $font_spread = $this->largest - $this->smallest;
        if ($font_spread < 0) {
            $font_spread = 1;
        }
        return $font_spread / $spread;
    }
}
