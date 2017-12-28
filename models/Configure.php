<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/12/28
 * Time: 11:14
 */
namespace  app\models;

use Yii;

use app\models\ConfigureJob;



class  Configure {

    public function getHost($hostid){

        $sql=<<<sql
SELECT * from `hosts` as h 
LEFT JOIN hosts_groups as hg on hg.hostid=h.hostid
LEFT JOIN groups as g on g.groupid=hg.groupid
LEFT JOIN interface as ip on ip.hostid=h.hostid
LEFT JOIN hosts_templates as ht on ht.hostid=h.hostid
LEFT JOIN hostmacro as m on m.hostid=h.hostid
LEFT JOIN host_inventory as hi on hi.hostid=h.hostid
WHERE h.hostid= $hostid

sql;

        $data = Yii::$app->db->createCommand($sql)->queryAll();
        if(!empty($data)) return json_encode($data);
        exit();

    }

    public function insertQueue($hostid){
        $json = $this->getHost($hostid);
        Yii::$app->queue->push(new ConfigureJob([
            'data' => $json,
        ]));
    }

}

