<?php
/**
 * Created by PhpStorm.
 * User: zhongyongbiao
 * Date: 2018/10/26
 * Time: 下午12:49
 */

namespace App\HttpController;


use EasySwoole\Config;
use \EasySwoole\Core\Swoole\ServerManager;
use EasySwoole\Core\Http\AbstractInterface\Controller;
use EasySwoole\Core\Http\Message\Status;

abstract class Base extends Controller
{
    protected function writeJson($statusCode = 200, $data = null, $msg = null)
    {
        if (!$this->response()->isEndResponse()) {
            if (is_null($msg)) {
                $msg = Status::getReasonPhrase($statusCode);
            }
            $data = ['code' => $statusCode, 'data' => $data, 'msg' => is_null($msg) ? '' : $msg];
            // debug 模式下多返回一些信息
            if (Config::getInstance()->getConf('DEBUG')) {
                // 从请求里获取之前增加的时间戳
                $reqTime = $this->request()->getAttribute('request_time');
                // 计算一下运行时间
                $runTime = round(microtime(true) - $reqTime, 6);
                // 获取用户IP地址
                $ip = ServerManager::getInstance()->getServer()->connection_info($this->request()->getSwooleRequest()->fd);
                // 拼接日志内容
                $debugInfo = ['ip' => $ip['remote_ip'], 'runtime' => $runTime, 'uri' => $this->request()->getUri()->__toString()];
                $userAgent = $this->request()->getHeader('user-agent');
                if (is_array($userAgent) && count($userAgent) > 0) {
                    $debugInfo['user_agent'] = $userAgent[0];
                }
                $this->request()->withAttribute('debug_info', $debugInfo);
                $data['debug'] = $debugInfo;
            }
            $this->response()->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $this->response()->withHeader('Content-type', 'application/json;charset=utf-8');
            $this->response()->withStatus($statusCode);
            return true;
        } else {
            trigger_error("response has end");
            return false;
        }
    }
}