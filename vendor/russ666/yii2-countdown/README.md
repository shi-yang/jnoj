Installation
================

download
-----------------------
Download [latest version](https://github.com/russ666/yii2-countdown/releases/latest)

composer
-----------------------
add this line to your composer.json
`"russ666/yii2-countdown": "*"`

Usage
================

```php
echo \russ666\widgets\Countdown::widget([
    'id' => 'some-id',
    'datetime' => date('Y-m-d H:i:s O', time() + 1000),
    'format' => '\<span style=\"background: red\"\>%M</span>:%S',
    'tagName' => 'span',
    'events' => [
        'finish' => 'function(){location.reload()}',
    ],
])
```

Params
================

id
-----------------------
Container id.

datetime
-----------------------
Datetime string to countdown. Must be added with timezone, to prevent client-server timezone difference issue.

format
-----------------------
Datetime format for widget (http://hilios.github.io/jQuery.countdown/documentation.html#formatter)

events
-----------------------
Widget events (http://hilios.github.io/jQuery.countdown/documentation.html#events)

options
-----------------------
Container html options.

tagName
-----------------------
Container tag name.

Plugin pages
================
Homepage - http://hilios.github.io/jQuery.countdown

GitHub - https://github.com/hilios/jQuery.countdown
