<?php
/**
 * Created by PhpStorm.
 * User: zhongyongbiao
 * Date: 2018/10/25
 * Time: 下午3:11
 */

namespace App\HttpController\V410\Test;


use App\HttpController\Base;

class Index extends Base
{

    public function index()
    {
        $this->writeJson(404);
    }

    public function test05()
    {
        $uri = $this->request()->getUri();
        $this->response()->write($uri);
    }

}