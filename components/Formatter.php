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
     * @param $markdown string content
     * @return string
     */
    public function asMarkdown($markdown)
    {
        $html = Markdown::process($markdown, 'gfm');
        $output = HtmlPurifier::process($html, $this->purifierConfig);
        return '<div class="markdown">' . $this->katex($output) . '</div>';
    }

    /**
     * Format as normal content without class link extensions.
     *
     * @param $value string content
     * @return string
     */
    public function asHtml($value, $config = NULL)
    {
        $output = HtmlPurifier::process($value, $this->purifierConfig);
        return $this->katex($output);
    }

    public function katex($content)
    {
        $textarr = preg_split($this->get_html_split_regex(), $content, -1, PREG_SPLIT_DELIM_CAPTURE);
        //匹配行内$$公式
        $regexTeXInline = '
		%
		\$\$
			((?:
				[^$]+ # Not a dollar
				|
				(?<=(?<!\\\\)\\\\)\$ # Dollar preceded by exactly one slash
				)+)
			(?<!\\\\)
		\$\$ # Dollar preceded by zero slashes
		%ix';
        foreach ($textarr as &$element) {
            if ('' === $element || '<' === $element[0]) {
                continue;
            }
            if (false === stripos($element, '$$')) {
                continue;
            }
            $element = preg_replace_callback($regexTeXInline, [$this, 'katex_src'], $element);
        }
        return implode('', $textarr);
    }

    /**
     * 渲染转换
     * @param $katex
     * @return mixed
     */
    public function katex_entity_decode($katex)
    {
        return str_replace(
            ['&lt;', '&gt;', '&quot;', '&#039;', '&#038;', '&amp;', "\n", "\r"],
            ['<', '>', '"', "'", '&', '&', ' ', ' '],
            $katex
        );
    }

    public function katex_src($matches)
    {
        $katex = $matches[1];
        $katex = $this->katex_entity_decode($katex);
        return '<span class="katex math inline">' . $katex . '</span>';
    }

    public function get_html_split_regex() {
        static $regex;
        if (!isset($regex)) {
            $comments =
                '!'           // Start of comment, after the <.
                . '(?:'         // Unroll the loop: Consume everything until --> is found.
                .     '-(?!->)' // Dash not followed by end of comment.
                .     '[^\-]*+' // Consume non-dashes.
                . ')*+'         // Loop possessively.
                . '(?:-->)?';   // End of comment. If not found, match all input.

            $cdata =
                '!\[CDATA\['  // Start of comment, after the <.
                . '[^\]]*+'     // Consume non-].
                . '(?:'         // Unroll the loop: Consume everything until ]]> is found.
                .     '](?!]>)' // One ] not followed by end of comment.
                .     '[^\]]*+' // Consume non-].
                . ')*+'         // Loop possessively.
                . '(?:]]>)?';   // End of comment. If not found, match all input.

            $escaped =
                '(?='           // Is the element escaped?
                .    '!--'
                . '|'
                .    '!\[CDATA\['
                . ')'
                . '(?(?=!-)'      // If yes, which type?
                .     $comments
                . '|'
                .     $cdata
                . ')';

            $regex =
                '/('              // Capture the entire match.
                .     '<'           // Find start of element.
                .     '(?'          // Conditional expression follows.
                .         $escaped  // Find end of escaped element.
                .     '|'           // ... else ...
                .         '[^>]*>?' // Find end of normal element.
                .     ')'
                . ')/';
        }
        return $regex;
    }
}
