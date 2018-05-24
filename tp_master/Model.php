<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/11/27
 * Time: 10:25
 */

namespace mikkle\tp_master;


use think\Facade;

class Model extends Facade
{
    protected static function getFacadeClass()
    {
        return 'think\Model';
    }

}