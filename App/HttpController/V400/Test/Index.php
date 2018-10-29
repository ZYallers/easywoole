<?php
/**
 * Created by PhpStorm.
 * User: zhongyongbiao
 * Date: 2018/10/25
 * Time: 下午3:11
 */

namespace App\HttpController\V400\Test;


use App\HttpController\Base;
use App\Model\User\UseInfo;
use EasySwoole\Config;
use EasySwoole\Core\Http\Message\Status;

class Index extends Base
{

    public function index()
    {
        $this->writeJson(404);
    }

    public function allconfig()
    {
        $Config = Config::getInstance();
        $all = $Config->toArray();
        $this->writeJson(200, $all);
    }

    public function test01()
    {
        $params = $this->request()->getRequestParam('user_id', 'product_id');
        $this->response()->write(json_encode($params, JSON_UNESCAPED_UNICODE));
        $this->response()->end();
    }

    public function test02()
    {
        $server = $this->request()->getServerParams();
        $this->writeJson(Status::CODE_OK, $server, Status::getReasonPhrase(Status::CODE_OK));
    }

    public function test03()
    {
        $UserInfo = new UseInfo();
        $User = $UserInfo->getOneByMobile('13670896425');
        $this->writeJson(200, $User, Status::getReasonPhrase(Status::CODE_OK));
    }

    public function test04()
    {
        $Redis = new Redis();
        $obj = $Redis->getConnector();
        $data = [];
        //$data[] = $obj->exec('SETEX', 'test1111', '60', '12345678');
        $data[] = $obj->exec('GET', 'test1111');
        $data[] = $obj->exec('TTL', 'test1111');
        $Redis->freeConnector();
        $this->writeJson(200, ['data' => $data]);
    }

    public function sleep()
    {
        $time = intval($this->request()->getRequestParam('time'));
        sleep($time);
        $this->writeJson(Status::CODE_NOT_FOUND, null, "sleep {$time}s.");
    }

    public function concurrent()
    {
        //以下流程网络IO的时间就接近于 MAX(q1网络IO时间, q2网络IO时间)。
        $micro = microtime(true);

        $q1 = new Http('http://127.0.0.1:9501/test/index/sleep?time=2');
        $c1 = $q1->exec(true);
        $q2 = new Http('http://127.0.0.1:9501/test/index/sleep?time=3');
        $c2 = $q2->exec(true);
        $q3 = new Http('http://ip.taobao.com/service/getIpInfo.php');
        $q3->setGet(['ip' => '121.40.81.149']);
        $c3 = $q3->exec(true);
        $c1->recv();
        $c2->recv();
        $c3->recv();
        $c1->close();
        $c2->close();
        $c3->close();
        $data = [
            'q1' => $c1->body,
            'q2' => $c2->body,
            'q3' => $c3->body
        ];
        $data['run_time'] = round(microtime(true) - $micro, 6) . 's';
        $this->writeJson(200, $data);
    }

    function concurrent2()
    {
        //以下流程网络IO的时间就接近于 MAX(q1网络IO时间, q2网络IO时间)
        $micro = microtime(true);
        $ret = [];
        for ($i = 0; $i < 100; $i++) {
            $ret[$i] = (new Http('http://127.0.0.1:9501/test/index/test03?id=' . $i))->exec(true);
        }
        $data = [];
        for ($i = 0; $i < 100; $i++) {
            $ret[$i]->recv();
            $ret[$i]->close();
            $data[$i] = $ret[$i]->body;
        }
        $time = round(microtime(true) - $micro, 6) . 's';
        $this->writeJson(200, ['run_time' => $time, 'data' => $data]);
    }

    public function noconcurrent()
    {
        //传统阻塞
        $data = [];
        $micro = microtime(true);
        $req = new \EasySwoole\Core\Utility\Curl\Request('http://127.0.0.1:9501/test/index/sleep?time=1');
        $data['req1'] = $req->exec()->getBody();
        $req2 = new \EasySwoole\Core\Utility\Curl\Request('http://127.0.0.1:9501/test/index/sleep?time=4');
        $data['req2'] = $req2->exec()->getBody();
        $data['time'] = round(microtime(true) - $micro, 6) . 's';
        $this->writeJson(200, $data);
    }

    public function bingfa()
    {
        $tasks[] = function () {
            sleep(1);
            return 'task1';
        }; // 任务1
        $tasks[] = function () {
            sleep(2);
            return 'task2';
        };     // 任务2
        $tasks[] = function () {
            sleep(3);
            return 'task3';
        }; // 任务3

        $start = microtime(true);
        $results = TaskManager::barrier($tasks, 5);
        $data = ['spent' => sprintf('%.6f', microtime(true) - $start), 'result' => (array)$results];
        $this->writeJson(200, $data, 'ok');
    }

}