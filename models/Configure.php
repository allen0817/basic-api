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

    public function getHost1($hostid){
        $fields = 'h.*';

        $fields = $fields. ' ,hg.hostgroupid,hg.groupid,g.name as group_name';
        $fields = $fields.' ,ip.interfaceid,ip.main as ip_main,ip.type as ip_type,ip.useip,ip.ip,ip.dns,ip.port,ip.bulk';
        $fields = $fields. ' ,ht.hosttemplateid,ht.templateid as template_templateid';
        $fields = $fields. ' ,m.hostmacroid,m.macro,m.value';

        $fields = $fields. ' ,hi.*,hi.name as hi_name';
        $fields = $fields.' ,h.hostid,h.name';

            $sql=<<<sql
SELECT $fields from `hosts` as h 
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

    public function getHost($hostid){
        $hosts = Yii::$app->db->createCommand('SELECT * from hosts WHERE hostid=:hostid' )->bindValue('hostid',$hostid)->queryOne();

        if(!empty($hosts)){
            $hosts_groups = Yii::$app->db->createCommand('SELECT * from hosts_groups WHERE hostid=:hostid' )->bindValue('hostid',$hostid)->queryOne();
            $groups = Yii::$app->db->createCommand('SELECT * from groups WHERE groupid=:groupid' )->bindValue('groupid',$hosts_groups['groupid'])->queryOne();
            $interface = Yii::$app->db->createCommand('SELECT * from interface WHERE hostid=:hostid' )->bindValue('hostid',$hostid)->queryOne();
            $hosts_templates = Yii::$app->db->createCommand('SELECT * from hosts_templates WHERE hostid=:hostid' )->bindValue('hostid',$hostid)->queryOne();
            $hostmacro = Yii::$app->db->createCommand('SELECT * from hostmacro WHERE hostid=:hostid' )->bindValue('hostid',$hostid)->queryOne();
            $host_inventory = Yii::$app->db->createCommand('SELECT * from host_inventory WHERE hostid=:hostid' )->bindValue('hostid',$hostid)->queryOne();

            $data = compact('hosts','hosts_groups','groups','interface','hosts_templates','hostmacro','host_inventory');
            return json_encode($data);
        }
        exit();
    }



    public function insertQueue($hostid){
        $json = $this->getHost($hostid);
        Yii::$app->queue->push(new ConfigureJob([
            'data' => $json,
        ]));
    }

}

