Codemirror widget for Yii2 framework
=================

## Description

CodeMirror is a versatile text editor implemented in JavaScript for the browser. It is specialized for editing code, and comes with a number of language modes and addons that implement more advanced editing functionality.
For more information please visit [CodeMirror](http://codemirror.net/) 

## Installation

The preferred way to install this extension is through [composer](http://getcomposer.org/download/). 

To install, either run

```
$ php composer.phar require conquer/codemirror "*"
```
or add

```
"conquer/codemirror": "*"
```

to the ```require``` section of your `composer.json` file.

## Usage

```php
use conquer\codemirror\CodemirrorWidget;

$form->field($model, 'code')->widget(
    CodemirrorWidget::className(),
    [
        'preset'=>'php',
        'options'=>['rows' => 20],
    ]
);
```

You can use ready-made presets, or create your own. To do this, specify the folder to your presets.

```php
use conquer\codemirror\CodemirrorWidget;

$form->field($model, 'code')->widget(
    CodemirrorWidget::className(),
    [
        'presetsDir'=>'/path_to_your_presets',
        'preset'=>'sql',
    ]
);
```

In general, you can customize the widget directly specifying its properties.

```php
use conquer\codemirror\CodemirrorWidget;
use conquer\codemirror\CodemirrorAsset;

$form->field($model, 'code')->widget(
    CodemirrorWidget::className(),
    [
        'assets'=>[
            CodemirrorAsset::MODE_CLIKE,
            CodemirrorAsset::KEYMAP_EMACS,
            CodemirrorAsset::ADDON_EDIT_MATCHBRACKETS,
            CodemirrorAsset::ADDON_COMMENT,
            CodemirrorAsset::ADDON_DIALOG,
            CodemirrorAsset::ADDON_SEARCHCURSOR,
            CodemirrorAsset::ADDON_SEARCH,
        ],
        'settings'=>[
            'lineNumbers' => true,
            'mode' => 'text/x-csrc',
            'keyMap' => 'emacs'
        ],
    ]
);
```

## License

**conquer/codemirror** is released under the MIT License. See the bundled `LICENSE.md` for details.
