<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2017/9/19
 * Time: 10:16
 */

namespace mikkle\tp_worker;


use mikkle\tp_redis\Redis;

class WorkerRedis
{
    static public function instance(){
        return Redis::instance( [
            "index"=>3,
            "auth"=>"",
            "port" => "6379",
            "host" => "127.0.0.1" ,
        ]);
    }
}