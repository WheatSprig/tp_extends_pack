<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2017/12/13
 * Time: 15:57
 */

namespace mikkle\tp_master;
use think\Facade;

class Cookie extends Facade
{
    protected static function getFacadeClass()
    {
        return 'think\Cookie';
    }

}