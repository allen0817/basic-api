<?php
/**
 * Created by PhpStorm.
 * User: ukerzheng
 * Date: 2017/5/15
 * Time: 15:13
 */

namespace app\components;

use Yii;
use yii\base\Component;
use yii\db\Query;
use yii\httpclient\Client;

class ZabbixApiComponent extends Component
{
    public $zUrl;

    /**
     * Zabbix server host name.
     *
     * @var string
     */
    public $host;

    /**
     * Zabbix server port number.
     *
     * @var string
     */
    public $port;

    /**
     * Request timeout.
     *
     * @var int
     */
    public $timeout = 3;

    /**
     * Zabbix server socket resource.
     *
     * @var resource
     */
    protected $socket;

    /**
     * Error message.
     *
     * @var string
     */
    protected $error;

    /**
     * Response value if the request has been executed successfully.
     */
    const RESPONSE_SUCCESS = 'success';

    /**
     * Response value if an error occurred.
     */
    const RESPONSE_FAILED = 'failed';

    /**
     * Maximum response size. If the size of the response exceeds this value, an error will be triggered.
     *
     * @var int
     */
    protected $totalBytesLimit;

    /**
     * Bite count to read from the response with each iteration.
     *
     * @var int
     */
    protected $readBytesLimit = 8192;

    /**
     * Total result count (if any).
     *
     * @var int
     */
    protected $total;

    /**
     * @param $object 对象
     * @param $method 方法
     * @param array $parameters 参数数组
     * @return mixed
     */



    public function callApi($object, $method, $parameters = [])
    {

        $token_value = $this->login("Admin",'zabbix');
//echo $token_value;die;
        $client   = new Client();
        $response = $client->createRequest()
            ->setMethod('post')
            ->setHeaders('Content-Type:application/json-rpc')
            ->setFormat($client::FORMAT_JSON)
            ->setUrl($this->getZUrl())
            ->setData([
                'jsonrpc' => '2.0',
                'method'  => 'script.execute',
                'params'  => $parameters,
                'auth'    => $token_value ,
                'id'      => 1,
            ])
            ->send();
        if ($response->isOk) {
            return $response->data;
        } else {
            return [
                'result'=>[
                    'response' => false,
                    'value' => $response->statusCode . '请求失败',
                ]
            ];

        }

    }

    /**
     * @param $username
     * @param $password
     * @return array
     */
    public function login($username,$password)
    {
        $client   = new Client();
        $response = $client->createRequest()
            ->setMethod('post')
            ->setHeaders('Content-Type:application/json-rpc')
            ->setFormat($client::FORMAT_JSON)
            ->setUrl($this->getZUrl())
            ->setData([
                'jsonrpc' => '2.0',
                'method'  => 'user.login',
                'params'  => [
                    'user'     => 'Admin',
                    'password' => 'zabbix',
                ],
                'id' => 1,
            ])
            ->send();




        if ($response->isOk && isset($response->data['result'])) {
            return $response->data['result'];

        } else {
            return null;
        }
    }

    /**
     * @return mixed
     */
    public function getZUrl()
    {
        if (isset(Yii::$app->params['rpcAddr']) && Yii::$app->params['rpcAddr']) {
            return Yii::$app->params['rpcAddr'];
        }

        return !empty($this->zUrl) ? $this->zUrl : Yii::$app->urlManager->createAbsoluteUrl('z/api_jsonrpc.php');
    }



    /**
     * Opens a socket to the Zabbix server. Returns the socket resource if the connection has been established or
     * false otherwise.
     *
     * @return bool|resource
     */
    protected function connect()
    {
        if (!$this->socket) {
            if (!$this->host || !$this->port) {
                return false;
            }

            if (!$socket = @fsockopen($this->host, $this->port, $errorCode, $errorMsg, $this->timeout)) {
                switch ($errorMsg) {
                    case 'Connection refused':
                        $dErrorMsg = '连接采集服务' . $this->host . '失败';
                        break;

                    case 'No route to host':
                        $dErrorMsg = '网络无法连接到' . $this->host;
                        break;

                    case 'Connection timed out':
                        $dErrorMsg = '连接采集服务' . $this->host . '超时';
                        break;

                    default:
                        $dErrorMsg = '连接采集服务' . $this->host . '失败';
                }

                $this->error = $dErrorMsg . $errorMsg;
            }

            $this->socket = $socket;
        }

        return $this->socket;
    }

    public function isRunning()
    {
        return (bool) $this->connect();
    }


    protected function request(array $params)
    {
        // reset object state
        $this->error = null;
        $this->total = null;

        // connect to the server
        if (!$this->connect()) {
            return false;
        }

        // set timeout
        stream_set_timeout($this->socket, $this->timeout);

        // send the command
        if (fwrite($this->socket, json_encode($params)) === false) {
            $this->error = '无法发送命令，请检查与采集服务器' . $this->host . '的网络情况';

            return false;
        }

        // read the response
        $readBytesLimit = ($this->totalBytesLimit && $this->totalBytesLimit < $this->readBytesLimit)
        ? $this->totalBytesLimit
        : $this->readBytesLimit;

        $response = '';
        $now      = time();
        $i        = 0;
        while (!feof($this->socket)) {
            $i++;
            if ((time() - $now) >= $this->timeout) {
                $this->error = '连接采集服务' . $this->host . '已超时' . $this->timeout;
                return false;
            } elseif ($this->totalBytesLimit && ($i * $readBytesLimit) >= $this->totalBytesLimit) {
                $this->error = '响应数据已超过限制';
                return false;
            }

            if (($out = fread($this->socket, $readBytesLimit)) !== false) {
                $response .= $out;
            } else {
                $this->error = '无法获取响应，请检查采集服务器' . $this->host . '连接情况';
                return false;
            }
        }

        fclose($this->socket);

        // check if the response is empty
        if (!strlen($response)) {
            $this->error = '响应数据为空';

            return false;
        }

        $response = json_decode($response, JSON_OBJECT_AS_ARRAY);
        if (!$response || !$this->validateResponse($response)) {
            $this->error = '数据响应异常';

            return false;
        }

        // request executed successfully
        if ($response['response'] == self::RESPONSE_SUCCESS) {
            // saves total count
            $this->total = array_key_exists('total', $response) ? $response['total'] : null;

            return $response['data'];
        } // an error on the server side occurred
        else {
            $this->error = $response['info'];

            return false;
        }
    }

    /**
     * Returns true if the response received from the Zabbix server is valid.
     *
     * @param array $response
     *
     * @return bool
     */
    protected function validateResponse(array $response)
    {
        return (isset($response['response'])
            && ($response['response'] == self::RESPONSE_SUCCESS && isset($response['data'])
                || $response['response'] == self::RESPONSE_FAILED && isset($response['info'])));
    }
}
