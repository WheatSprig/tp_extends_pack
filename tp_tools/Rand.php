<?php
/**
 * Created by PhpStorm.
 * User: 77632
 * Date: 2017/6/8
 * Time: 11:09
 */

namespace mikkle\tp_tools;

use mikkle\tp_redis\Redis;

class Rand
{
    /**
     * 创建随机数
     * Power by Mikkle
     * QQ:776329498
     * @param int $num  随机数位数
     * @return string
     */
    static public function createRandNum($num=8){
        return substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, $num);
    }



    /*
* 通过创建随机数
*/
    static public function createSerialNumberByName($name,$num=24){
        return self::createSerialNumberByRedis($num,__FUNCTION__.$name);
    }

    /*
     * 通过前缀创建随机数
     */
    static public function createSerialNumberByPrefix($prefix,$num=24){
        return ((string)$prefix).self::createSerialNumberByRedis($num,__FUNCTION__.$prefix);
    }



    static public function createSerialNumberByRedis( $num=24,$name ="createSerialNumber" ){
        if ((int)$num<24){
            $num = 24;
        }
        return  ((string)self::getTimeInt()).substr(((string) (1*pow(10,($num-14) )+self::redis()->incre($name) )) ,1);
    }

    static public function createNumberString( $length=10 ){
        $len=1;
        $prefix="1";
        return (string) (1*pow(10,($length-$len)) +self::redis()->incre("createNumberString_{$prefix}") );
    }

    /**
     * title
     * description createNumberStringByPrefix
     * User: Mikkle
     * QQ:776329498
     * @param string $prefix
     * @param int $length
     * @return string
     */
    static public function createNumberStringByPrefix( $prefix  ,$length=12 ){
        $len=strlen($prefix);
        $str = (string) (1*pow(10,($length-$len)) +self::redis()->incre("createNumberString_{$prefix}") );
        return  $prefix . substr($str,1);
    }

    /*
* 获取Redis中使用的当天时间时间字符串
*/
    static public function getTimeString($time=null){
        if ( $time ){
            return date("Y-m-d H:i:s",$time) ;
        }
        return date("Y-m-d H:i:s") ;
    }

    /*
* 获取Redis中使用的当天时间时间字符串
*/
    static public function getTimeInt($time=null){
        if ( $time ){
            return date("YmdHis",$time) ;
        }
        return (int) date("YmdHis") ;
    }

    /*
     * 获取Redis中使用的当天时间时间字符串
     */
    static public function getDateString($time=null){
        if ( $time ){
            return date("Ymd",$time) ;
        }
        return date("Ymd") ;
    }

    /*
 * 获取Redis中使用的当天时间时间字符串前缀
 */
    static public function getDatePrefix($time=null){
        if ( $time ){
            return date("Ymd_",$time) ;
        }
        return date("Ymd_") ;
    }

    /**
     * 创建个性GUID
     * Power by Mikkle
     * QQ:776329498
     * @param string $base_code
     * @return string
     */
    static public function createUuid($base_code = '')
    {
        if (empty($base_code)) {
            $base_code = isset($base_code) ? $base_code: 'QT';
        }
        $uuid = $base_code . strtoupper(uniqid()) . self::builderRand(6);
        return $uuid;
    }
    
    static public function builderRand($num=8){
        return substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, $num);
    }

    /*
 * 分转成元
 */
    static public function CNYFenToYuan($fen){
        return sprintf("%.2f", ($fen/100) );
    }

    /*
     * 百分比
     */
    static public function baiFenBi($dividend="",$divisor="",$show=false){
        if (empty($dividend) || empty($divisor) || $divisor == 0) {
            $result =  "0";
        } elseif (!is_numeric($dividend) || !is_numeric($divisor)) {
            $result = "0";
        } else {
            $result =  sprintf("%.2f",($dividend * 100 / $divisor) );
        }
        return $show ? $result."%" : $result;
    }


    static protected function redis(){
        return Redis::instance(
          ["index"=>2]
        );
    }

}