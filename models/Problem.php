<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/20
 * Time: 14:34
 */

namespace app\models;

use Yii;

use app\models\ProblemJob;
use app\models\ProblemRecoveryJob;

class Problem
{

    const PROBLEM_ID  = 'PROBLEM_ID'; //告警最大的ID

    const PROBLEM_RECOVERY_ID  = 'PROBLEM_RECOVERY_ID'; //告警恢复最大的ID

    /**返回 最大的problem id
     * @return mixed
     * @throws \yii\db\Exception
     */

    public function getMaxId(){
        $maxId = Yii::$app->redis->get(self::PROBLEM_ID);

        $cache = 0;
        if(!$maxId) {
            $sql = 'SELECT max(eventid) as maxId from problem';
            $tmp = Yii::$app->db->createCommand($sql)->queryOne();
            $cache = $maxId = $tmp['maxId'];
        }else{
            $sql = 'SELECT max(eventid) as maxId from problem';
            $result = Yii::$app->db->createCommand($sql)->queryOne();
            $cache = $result['maxId'];
        }

//        Yii::info($maxId);

        $this->setMaxId($cache);
        return $maxId;
    }

    /**
     * 设置缓存
     * @param $maxId
     */

    public function setMaxId($maxId){
        Yii::$app->redis->set(self::PROBLEM_ID,$maxId);
    }

    public function getProblem(){
        $maxId = $this->getMaxId();
        $where  = ' p.eventid > '.$maxId;
        $fields = 'p.*,e.value,e.acknowledged';
        $sql = <<<sql
SELECT $fields from problem as p
LEFT JOIN  events as e on e.eventid = p.eventid
WHERE  $where
sql;
        $data = Yii::$app->db->createCommand($sql)->queryAll();
        if(!empty($data)) return json_encode($data);
        exit;
    }




    public function getProblem1(){
        //Yii::$app->response->format = Response::FORMAT_JSON;
        $maxId = $this->getMaxId();
        $where  = ' p.eventid > '.$maxId;
        $fields = '*';
        $sql = <<<sql
SELECT $fields from problem as p
LEFT JOIN  triggers as t on t.triggerid = p.objectid
LEFT JOIN functions as f on  f.triggerid = t.triggerid
LEFT JOIN  items as i on i.itemid = f.itemid 
LEFT JOIN hosts as h on h.hostid = i.hostid 
LEFT JOIN interface as ip on ip.hostid = h.hostid
WHERE  $where
sql;

        $data = Yii::$app->db->createCommand($sql)->queryAll();
        if(!empty($data)) return json_encode($data);

        exit();
    }

    /**
     * 入列
     */
    public  function insertQueue(){
        $json = $this->getProblem();
        Yii::$app->queue->push(new ProblemJob([
            'data' => $json,
        ]));
    }



    //告警恢复
    /**返回 最大的problem id
     * @return mixed
     * @throws \yii\db\Exception
     */

    public function getMaxRecoveryId(){
        $maxId = Yii::$app->redis->get(self::PROBLEM_RECOVERY_ID);

        $sql = 'SELECT max(eventid) as maxId from event_recovery';
        $result = Yii::$app->db->createCommand($sql)->queryOne();

        $cache = 0;
        if(!empty($result)){
            $cache = $result['maxId'];
            if(!$maxId){
                $maxId = $result['maxId'];
            }
            elseif ($maxId == $result || $maxId > $result ){
                exit();
            }
        }else{
            exit();
        }

        $this->setMaxRecoveryId($cache);
        return $maxId;
    }

    /**
     * 设置缓存
     * @param $maxId
     */

    public function setMaxRecoveryId($maxId){
        Yii::$app->redis->set(self::PROBLEM_RECOVERY_ID,$maxId);
    }

    public function getProblemRecovery(){
        $maxId = $this->getMaxRecoveryId();
        $where  = ' r.eventid > '.$maxId;
        $fields = 'p.*';
        $sql = <<<sql
SELECT $fields from event_recovery as r
LEFT JOIN  problem as p on p.r_eventid = r.r_eventid
WHERE  $where
sql;
        $data = Yii::$app->db->createCommand($sql)->queryAll();
        if(!empty($data)) return json_encode($data);
        exit;
    }


    /**
     * 入列
     */
    public  function insertQueueRecovery(){
        $json = $this->getProblemRecovery();
        Yii::$app->queue->push(new ProblemRecoveryJob([
            'data' => $json,
        ]));
    }


}