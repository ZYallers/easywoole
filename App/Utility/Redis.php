<?php
/**
 * Created by PhpStorm.
 * User: zhongyongbiao
 * Date: 2018/10/27
 * Time: 下午12:20
 */

namespace App\Utility;


use EasySwoole\Core\Component\Pool\PoolManager;

class Redis
{
    protected $pool;
    private $cache;

    public function __construct()
    {
        $this->pool = PoolManager::getInstance()->getPool('App\Utility\RedisPool');
        $this->cache = $this->pool->getObj();
    }

    public function getConnector(): \EasySwoole\Core\Swoole\Coroutine\Client\Redis
    {
        return $this->cache;
    }

    public function freeConnector(): void
    {
        if (!is_null($this->cache)) {
            $this->pool->freeObj($this->cache);
        }
    }

}