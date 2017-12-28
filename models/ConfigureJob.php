<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/28
 * Time: 11:20
 */

namespace  app\models;

use yii\base\BaseObject;

class ConfigureJob extends BaseObject implements \yii\queue\Job{

    public  $data;

    public function execute($queue)
    {
        // TODO: Implement execute() method.
        shell_exec('python /usr/share/zabbix_api/shell/configure.py  \''.$this->data.'\'' );

    }
}
