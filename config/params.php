<?php
return [
    'adminEmail' => 'admin@example.com',
    'components.formatter' => [
        'class' => app\components\Formatter::class,
        'defaultTimeZone' => 'Asia/Shanghai',
        'locale' => 'zh-CN',
        'dateFormat'=>'yyyy年MM月dd日',
        'thousandSeparator' => '&thinsp;',
    ],
    'components.setting' => [
        'class' => app\components\Setting::class,
    ],
    'judgeProblemDataPath' => dirname(__FILE__) . '/../judge/data/',
    'polygonProblemDataPath' => dirname(__FILE__) . '/../polygon/data/',
];
