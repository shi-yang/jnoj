<?php

$params = require __DIR__ . '/params.php';
$db = require __DIR__ . '/db.php';

$config = [
    'id' => 'basic',
    'timeZone' => 'Asia/Shanghai',
    'basePath' => dirname(__DIR__),
    'bootstrap' => ['log'],
    'aliases' => [
        '@bower' => '@vendor/bower-asset',
        '@npm'   => '@vendor/npm-asset',
    ],
    'sourceLanguage' => 'en-US',
    'language' => 'zh-CN',
    'timeZone' => 'Asia/Shanghai',
    'modules' => [
        'admin' => [
            'class' => 'app\modules\admin\AdminModule',
        ],
        'polygon' => [
            'class' => 'app\modules\polygon\Module',
        ],
    ],
    'components' => [
        'formatter' => $params['components.formatter'],
        'setting' => $params['components.setting'],
//        'view' => [
//            'class' => '\rmrevin\yii\minify\View',
//            'enableMinify' => !YII_DEBUG,
//            'concatCss' => true, // concatenate css
//            'minifyCss' => true, // minificate css
//            'concatJs' => true, // concatenate js
//            'minifyJs' => true, // minificate js
//            'minifyOutput' => true, // minificate result html page
//            'webPath' => '@web', // path alias to web base
//            'basePath' => '@webroot', // path alias to web base
//            'minifyPath' => '@webroot/assets', // path alias to save minify result
//            'jsPosition' => [ \yii\web\View::POS_END ], // positions of js files to be minified
//            'forceCharset' => 'UTF-8', // charset forcibly assign, otherwise will use all of the files found charset
//            'expandImports' => true, // whether to change @import on content
//            'compressOptions' => ['extra' => true], // options for compress
//            'excludeFiles' => [
//                'jquery.js', // exclude this file from minification
//                'app-[^.].js', // you may use regexp,
//                'MathJax.js'
//            ],
//            'excludeBundles' => [
//                \app\widgets\editormd\EditormdAsset::class,
//                \app\widgets\laydate\LayDateAsset::class,
//            ],
//        ],
        'request' => [
            // !!! insert a secret key in the following (if it is empty) - this is required by cookie validation
            'cookieValidationKey' => 'DQAnZjUbLJuK4Qci1ZIR8WZ3RJEKQuNm',
        ],
        'cache' => [
            'class' => 'yii\caching\FileCache',
        ],
        'user' => [
            'identityClass' => 'app\models\User',
            'enableAutoLogin' => true,
        ],
        'errorHandler' => [
            'errorAction' => 'site/error',
        ],
        'mailer' => [
            'class' => 'yii\swiftmailer\Mailer',
            // send all mails to a file by default. You have to set
            // 'useFileTransport' to false and configure a transport
            // for the mailer to send real emails.
            'useFileTransport' => true,
        ],
        'log' => [
            'traceLevel' => YII_DEBUG ? 3 : 0,
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'urlManager' => [
            'enablePrettyUrl' => true,
            'showScriptName' => false,
            'rules' => [
                //'<controller:[\w-]+>/<id:\d+>' => '<controller>/view',
                'p/<id:\d+>' => '/problem/view',
                'status/index' => '/solution/index'
            ],
        ],
    ],
    'params' => $params,
];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'debug';
    $config['modules']['debug'] = [
        'class' => 'yii\debug\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];

    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
        // uncomment the following to add your IP if you are not connecting from localhost.
        //'allowedIPs' => ['127.0.0.1', '::1'],
    ];
}

return $config;
