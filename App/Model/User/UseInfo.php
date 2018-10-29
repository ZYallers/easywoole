<?php
/**
 * Created by PhpStorm.
 * User: zhongyongbiao
 * Date: 2018/10/27
 * Time: 下午12:38
 */

namespace App\Model\User;


use App\Model\Base;
use App\Utility\Logger;

class UseInfo extends Base
{
    public function getOneByMobile(string $mobile): array
    {
        $ret = [];
        try {
            $ret = $this->getConnector()->where('mobile', $mobile)->getOne(self::TABLE_USER_INFO);
        } catch (\Throwable $throwable) {
            Logger::getInstance()->logException($throwable->__toString());
        } finally {
            $this->freeConnector();
        }
        return $ret;
    }
}