<?php

global $YIIDB,$YIIZBX_SERVER,$YIIZBX_SERVER_PORT;
$YIIDB['SERVER'] = '172.16.28.87';
$YIIDB['PORT'] = '3306';
$YIIDB['DATABASE'] = 'zabbix';
$YIIDB['USER'] = 'root';
$YIIDB['PASSWORD'] = 'root';
$YIIZBX_SERVER = '172.16.28.87';
$YIIZBX_SERVER_PORT = '10051';
return [
    'class' => 'yii\db\Connection',
    'dsn' => 'mysql:host='.$YIIDB['SERVER'].';port='.$YIIDB['PORT'].';dbname='.$YIIDB['DATABASE'],
    'username' => $YIIDB['USER'],
    'password' => $YIIDB['PASSWORD'],
    'charset' => 'utf8',
    'enableSchemaCache' => true,
    // Duration of schema cache.
    'schemaCacheDuration' => 300,
    // Name of the cache component used. Default is 'cache'.
    'schemaCache' => 'cache',
];