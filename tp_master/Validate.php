<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/11/27
 * Time: 16:18
 */

namespace mikkle\tp_master;


use think\Facade;

class Validate extends Facade
{
    protected static function getFacadeClass()
    {
        return 'think\Validate';
    }

}