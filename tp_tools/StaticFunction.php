<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/06/27
 * Time: 10:28
 */

namespace mikkle\tp_tools;

use mikkle\tp_master\Loader;
use mikkle\tp_master\Request;
use think\Validate;
use think\Model;
use mikkle\tp_master\Exception;
class StaticFunction
{
    static public function  instanceOfValidate($class){
        return self::instanceOfClassHandle($class,"validate");
    }

    static public function  instanceOfModel($class){
        return self::instanceOfClassHandle($class,"model");
    }

    static protected function instanceOfClassHandle($class,$type){
        switch (true){
            case (empty($class)||empty($type)):
                throw new Exception("判断归属类的参数丢失");
                break;
            case ($type==="validate"):
                $result = ($class instanceof Validate);
                break;
            case ($type==="model"):
                $result = ($class instanceof Model);
                break;
            default:
                $result=false;
        }
        if(!$result){
            throw new Exception("归属类错误或不存在");
        }
        return true;
    }

    static public function buildParameter($array)
    {
        $data=[];
        $request=Request::instance();
        foreach( $array as $item=>$value ){
            $data[$item] = $request->param($value);
        }
        return $data;
    }






}