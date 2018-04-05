<?php
return [
    'adminEmail' => 'admin@example.com',
    'components.formatter' => [
        'class' => app\components\Formatter::class,
        'thousandSeparator' => '&thinsp;',
    ],
    'components.setting' => [
        'class' => app\components\Setting::class,
    ],
];
