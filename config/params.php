<?php
return [
    // 学校名称
    'schoolName' => '江南大学',

    // OJ名称，这里填写 ‘江南’ 则表示 `江南OJ` 或者 `江南Online Judge`
    'ojName' => '江南',

    // 是否要分享代码
    // true : 用户可以查看其他用户的代码
    // false : 用户的代码只能由自己或者管理员查看
    'isShareCode' => false,

    // 封榜时间。单位：秒
    'scoreboardFrozenTime' => 2 * 60 * 60,

    // judge 数据所在目录
    'judgeProblemDataPath' => dirname(__FILE__) . '/../judge/data/',

    // polygon 数据所在目录
    'polygonProblemDataPath' => dirname(__FILE__) . '/../polygon/data/',

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
];
