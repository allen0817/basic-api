<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/20
 * Time: 15:14
 */

namespace app\models;


use yii\base\BaseObject;

class ProblemJob extends BaseObject implements \yii\queue\Job
{
    public $data;

    /**
     * 发送消息到kafka
     * @param \yii\queue\Queue $queue
     */
    public function execute($queue)
    {
        file_put_contents('a.txt', file_put_contents( $this->data ));
    }


}