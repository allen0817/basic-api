<?php

$params = require(__DIR__ . '/params.php');
$db = require(__DIR__ . '/db.php');

Yii::setAlias('@zabbix', dirname(__DIR__) . '/web/z/include');

Yii::setAlias('@web', dirname(__DIR__) . '/web');

$config = [
    'id' => 'basic-console',
    'basePath' => dirname(__DIR__),
    'bootstrap' => [
        'queue', // 把这个组件注册到控制台
        'log',
    ],

    'controllerNamespace' => 'app\commands',
    'components' => [
        'queue' => [
            'class' => \yii\queue\redis\Queue::class,
            'as log' => \yii\queue\LogBehavior::class,
            'ttr' => 5 * 60, // Max time for anything job handling
            'attempts' => 3, // Max number of attempts
            // 驱动的其他选项
        ],
        'redis' => [
            'class' => 'yii\redis\Connection',
            'hostname' => '172.16.28.87',
            'password' => '91redis',
            'port' => 6379,
            'database' => 0,
        ],

        'cache' => [
            'class' => 'yii\caching\FileCache',
//            'class' => 'yii\redis\Cache',
        ],
        'log' => [
            'targets' => [
                [
                    'class' => 'yii\log\FileTarget',
                    'levels' => ['error', 'warning'],
                ],
            ],
        ],
        'db' => $db,
        'zabbix'       => [
            'class' => 'app\components\ZabbixApiComponent',
        ],

    ],
    'params' => $params,

    'controllerMap' => [
        'fixture' => [ // Fixture generation command line.
            'class' => 'yii\faker\FixtureController',
        ],
    ],

];

if (YII_ENV_DEV) {
    // configuration adjustments for 'dev' environment
    $config['bootstrap'][] = 'gii';
    $config['modules']['gii'] = [
        'class' => 'yii\gii\Module',
    ];
}

return $config;
