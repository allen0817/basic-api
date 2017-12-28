<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\OnlyOne;
use yii\console\Controller;
use app\models\ProblemJob;
use Yii;
use yii\helpers\Json;
use app\models\Configure;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class HelloController extends Controller
{

    /**
     * 测试redis
     */
    public function actionIndex(){
        //file_put_contents(\Yii::getAlias('@web').'/uploads/a.txt',time());
        $redis = \Yii::$app->redis;
        echo $redis->get('name');
        //echo "hello world\n";


    }

    /**
     * 增加脚本
     * 增加触发动作
     * 查询最后更新的数据
     * 加入队列
     * 发送到 kafka
     *
     */
    public function actionProblem(){
        OnlyOne::getInstance()->handleProblem();
    }

//shell_exec('python /tmp/p.py "come form python"');
//shell_exec('python /tmp/p.py  "'.$name.'" ' );
/// shell_exec('/usr/local/php71/bin/php /usr/share/zabbix_api/yii hello/config  "'.$name.'" ' );

    /**
     * 手动更新，修改源码
     * shell 传 hostid 过来
     * php查询处理
     *
     * var_dump($_SERVER['argv']); //脚本参数
     *
     * z/hosts.php 698
     *@shell_exec('/usr/local/php71/bin/php  /usr/share/zabbix_api/yii hello/config  "'.$hostId.'" ' );
     */
    public function actionConfig(){

        $hostid = $_SERVER['argv'];
        if($hostid){
            $config = new Configure();
            $config->insertQueue($hostid[2]);
        }

    }

}
