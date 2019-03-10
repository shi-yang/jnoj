<?php
return [
    // judge 数据所在目录
    'judgeProblemDataPath' => dirname(__FILE__) . '/../judge/data/',

    // polygon 数据所在目录
    'polygonProblemDataPath' => dirname(__FILE__) . '/../polygon/data/',

    'components.formatter' => [
        'class' => app\components\Formatter::class,
        'defaultTimeZone' => 'Asia/Shanghai',
        'locale' => 'zh-CN',
        'dateFormat' => 'yyyy年MM月dd日',
        'datetimeFormat' => 'yyyy/MM/dd HH:mm:ss',
        'thousandSeparator' => '&thinsp;',
    ],
    'components.setting' => [
        'class' => app\components\Setting::class,
    ],
];
