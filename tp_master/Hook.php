<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2017/12/11
 * Time: 9:15
 */

namespace mikkle\tp_master;


use think\Facade;

class Hook extends Facade
{
    protected static function getFacadeClass()
    {
        return 'think\Config';
    }
}