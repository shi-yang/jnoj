<?php
return [
    'schoolName' => '江南大学',  // 学校名称
    'ojName' => '江南',  // OJ名称，这里填写 ‘江南’ 则表示 `江南OJ` 或者 `江南Online Judge`
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
