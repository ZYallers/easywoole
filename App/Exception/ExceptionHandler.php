<?php
/**
 * Created by PhpStorm.
 * User: zhongyongbiao
 * Date: 2018/10/25
 * Time: 下午3:35
 */

namespace App\Exception;


use App\Utility\Logger;
use EasySwoole\Core\Http\AbstractInterface\ExceptionHandlerInterface;
use EasySwoole\Core\Http\Request;
use EasySwoole\Core\Http\Response;

class ExceptionHandler implements ExceptionHandlerInterface
{
    public function handle(\Throwable $throwable, Request $request, Response $response)
    {
        Logger::getInstance()->log($throwable->__toString() . "\n", 'exception');
    }
}