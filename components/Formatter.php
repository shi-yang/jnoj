<?php

namespace app\components;

use yii\helpers\HtmlPurifier;
use yii\helpers\Markdown;

class Formatter extends \yii\i18n\Formatter
{
    public $purifierConfig = [
        'HTML' => [
            'AllowedElements' => [
                'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                'strong', 'em', 'b', 'i', 'u', 's', 'span',
                'pre', 'code',
                'table', 'tr', 'td', 'th',
                'a', 'p', 'br',
                'blockquote',
                'ul', 'ol', 'li',
                'img'
            ],
        ],
        'Attr' => [
            'EnableID' => true,
        ],
    ];

    /**
     * Format as normal markdown without class link extensions.
     *
     * @param $markdown
     * @return string
     */
    public function asMarkdown($markdown)
    {
        $html = Markdown::process($markdown, 'gfm');
        $output = HtmlPurifier::process($html, $this->purifierConfig);
        return '<div class="markdown">'.$output.'</div>';
    }
}
