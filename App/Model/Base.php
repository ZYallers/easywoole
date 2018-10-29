<?php
/**
 * Created by PhpStorm.
 * User: zhongyongbiao
 * Date: 2018/10/27
 * Time: 下午12:42
 */

namespace App\Model;


use App\Utility\Mysql;

abstract class Base extends Mysql
{
    public function __construct()
    {
        parent::__construct();
    }

    const TABLE_USER_INFO = 'et_user_info';

}