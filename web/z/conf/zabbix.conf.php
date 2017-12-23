<?php
require_once dirname(__DIR__) . '/../../config/db.php';

// Zabbix GUI configuration file.
global $DB, $YIIDB, $YIIZBX_SERVER, $YIIZBX_SERVER_PORT;

$DB['TYPE']     = 'MYSQL';
$DB['SERVER']   = $YIIDB['SERVER'];
$DB['PORT']     = $YIIDB['PORT'];
$DB['DATABASE'] = $YIIDB['DATABASE'];
$DB['USER']     = $YIIDB['USER'];
$DB['PASSWORD'] = $YIIDB['PASSWORD'];

// Schema name. Used for IBM DB2 and PostgreSQL.
$DB['SCHEMA'] = '';

$ZBX_SERVER      = $YIIZBX_SERVER;
$ZBX_SERVER_PORT = $YIIZBX_SERVER_PORT;
$ZBX_SERVER_NAME = '';

$IMAGE_FORMAT_DEFAULT = IMAGE_FORMAT_PNG;



