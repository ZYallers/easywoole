<?php
/**
 * Created by PhpStorm.
 * User: zhongyongbiao
 * Date: 2018/10/27
 * Time: 下午12:20
 */

namespace App\Utility;


use EasySwoole\Core\Component\Logger;
use EasySwoole\Core\Component\Pool\PoolManager;

class Mysql
{
    protected $pool;
    private $db;

    protected function __construct()
    {
        // 获取连接池对象
        $this->pool = PoolManager::getInstance()->getPool('App\Utility\MysqlPool');
        $this->db = $this->pool->getObj();
    }

    /**
     * dbConnector
     * @return \EasySwoole\Core\Swoole\Coroutine\Client\Mysql
     */
    protected function getConnector(): \EasySwoole\Core\Swoole\Coroutine\Client\Mysql
    {
        return $this->db;
    }

    protected function freeConnector(): void
    {
        if (!is_null($this->db)) {
            $this->pool->freeObj($this->db);
        }
    }

}