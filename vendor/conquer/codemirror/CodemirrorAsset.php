<?php
/**
 * @link https://github.com/borodulin/yii2-codemirror
 * @copyright Copyright (c) 2015 Andrey Borodulin
 * @license https://github.com/borodulin/yii2-codemirror/blob/master/LICENSE.md
 */
namespace conquer\codemirror;

use yii\web\View;
use yii\helpers\ArrayHelper;

/**
 * @author Andrey Borodulin
 * @link http://codemirror.net/
 */
class CodemirrorAsset extends \yii\web\AssetBundle
{
    const KEYMAP_EMACS = 'KEYMAP_EMACS';
    const KEYMAP_SUBLIME = 'KEYMAP_SUBLIME';
    const KEYMAP_VIM = 'KEYMAP_VIM';

    const THEME_3024_DAY = 'THEME_3024_DAY';
    const THEME_3024_NIGHT = 'THEME_3024_NIGHT';
    const THEME_ABCDEF = 'THEME_ABCDEF';
    const THEME_AMBIANCE_MOBILE = 'THEME_AMBIANCE_MOBILE';
    const THEME_AMBIANCE = 'THEME_AMBIANCE';
    const THEME_BASE16_DARK = 'THEME_BASE16_DARK';
    const THEME_BASE16_LIGHT = 'THEME_BASE16_LIGHT';
    const THEME_BLACKBOARD = 'THEME_BLACKBOARD';
    const THEME_COBALT = 'THEME_COBALT';
    const THEME_COLORFORTH = 'THEME_COLORFORTH';
    const THEME_DRACULA = 'THEME_DRACULA';
    const THEME_ECLIPSE = 'THEME_ECLIPSE';
    const THEME_ELEGANT = 'THEME_ELEGANT';
    const THEME_ERLANG_DARK = 'THEME_ERLANG_DARK';
    const THEME_ICECODER = 'THEME_ICECODER';
    const THEME_LESSER_DARK = 'THEME_LESSER_DARK';
    const THEME_LIQUIBYTE = 'THEME_LIQUIBYTE';
    const THEME_MATERIAL = 'THEME_MATERIAL';
    const THEME_MBO = 'THEME_MBO';
    const THEME_MDN_LIKE = 'THEME_MDN_LIKE';
    const THEME_MIDNIGHT = 'THEME_MIDNIGHT';
    const THEME_MONOKAI = 'THEME_MONOKAI';
    const THEME_NEAT = 'THEME_NEAT';
    const THEME_NEO = 'THEME_NEO';
    const THEME_NIGHT = 'THEME_NIGHT';
    const THEME_PARAISO_DARK = 'THEME_PARAISO_DARK';
    const THEME_PARAISO_LIGHT = 'THEME_PARAISO_LIGHT';
    const THEME_PASTEL_ON_DARK = 'THEME_PASTEL_ON_DARK';
    const THEME_RUBYBLUE = 'THEME_RUBYBLUE';
    const THEME_SETI = 'THEME_SETI';
    const THEME_SOLARIZED = 'THEME_SOLARIZED';
    const THEME_THE_MATRIX = 'THEME_THE_MATRIX';
    const THEME_TOMORROW_NIGHT_BRIGHT = 'THEME_TOMORROW_NIGHT_BRIGHT';
    const THEME_TOMORROW_NIGHT_EIGHTIES = 'THEME_TOMORROW_NIGHT_EIGHTIES';
    const THEME_TTCN = 'THEME_TTCN';
    const THEME_TWILIGHT = 'THEME_TWILIGHT';
    const THEME_VIBRANT_INK = 'THEME_VIBRANT_INK';
    const THEME_XQ_DARK = 'THEME_XQ_DARK';
    const THEME_XQ_LIGHT = 'THEME_XQ_LIGHT';
    const THEME_YETI = 'THEME_YETI';
    const THEME_ZENBURN = 'THEME_ZENBURN';

    const ADDON_COMMENT = 'ADDON_COMMENT';
    const ADDON_CONTINUECOMMENT = 'ADDON_CONTINUECOMMENT';
    const ADDON_DIALOG = 'ADDON_DIALOG';
    const ADDON_DISPLAY_AUTOREFRESH = 'ADDON_DISPLAY_AUTOREFRESH';
    const ADDON_DISPLAY_FULLSCREEN = 'ADDON_DISPLAY_FULLSCREEN';
    const ADDON_DISPLAY_PANEL = 'ADDON_DISPLAY_PANEL';
    const ADDON_DISPLAY_PLACEHOLDER = 'ADDON_DISPLAY_PLACEHOLDER';
    const ADDON_DISPLAY_RULERS = 'ADDON_DISPLAY_RULERS';
    const ADDON_EDIT_CLOSEBRACKETS = 'ADDON_EDIT_CLOSEBRACKETS';
    const ADDON_EDIT_CLOSETAG = 'ADDON_EDIT_CLOSETAG';
    const ADDON_EDIT_CONTINUELIST = 'ADDON_EDIT_CONTINUELIST';
    const ADDON_EDIT_MATCHBRACKETS = 'ADDON_EDIT_MATCHBRACKETS';
    const ADDON_EDIT_MATCHTAGS = 'ADDON_EDIT_MATCHTAGS';
    const ADDON_EDIT_TRAILINGSPACE = 'ADDON_EDIT_TRAILINGSPACE';
    const ADDON_FOLD_BRACE_FOLD = 'ADDON_FOLD_BRACE_FOLD';
    const ADDON_FOLD_COMMENT_FOLD = 'ADDON_FOLD_COMMENT_FOLD';
    const ADDON_FOLD_FOLDCODE = 'ADDON_FOLD_FOLDCODE';
    const ADDON_FOLD_FOLDGUTTER = 'ADDON_FOLD_FOLDGUTTER';
    const ADDON_FOLD_INDENT_FOLD = 'ADDON_FOLD_INDENT_FOLD';
    const ADDON_FOLD_MARKDOWN_FOLD = 'ADDON_FOLD_MARKDOWN_FOLD';
    const ADDON_FOLD_XML_FOLD = 'ADDON_FOLD_XML_FOLD';
    const ADDON_HINT_ANYWORD_HINT = 'ADDON_HINT_ANYWORD_HINT';
    const ADDON_HINT_CSS_HINT = 'ADDON_HINT_CSS_HINT';
    const ADDON_HINT_HTML_HINT = 'ADDON_HINT_HTML_HINT';
    const ADDON_HINT_JAVASCRIPT_HINT = 'ADDON_HINT_JAVASCRIPT_HINT';
    const ADDON_HINT_SHOW_HINT = 'ADDON_HINT_SHOW_HINT';
    const ADDON_HINT_SQL_HINT = 'ADDON_HINT_SQL_HINT';
    const ADDON_HINT_XML_HINT = 'ADDON_HINT_XML_HINT';
    const ADDON_LINT_COFFEESCRIPT_LINT = 'ADDON_LINT_COFFEESCRIPT_LINT';
    const ADDON_LINT_CSS_LINT = 'ADDON_LINT_CSS_LINT';
    const ADDON_LINT_JAVASCRIPT_LINT = 'ADDON_LINT_JAVASCRIPT_LINT';
    const ADDON_LINT_JSON_LINT = 'ADDON_LINT_JSON_LINT';
    const ADDON_LINT = 'ADDON_LINT';
    const ADDON_LINT_YAML_LINT = 'ADDON_LINT_YAML_LINT';
    const ADDON_MERGE = 'ADDON_MERGE';
    const ADDON_MODE_LOADMODE = 'ADDON_MODE_LOADMODE';
    const ADDON_MODE_MULTIPLEX = 'ADDON_MODE_MULTIPLEX';
    const ADDON_MODE_MULTIPLEX_TEST = 'ADDON_MODE_MULTIPLEX_TEST';
    const ADDON_MODE_OVERLAY = 'ADDON_MODE_OVERLAY';
    const ADDON_MODE_SIMPLE = 'ADDON_MODE_SIMPLE';
    const ADDON_RUNMODE_COLORIZE = 'ADDON_RUNMODE_COLORIZE';
    const ADDON_RUNMODE_STANDALONE = 'ADDON_RUNMODE_STANDALONE';
    const ADDON_RUNMODE = 'ADDON_RUNMODE';
    const ADDON_RUNMODE_NODE = 'ADDON_RUNMODE_NODE';
    const ADDON_SCROLL_ANNOTATESCROLLBAR = 'ADDON_SCROLL_ANNOTATESCROLLBAR';
    const ADDON_SCROLL_SCROLLPASTEND = 'ADDON_SCROLL_SCROLLPASTEND';
    const ADDON_SCROLL_SIMPLESCROLLBARS = 'ADDON_SCROLL_SIMPLESCROLLBARS';
    const ADDON_SEARCH_MATCH_HIGHLIGHTER = 'ADDON_SEARCH_MATCH_HIGHLIGHTER';
    const ADDON_SEARCH_MATCHESONSCROLLBAR = 'ADDON_SEARCH_MATCHESONSCROLLBAR';
    const ADDON_SEARCH = 'ADDON_SEARCH';
    const ADDON_SEARCHCURSOR = 'ADDON_SEARCHCURSOR';
    const ADDON_SELECTION_ACTIVE_LINE = 'ADDON_SELECTION_ACTIVE_LINE';
    const ADDON_SELECTION_MARK_SELECTION = 'ADDON_SELECTION_MARK_SELECTION';
    const ADDON_SELECTION_POINTER = 'ADDON_SELECTION_POINTER';
    const ADDON_TERN = 'ADDON_TERN';
    const ADDON_TERN_WORKER = 'ADDON_TERN_WORKER';
    const ADDON_WRAP_HARDWRAP = 'ADDON_WRAP_HARDWRAP';


    const MODE_APL = 'MODE_APL';
    const MODE_ASCIIARMOR = 'MODE_ASCIIARMOR';
    const MODE_ASN_1 = 'MODE_ASN_1';
    const MODE_ASTERISK = 'MODE_ASTERISK';
    const MODE_BRAINFUCK = 'MODE_BRAINFUCK';
    const MODE_CLIKE = 'MODE_CLIKE';
    const MODE_CLOJURE = 'MODE_CLOJURE';
    const MODE_CMAKE = 'MODE_CMAKE';
    const MODE_COBOL = 'MODE_COBOL';
    const MODE_COFFEESCRIPT = 'MODE_COFFEESCRIPT';
    const MODE_COMMONLISP = 'MODE_COMMONLISP';
    const MODE_CSS = 'MODE_CSS';
    const MODE_CSS_LESS = 'MODE_CSS_LESS';
    const MODE_CSS_SCSS = 'MODE_CSS_SCSS';
    const MODE_CYPHER = 'MODE_CYPHER';
    const MODE_D = 'MODE_D';
    const MODE_DART = 'MODE_DART';
    const MODE_DIFF = 'MODE_DIFF';
    const MODE_DJANGO = 'MODE_DJANGO';
    const MODE_DOCKERFILE = 'MODE_DOCKERFILE';
    const MODE_DTD = 'MODE_DTD';
    const MODE_DYLAN = 'MODE_DYLAN';
    const MODE_EBNF = 'MODE_EBNF';
    const MODE_ECL = 'MODE_ECL';
    const MODE_EIFFEL = 'MODE_EIFFEL';
    const MODE_ELM = 'MODE_ELM';
    const MODE_ERLANG = 'MODE_ERLANG';
    const MODE_FACTOR = 'MODE_FACTOR';
    const MODE_FORTH = 'MODE_FORTH';
    const MODE_FORTRAN = 'MODE_FORTRAN';
    const MODE_GAS = 'MODE_GAS';
    const MODE_GFM = 'MODE_GFM';
    const MODE_GHERKIN = 'MODE_GHERKIN';
    const MODE_GO = 'MODE_GO';
    const MODE_GROOVY = 'MODE_GROOVY';
    const MODE_HAML = 'MODE_HAML';
    const MODE_HANDLEBARS = 'MODE_HANDLEBARS';
    const MODE_HASKELL = 'MODE_HASKELL';
    const MODE_HAXE = 'MODE_HAXE';
    const MODE_HTMLEMBEDDED = 'MODE_HTMLEMBEDDED';
    const MODE_HTMLMIXED = 'MODE_HTMLMIXED';
    const MODE_HTTP = 'MODE_HTTP';
    const MODE_IDL = 'MODE_IDL';
    const MODE_JADE = 'MODE_JADE';
    const MODE_JAVASCRIPT = 'MODE_JAVASCRIPT';
    const MODE_JINJA2 = 'MODE_JINJA2';
    const MODE_JSX = 'MODE_JSX';
    const MODE_JULIA = 'MODE_JULIA';
    const MODE_KOTLIN = 'MODE_KOTLIN';
    const MODE_LIVESCRIPT = 'MODE_LIVESCRIPT';
    const MODE_LUA = 'MODE_LUA';
    const MODE_MARKDOWN = 'MODE_MARKDOWN';
    const MODE_MATHEMATICA = 'MODE_MATHEMATICA';
    const MODE_MIRC = 'MODE_MIRC';
    const MODE_MLLIKE = 'MODE_MLLIKE';
    const MODE_MODELICA = 'MODE_MODELICA';
    const MODE_MSCGEN = 'MODE_MSCGEN';
    const MODE_MUMPS = 'MODE_MUMPS';
    const MODE_NGINX = 'MODE_NGINX';
    const MODE_NTRIPLES = 'MODE_NTRIPLES';
    const MODE_OCTAVE = 'MODE_OCTAVE';
    const MODE_OZ = 'MODE_OZ';
    const MODE_PASCAL = 'MODE_PASCAL';
    const MODE_PEGJS = 'MODE_PEGJS';
    const MODE_PERL = 'MODE_PERL';
    const MODE_PHP = 'MODE_PHP';
    const MODE_PIG = 'MODE_PIG';
    const MODE_PROPERTIES = 'MODE_PROPERTIES';
    const MODE_PUPPET = 'MODE_PUPPET';
    const MODE_PYTHON = 'MODE_PYTHON';
    const MODE_Q = 'MODE_Q';
    const MODE_R = 'MODE_R';
    const MODE_RPM = 'MODE_RPM';
    const MODE_RST = 'MODE_RST';
    const MODE_RUBY = 'MODE_RUBY';
    const MODE_RUST = 'MODE_RUST';
    const MODE_SASS = 'MODE_SASS';
    const MODE_SCHEME = 'MODE_SCHEME';
    const MODE_SHELL = 'MODE_SHELL';
    const MODE_SIEVE = 'MODE_SIEVE';
    const MODE_SLIM = 'MODE_SLIM';
    const MODE_SMALLTALK = 'MODE_SMALLTALK';
    const MODE_SMARTY = 'MODE_SMARTY';
    const MODE_SOLR = 'MODE_SOLR';
    const MODE_SOY = 'MODE_SOY';
    const MODE_SPARQL = 'MODE_SPARQL';
    const MODE_SPREADSHEET = 'MODE_SPREADSHEET';
    const MODE_SQL = 'MODE_SQL';
    const MODE_STEX = 'MODE_STEX';
    const MODE_STYLUS = 'MODE_STYLUS';
    const MODE_SWIFT = 'MODE_SWIFT';
    const MODE_TCL = 'MODE_TCL';
    const MODE_TEXTILE = 'MODE_TEXTILE';
    const MODE_TIDDLYWIKI = 'MODE_TIDDLYWIKI';
    const MODE_TIKI = 'MODE_TIKI';
    const MODE_TOML = 'MODE_TOML';
    const MODE_TORNADO = 'MODE_TORNADO';
    const MODE_TROFF = 'MODE_TROFF';
    const MODE_TTCN = 'MODE_TTCN';
    const MODE_TTCN_CFG = 'MODE_TTCN_CFG';
    const MODE_TURTLE = 'MODE_TURTLE';
    const MODE_TWIG = 'MODE_TWIG';
    const MODE_VB = 'MODE_VB';
    const MODE_VBSCRIPT = 'MODE_VBSCRIPT';
    const MODE_VELOCITY = 'MODE_VELOCITY';
    const MODE_VERILOG = 'MODE_VERILOG';
    const MODE_VHDL = 'MODE_VHDL';
    const MODE_VUE = 'MODE_VUE';
    const MODE_XML = 'MODE_XML';
    const MODE_XQUERY = 'MODE_XQUERY';
    const MODE_YAML = 'MODE_YAML';
    const MODE_Z80 = 'MODE_Z80';

    private static $_css = [
        'lib' => 'lib/codemirror.css',
        self::THEME_3024_DAY => 'theme/3024-day.css',
        self::THEME_3024_NIGHT => 'theme/3024-night.css',
        self::THEME_ABCDEF => 'theme/abcdef.css',
        self::THEME_AMBIANCE_MOBILE => 'theme/ambiance-mobile.css',
        self::THEME_AMBIANCE => 'theme/ambiance.css',
        self::THEME_BASE16_DARK => 'theme/base16-dark.css',
        self::THEME_BASE16_LIGHT => 'theme/base16-light.css',
        self::THEME_BLACKBOARD => 'theme/blackboard.css',
        self::THEME_COBALT => 'theme/cobalt.css',
        self::THEME_COLORFORTH => 'theme/colorforth.css',
        self::THEME_DRACULA => 'theme/dracula.css',
        self::THEME_ECLIPSE => 'theme/eclipse.css',
        self::THEME_ELEGANT => 'theme/elegant.css',
        self::THEME_ERLANG_DARK => 'theme/erlang-dark.css',
        self::THEME_ICECODER => 'theme/icecoder.css',
        self::THEME_LESSER_DARK => 'theme/lesser-dark.css',
        self::THEME_LIQUIBYTE => 'theme/liquibyte.css',
        self::THEME_MBO => 'theme/mbo.css',
        self::THEME_MDN_LIKE => 'theme/mdn-like.css',
        self::THEME_MIDNIGHT => 'theme/midnight.css',
        self::THEME_MONOKAI => 'theme/monokai.css',
        self::THEME_NEAT => 'theme/neat.css',
        self::THEME_NEO => 'theme/neo.css',
        self::THEME_NIGHT => 'theme/night.css',
        self::THEME_PARAISO_DARK => 'theme/paraiso-dark.css',
        self::THEME_PARAISO_LIGHT => 'theme/paraiso-light.css',
        self::THEME_PASTEL_ON_DARK => 'theme/pastel-on-dark.css',
        self::THEME_RUBYBLUE => 'theme/rubyblue.css',
        self::THEME_SETI => 'theme/seti.css',
        self::THEME_SOLARIZED => 'theme/solarized.css',
        self::THEME_THE_MATRIX => 'theme/the-matrix.css',
        self::THEME_TOMORROW_NIGHT_BRIGHT => 'theme/tomorrow-night-bright.css',
        self::THEME_TOMORROW_NIGHT_EIGHTIES => 'theme/tomorrow-night-eighties.css',
        self::THEME_TTCN => 'theme/ttcn.css',
        self::THEME_TWILIGHT => 'theme/twilight.css',
        self::THEME_VIBRANT_INK => 'theme/vibrant-ink.css',
        self::THEME_XQ_DARK => 'theme/xq-dark.css',
        self::THEME_XQ_LIGHT => 'theme/xq-light.css',
        self::THEME_YETI => 'theme/yeti.css',
        self::THEME_ZENBURN => 'theme/zenburn.css',

        self::ADDON_DIALOG => 'addon/dialog/dialog.css',
        self::ADDON_DISPLAY_FULLSCREEN => 'addon/display/fullscreen.css',
        self::ADDON_FOLD_FOLDGUTTER => 'addon/fold/foldgutter.css',
        self::ADDON_HINT_SHOW_HINT => 'addon/hint/show-hint.css',
        self::ADDON_LINT => 'addon/lint/lint.css',
        self::ADDON_MERGE => 'addon/merge/merge.css',
        self::ADDON_SCROLL_SIMPLESCROLLBARS => 'addon/scroll/simplescrollbars.css',
        self::ADDON_SEARCH_MATCHESONSCROLLBAR => 'addon/search/matchesonscrollbar.css',
        self::ADDON_TERN => 'addon/tern/tern.css',

        self::MODE_TIDDLYWIKI => 'mode/tiddlywiki/tiddlywiki.css',
        self::MODE_TIKI => 'mode/tiki/tiki.css',
    ];

    private static $_js = [
        'lib' => 'lib/codemirror.js',
        self::ADDON_COMMENT => 'addon/comment/comment.js',
        self::ADDON_CONTINUECOMMENT => 'addon/comment/continuecomment.js',
        self::ADDON_DIALOG => 'addon/dialog/dialog.js',
        self::ADDON_DISPLAY_AUTOREFRESH => 'addon/display/autorefresh.js',
        self::ADDON_DISPLAY_FULLSCREEN => 'addon/display/fullscreen.js',
        self::ADDON_DISPLAY_PANEL => 'addon/display/panel.js',
        self::ADDON_DISPLAY_PLACEHOLDER => 'addon/display/placeholder.js',
        self::ADDON_DISPLAY_RULERS => 'addon/display/rulers.js',
        self::ADDON_EDIT_CLOSEBRACKETS => 'addon/edit/closebrackets.js',
        self::ADDON_EDIT_CLOSETAG => 'addon/edit/closetag.js',
        self::ADDON_EDIT_CONTINUELIST => 'addon/edit/continuelist.js',
        self::ADDON_EDIT_MATCHBRACKETS => 'addon/edit/matchbrackets.js',
        self::ADDON_EDIT_MATCHTAGS => 'addon/edit/matchtags.js',
        self::ADDON_EDIT_TRAILINGSPACE => 'addon/edit/trailingspace.js',
        self::ADDON_FOLD_BRACE_FOLD => 'addon/fold/brace-fold.js',
        self::ADDON_FOLD_COMMENT_FOLD => 'addon/fold/comment-fold.js',
        self::ADDON_FOLD_FOLDCODE => 'addon/fold/foldcode.js',
        self::ADDON_FOLD_FOLDGUTTER => 'addon/fold/foldgutter.js',
        self::ADDON_FOLD_INDENT_FOLD => 'addon/fold/indent-fold.js',
        self::ADDON_FOLD_MARKDOWN_FOLD => 'addon/fold/markdown-fold.js',
        self::ADDON_FOLD_XML_FOLD => 'addon/fold/xml-fold.js',
        self::ADDON_HINT_ANYWORD_HINT => 'addon/hint/anyword-hint.js',
        self::ADDON_HINT_CSS_HINT => 'addon/hint/css-hint.js',
        self::ADDON_HINT_HTML_HINT => 'addon/hint/html-hint.js',
        self::ADDON_HINT_JAVASCRIPT_HINT => 'addon/hint/javascript-hint.js',
        self::ADDON_HINT_SHOW_HINT => 'addon/hint/show-hint.js',
        self::ADDON_HINT_SQL_HINT => 'addon/hint/sql-hint.js',
        self::ADDON_HINT_XML_HINT => 'addon/hint/xml-hint.js',
        self::ADDON_LINT_COFFEESCRIPT_LINT => 'addon/lint/coffeescript-lint.js',
        self::ADDON_LINT_CSS_LINT => 'addon/lint/css-lint.js',
        self::ADDON_LINT_JAVASCRIPT_LINT => 'addon/lint/javascript-lint.js',
        self::ADDON_LINT_JSON_LINT => 'addon/lint/json-lint.js',
        self::ADDON_LINT => 'addon/lint/lint.js',
        self::ADDON_LINT_YAML_LINT => 'addon/lint/yaml-lint.js',
        self::ADDON_MERGE => 'addon/merge/merge.js',
        self::ADDON_MODE_LOADMODE => 'addon/mode/loadmode.js',
        self::ADDON_MODE_MULTIPLEX_TEST => 'addon/mode/multiplex_test.js',
        self::ADDON_MODE_MULTIPLEX => 'addon/mode/multiplex.js',
        self::ADDON_MODE_OVERLAY => 'addon/mode/overlay.js',
        self::ADDON_MODE_SIMPLE => 'addon/mode/simple.js',
        self::ADDON_RUNMODE_COLORIZE => 'addon/runmode/colorize.js',
        self::ADDON_RUNMODE_STANDALONE => 'addon/runmode/runmode-standalone.js',
        self::ADDON_RUNMODE => 'addon/runmode/runmode.js',
        self::ADDON_RUNMODE_NODE => 'addon/runmode/runmode.node.js',
        self::ADDON_SCROLL_ANNOTATESCROLLBAR => 'addon/scroll/annotatescrollbar.js',
        self::ADDON_SCROLL_SCROLLPASTEND => 'addon/scroll/scrollpastend.js',
        self::ADDON_SCROLL_SIMPLESCROLLBARS => 'addon/scroll/simplescrollbars.js',
        self::ADDON_SEARCH_MATCH_HIGHLIGHTER => 'addon/search/match-highlighter.js',
        self::ADDON_SEARCH_MATCHESONSCROLLBAR => 'addon/search/matchesonscrollbar.js',
        self::ADDON_SEARCH => 'addon/search/search.js',
        self::ADDON_SEARCHCURSOR => 'addon/search/searchcursor.js',
        self::ADDON_SELECTION_ACTIVE_LINE => 'addon/selection/active-line.js',
        self::ADDON_SELECTION_MARK_SELECTION => 'addon/selection/mark-selection.js',
        self::ADDON_SELECTION_POINTER => 'addon/selection/selection-pointer.js',
        self::ADDON_TERN => 'addon/tern/tern.js',
        self::ADDON_TERN_WORKER => 'addon/tern/worker.js',
        self::ADDON_WRAP_HARDWRAP => 'addon/wrap/hardwrap.js',

        self::KEYMAP_EMACS => 'keymap/emacs.js',
        self::KEYMAP_SUBLIME => 'keymap/sublime.js',
        self::KEYMAP_VIM => 'keymap/vim.js',

        self::MODE_APL => 'mode/apl/apl.js',
        self::MODE_ASCIIARMOR => 'mode/asciiarmor/asciiarmor.js',
        self::MODE_ASN_1 => 'mode/asn.1/asn.1.js',
        self::MODE_ASTERISK => 'mode/asterisk/asterisk.js',
        self::MODE_BRAINFUCK => 'mode/brainfuck/brainfuck.js',
        self::MODE_CLIKE => 'mode/clike/clike.js',
        self::MODE_CLOJURE => 'mode/clojure/clojure.js',
        self::MODE_CMAKE => 'mode/cmake/cmake.js',
        self::MODE_COBOL => 'mode/cobol/cobol.js',
        self::MODE_COFFEESCRIPT => 'mode/coffeescript/coffeescript.js',
        self::MODE_COMMONLISP => 'mode/commonlisp/commonlisp.js',
        self::MODE_CSS => 'mode/css/css.js',
        self::MODE_CYPHER => 'mode/cypher/cypher.js',
        self::MODE_D => 'mode/d/d.js',
        self::MODE_DART => 'mode/dart/dart.js',
        self::MODE_DIFF => 'mode/diff/diff.js',
        self::MODE_DJANGO => 'mode/django/django.js',
        self::MODE_DOCKERFILE => 'mode/dockerfile/dockerfile.js',
        self::MODE_DTD => 'mode/dtd/dtd.js',
        self::MODE_DYLAN => 'mode/dylan/dylan.js',
        self::MODE_EBNF => 'mode/ebnf/ebnf.js',
        self::MODE_ECL => 'mode/ecl/ecl.js',
        self::MODE_EIFFEL => 'mode/eiffel/eiffel.js',
        self::MODE_ELM => 'mode/elm/elm.js',
        self::MODE_ERLANG => 'mode/erlang/erlang.js',
        self::MODE_FACTOR => 'mode/factor/factor.js',
        self::MODE_FORTH => 'mode/forth/forth.js',
        self::MODE_FORTRAN => 'mode/fortran/fortran.js',
        self::MODE_GAS => 'mode/gas/gas.js',
        self::MODE_GFM => 'mode/gfm/gfm.js',
        self::MODE_GHERKIN => 'mode/gherkin/gherkin.js',
        self::MODE_GO => 'mode/go/go.js',
        self::MODE_GROOVY => 'mode/groovy/groovy.js',
        self::MODE_HAML => 'mode/haml/haml.js',
        self::MODE_HANDLEBARS => 'mode/handlebars/handlebars.js',
        self::MODE_HASKELL => 'mode/haskell/haskell.js',
        self::MODE_HAXE => 'mode/haxe/haxe.js',
        self::MODE_HTMLEMBEDDED => 'mode/htmlembedded/htmlembedded.js',
        self::MODE_HTMLMIXED => 'mode/htmlmixed/htmlmixed.js',
        self::MODE_HTTP => 'mode/http/http.js',
        self::MODE_IDL => 'mode/idl/idl.js',
        self::MODE_JADE => 'mode/jade/jade.js',
        self::MODE_JAVASCRIPT => 'mode/javascript/javascript.js',
        self::MODE_JINJA2 => 'mode/jinja2/jinja2.js',
        self::MODE_JSX => 'mode/jsx/jsx.js',
        self::MODE_JULIA => 'mode/julia/julia.js',
        self::MODE_KOTLIN => 'mode/kotlin/kotlin.js',
        self::MODE_LIVESCRIPT => 'mode/livescript/livescript.js',
        self::MODE_LUA => 'mode/lua/lua.js',
        self::MODE_MARKDOWN => 'mode/markdown/markdown.js',
        self::MODE_MATHEMATICA => 'mode/mathematica/mathematica.js',
        self::MODE_MIRC => 'mode/mirc/mirc.js',
        self::MODE_MLLIKE => 'mode/mllike/mllike.js',
        self::MODE_MODELICA => 'mode/modelica/modelica.js',
        self::MODE_MSCGEN => 'mode/modelica/mscgen.js',
        self::MODE_MUMPS => 'mode/modelica/mumps.js',
        self::MODE_NGINX => 'mode/nginx/nginx.js',
        self::MODE_NTRIPLES => 'mode/ntriples/ntriples.js',
        self::MODE_OCTAVE => 'mode/octave/octave.js',
        self::MODE_OZ => 'mode/oz/oz.js',
        self::MODE_PASCAL => 'mode/pascal/pascal.js',
        self::MODE_PEGJS => 'mode/pegjs/pegjs.js',
        self::MODE_PERL => 'mode/perl/perl.js',
        self::MODE_PHP => 'mode/php/php.js',
        self::MODE_PIG => 'mode/pig/pig.js',
        self::MODE_PROPERTIES => 'mode/properties/properties.js',
        self::MODE_PUPPET => 'mode/puppet/puppet.js',
        self::MODE_PYTHON => 'mode/python/python.js',
        self::MODE_Q => 'mode/q/q.js',
        self::MODE_R => 'mode/r/r.js',
        self::MODE_RPM => 'mode/rpm/rpm.js',
        self::MODE_RST => 'mode/rst/rst.js',
        self::MODE_RUBY => 'mode/ruby/ruby.js',
        self::MODE_RUST => 'mode/rust/rust.js',
        self::MODE_SASS => 'mode/sass/sass.js',
        self::MODE_SCHEME => 'mode/scheme/scheme.js',
        self::MODE_SHELL => 'mode/shell/shell.js',
        self::MODE_SIEVE => 'mode/sieve/sieve.js',
        self::MODE_SLIM => 'mode/slim/slim.js',
        self::MODE_SMALLTALK => 'mode/smalltalk/smalltalk.js',
        self::MODE_SMARTY => 'mode/smarty/smarty.js',
        self::MODE_SOLR => 'mode/solr/solr.js',
        self::MODE_SOY => 'mode/soy/soy.js',
        self::MODE_SPARQL => 'mode/sparql/sparql.js',
        self::MODE_SPREADSHEET => 'mode/spreadsheet/spreadsheet.js',
        self::MODE_SQL => 'mode/sql/sql.js',
        self::MODE_STEX => 'mode/stex/stex.js',
        self::MODE_STYLUS => 'mode/stylus/stylus.js',
        self::MODE_SWIFT => 'mode/swift/swift.js',
        self::MODE_TCL => 'mode/tcl/tcl.js',
        self::MODE_TEXTILE => 'mode/textile/textile.js',
        self::MODE_TIDDLYWIKI => 'mode/tiddlywiki/tiddlywiki.js',
        self::MODE_TIKI => 'mode/tiki/tiki.js',
        self::MODE_TOML => 'mode/toml/toml.js',
        self::MODE_TORNADO => 'mode/tornado/tornado.js',
        self::MODE_TROFF => 'mode/troff/troff.js',
        self::MODE_TTCN => 'mode/ttcn/ttcn.js',
        self::MODE_TTCN_CFG => 'mode/ttcn/ttcn-cfg.js',
        self::MODE_TURTLE => 'mode/turtle/turtle.js',
        self::MODE_TWIG => 'mode/twig/twig.js',
        self::MODE_VB => 'mode/vb/vb.js',
        self::MODE_VBSCRIPT => 'mode/vbscript/vbscript.js',
        self::MODE_VELOCITY => 'mode/velocity/velocity.js',
        self::MODE_VERILOG => 'mode/verilog/verilog.js',
        self::MODE_VHDL => 'mode/vhdl/vhdl.js',
        self::MODE_VUE => 'mode/vue/vue.js',
        self::MODE_XML => 'mode/xml/xml.js',
        self::MODE_XQUERY => 'mode/xquery/xquery.js',
        self::MODE_YAML => 'mode/yaml/yaml.js',
        self::MODE_Z80 => 'mode/z80/z80.js',
    ];

    private static $_assets;


    // The files are not web directory accessible, therefore we need
    // to specify the sourcePath property. Notice the @bower alias used.
    public $sourcePath = '@bower/codemirror';

    /**
     * Registers this asset bundle with a view.
     * @param View $view the view to be registered with
     * @param array() $assets
     * @return static the registered asset bundle instance
     */
    public static function register($view, $assets = [])
    {
        self::$_assets = ArrayHelper::merge(self::$_assets, array_flip($assets));
        return $view->registerAssetBundle(get_called_class());
    }

    /**
     * Registers the CSS and JS files with the given view.
     * @param \yii\web\View $view the view that the asset files are to be registered with.
     */
    public function registerAssetFiles($view)
    {
        if (is_array(self::$_assets)) {
            $this->css = array_values(array_intersect_key(self::$_css, self::$_assets));
            $this->js = array_values(array_intersect_key(self::$_js, self::$_assets));
        }
        array_unshift($this->css, self::$_css['lib']);
        array_unshift($this->js, self::$_js['lib']);
        parent::registerAssetFiles($view);
    }
}
