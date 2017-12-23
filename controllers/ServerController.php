<?php
/**
 * Created by PhpStorm.
 * User: Allen
 * Date: 2017/12/15
 * Time: 14:09
 */

namespace app\controllers;


use yii\rest\ActiveController;
use Yii;
use yii\filters\ContentNegotiator;
use yii\web\Response;

class ServerController extends  ActiveController
{

    public $serializer = [
        'class' => 'yii\rest\Serializer',
        'collectionEnvelope' => 'items',
    ];

    public function init()
    {
        parent::init();
        \Yii::$app->user->enableSession = false;
    }

    public $modelClass = '';



    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator'] =[
            'class' => ContentNegotiator::className(),
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
                'application/xml' => Response::FORMAT_XML,
            ],
        ];
        return $behaviors;
    }

    public function actions()
    {
        return [];
    }


    /**
     * @param $token
     * @param $host
     * @return mixed
     */

    public function actionReboot($host){
        $result =  Yii::$app->zabbix->callApi('script','execute',[
            "scriptid" => 4,
            "hostid" => $host,  //10338  10245 10339
        ]);
        return $result;
    }

    /**
     * @param $name
     * @param $pwd
     * @return mixed
     */

    public function actionLogin($name,$pwd){
        return  Yii::$app->zabbix->login($name, $pwd);
    }


}
