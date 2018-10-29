<?php
/**
 * Created by PhpStorm.
 * User: zhongyongbiao
 * Date: 2018/10/26
 * Time: 下午5:27
 */

return [
    'host' => '121.41.83.91', // redis主机地址
    'port' => 6379, // 端口
    'serialize' => false, // 是否序列化php变量
    'db_name' => 1, // db名
    'auth' => 'n5R8VzYs615', // 密码
    'pool' => [
        'min' => 5, // 最小连接数
        'max' => 100 // 最大连接数
    ],
    'errorHandler' => function () {
        return null;
    } // 如果Redis重连失败，会判断errorHandler是否callable，如果是，则会调用，否则会抛出异常，请自行try
];