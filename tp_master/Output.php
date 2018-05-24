<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/1/19
 * Time: 10:32
 */

namespace mikkle\tp_master;


use think\Facade;

class Output extends Facade
{
    protected static function getFacadeClass()
    {
        return 'think\console\Output';
    }
}