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
        return $this->katex_markup_single($this->katex_markup_double($content));
    }

    public function katex_markup_single( $content ) {

        //匹配行内$公式
        $regexTeXInline = '
		%
		\$
			((?:
				[^$]+ # Not a dollar
				|
				(?<=(?<!\\\\)\\\\)\$ # Dollar preceded by exactly one slash
				)+)
			(?<!\\\\)
		\$ # Dollar preceded by zero slashes
		%ix';

        $textarr = $this->wp_html_split( $content );

        // 初始化参数
        $count = 0;
        $preg  = true;

        foreach ($textarr as &$element) {

            //判断是否在code里面
            if ($count > 0) {
                ++ $count;
            }

            // 判断是否是<pre>然后开始计数，此时为第一行
            if ( htmlspecialchars_decode( $element ) == "<pre>" ) {
                $count = 1;
            }

            // 当读到第三行时，判断是code标签嘛，如果是，说明是代码，则后续不进行处理
            if ( $count == 3 && strpos( htmlspecialchars_decode( $element ), "<code class=" ) === 0 ) {
                $preg = false;
            }

            // 如果发现是</pre>标签，则表示代码部分结束，继续处理
            if ( htmlspecialchars_decode( $element ) == "</pre>" ) {
                $preg = true;
            }

            // 如果在代码中，则跳出本次循环
            if ( ! $preg ) {
                continue;
            }

            // 跳出循环
            if ( '' === $element || '<' === $element[0] ) {
                continue;
            }

            if ( false === stripos( $element, '$' ) ) {
                continue;
            }

            $element = preg_replace_callback( $regexTeXInline, array( $this, 'katex_src_inline' ), $element );
        }

        return implode( '', $textarr );
    }

    public function katex_src_inline( $matches ) {

        $katex = $matches[1];

        $katex = $this->katex_entity_decode_editormd( $katex );

        return '<span class="katex math inline">' . $katex . '</span>';
    }

    public function katex_markup_double( $content ) {

        //匹配行内$公式
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

        $textarr = $this->wp_html_split( $content );

        // 初始化参数
        $count = 0;
        $preg  = true;

        foreach ( $textarr as &$element ) {

            //判断是否在code里面
            if ( $count > 0 ) {
                ++ $count;
            }

            // 判断是否是<pre>然后开始计数，此时为第一行
            if ( htmlspecialchars_decode( $element ) == "<pre>" ) {
                $count = 1;
            }

            // 当读到第三行时，判断是code标签嘛，如果是，说明是代码，则后续不进行处理
            if ( $count == 3 && strpos( htmlspecialchars_decode( $element ), "<code class=" ) === 0 ) {
                $preg = false;
            }

            // 如果发现是</pre>标签，则表示代码部分结束，继续处理
            if ( htmlspecialchars_decode( $element ) == "</pre>" ) {
                $preg = true;
            }

            // 如果在代码中，则跳出本次循环
            if ( ! $preg ) {
                continue;
            }

            // 跳出循环
            if ( '' === $element || '<' === $element[0] ) {
                continue;
            }

            if ( false === stripos( $element, '$$' ) ) {
                continue;
            }

            $element = preg_replace_callback( $regexTeXInline, array( $this, 'katex_src_multiline' ), $element );
        }

        return implode( '', $textarr );
    }

    public function katex_src_multiline( $matches ) {

        $katex = $matches[1];

        $katex = $this->katex_entity_decode_editormd( $katex );

        return '<span class="katex math multi-line">' . $katex . '</span>';
    }

    /**
     * 渲染转换
     *
     * @param $katex
     *
     * @return mixed
     */
    public function katex_entity_decode_editormd( $katex ) {
        return str_replace(
            array( '&lt;', '&gt;', '&quot;', '&#039;', '&#038;', '&amp;', "\n", "\r", '&#60;', '&#62;', "&#40;", "&#41;", "&#95;", "&#33;", "&#123;", "&#125;", "&#94;", "&#43;","&#92;" ),
            array( '<', '>', '"', "'", '&', '&', ' ', ' ', '<', '>', '(', ')', '_', '!', '{', '}', '^', '+','\\\\' ),
            $katex );
    }

    public function wp_html_split( $input ) {
        return preg_split($this->get_html_split_regex(), $input, -1, PREG_SPLIT_DELIM_CAPTURE );
    }

    public function get_html_split_regex() {
        static $regex;

        if ( ! isset( $regex ) ) {
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
