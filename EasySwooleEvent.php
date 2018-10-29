<?php
/**
 * Created by PhpStorm.
 * User: yf
 * Date: 2018/1/9
 * Time: 下午1:04
 */

namespace EasySwoole;

use App\Exception\ExceptionHandler;
use \EasySwoole\Core\AbstractInterface\EventInterface;
use EasySwoole\Core\Component\Di;
use EasySwoole\Core\Component\SysConst;
use EasySwoole\Core\Http\Message\Status;
use \EasySwoole\Core\Swoole\ServerManager;
use \EasySwoole\Core\Swoole\EventRegister;
use \EasySwoole\Core\Http\Request;
use \EasySwoole\Core\Http\Response;

Class EasySwooleEvent implements EventInterface
{

    public static function frameInitialize(): void
    {
        // TODO: Implement frameInitialize() method.
        date_default_timezone_set('Asia/Shanghai');
        // 载入项目 Conf 文件夹中的所有的配置文件
        Config::getInstance()->loadPath(EASYSWOOLE_ROOT . '/Conf');
        // 允许 URL 最大解析至5层
        Di::getInstance()->set(SysConst::HTTP_CONTROLLER_MAX_DEPTH, 5);
        // 异常捕获处理
        Di::getInstance()->set(SysConst::HTTP_EXCEPTION_HANDLER, ExceptionHandler::class);
    }

    public static function mainServerCreate(ServerManager $server, EventRegister $register): void
    {
        // TODO: Implement mainServerCreate() method.
        // 注册主服务回调事件
        $register->add($register::onWorkerStart, function (\swoole_server $server, int $workerId) {
            echo $workerId . " Start.\n";
        });
        $register->add($register::onWorkerStop, function (\swoole_server $server, int $workerId) {
            echo $workerId . " Stop.\n";
        });
        $register->add($register::onWorkerExit, function (\swoole_server $server, int $workerId) {
            echo $workerId . " Exit.\n";
        });
    }

    private static function versionCheck(Request &$request, Response &$response)
    {
        $msg = null;
        $uriPath = substr($request->getUri()->getPath(), 1);
        $router = Config::getInstance()->getConf('router');
        if (array_key_exists($uriPath, $router)) {
            $uriRouter = $router[$uriPath];
            if (in_array(strtolower($request->getMethod()), explode(',', $uriRouter['method']))) {
                $version = $request->getRequestParam('version');
                if (empty($version)) {
                    $version = Config::getInstance()->getConf('APP_VERSION');
                }
                if (version_compare($version, $uriRouter['version'], '>=')) {
                    $module = join('', explode('.', $uriRouter['version']));
                    $path = "/v{$module}/{$uriPath}";
                    $request->getUri()->withPath($path);
                } else {
                    $msg = "Version '{$version}' is too low";
                }
            } else {
                $msg = 'Method not allowed';
            }
        } else {
            $msg = 'Uri not found';
        }

        if (!is_null($msg)) {
            $data = ['code' => Status::CODE_NOT_FOUND, 'data' => null, 'msg' => $msg];
            $response->write(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
            $response->withHeader('Content-type', 'application/json;charset=utf-8');
            $response->withStatus(Status::CODE_NOT_FOUND);
            $response->end();
        }
    }

    public static function onRequest(Request $request, Response $response): void
    {
        // TODO: Implement onRequest() method.
        // 记录执行时间长短
        $request->withAttribute('request_time', microtime(true));
        // 接口版本校验
        self::versionCheck($request, $response);
    }

    public static function afterAction(Request $request, Response $response): void
    {
        // TODO: Implement afterAction() method.
        // 超过 3 秒记录到 slow 日志文件
        $debugInfo = $request->getAttribute('debug_info');
        if (is_array($debugInfo) && isset($debugInfo['runtime']) && $debugInfo['runtime'] >= 3) {
            \App\Utility\Logger::getInstance()->log(join('|', $debugInfo), 'slow');
        }

    }
}