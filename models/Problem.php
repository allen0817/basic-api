<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/20
 * Time: 14:34
 */

namespace app\models;

use Yii;
use yii\web\Response;
use app\models\ProblemJob;

class Problem
{

    const PROBLEM_ID  = 0; //告警最大的ID

    /**返回 最大的problem id
     * @return mixed
     * @throws \yii\db\Exception
     */

    public function getMaxId(){
        $maxId = Yii::$app->redis->get(self::PROBLEM_ID);

        $sql = 'SELECT max(eventid) as maxId from problem';
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
        Yii::$app->response->format = Response::FORMAT_JSON;
        $maxId = $this->getMaxId();
        $where  = ' p.eventid > '.$maxId;
        $fields = '*';
        $sql = <<<sql
SELECT $fields from problem as p
LEFT JOIN  triggers as t on t.triggerid = p.objectid
LEFT JOIN function as f on  f.triggerid = t.triggerid
LEFT JOIN  item as i on i.itemid = f.itemid 
LEFT JOIN hosts as h on h.hostid = i.hostid 
LEFT JOIN interface as ip on ip.hostid = h.hostid
WHEN  $where
sql;
        return Yii::$app->db->createCommand($sql)->queryAll();
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


}