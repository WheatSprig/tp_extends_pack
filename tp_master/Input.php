<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/1/19
 * Time: 10:31
 */

namespace mikkle\tp_master;


use think\Facade;

class Input extends Facade
{
    protected static function getFacadeClass()
    {
        return 'think\console\Input';
    }

}