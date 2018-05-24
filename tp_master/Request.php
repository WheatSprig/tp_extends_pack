<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/11/27
 * Time: 11:36
 */

namespace mikkle\tp_master;



class Request extends \think\Request
{
    static public $ins;
    /**
     * 初始化
     * @access public
     * @param array $options 参数
     * @return \think\Request
     */
    public static function instance($options = [])
    {
        if (is_null(self::$ins)) {
            self::$ins = new static($options);
        }
        return self::$ins;
    }
}