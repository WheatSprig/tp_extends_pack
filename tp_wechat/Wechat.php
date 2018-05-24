<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/8/30
 * Time: 9:21
 */

namespace mikkle\tp_wechat;

use mikkle\tp_master\Exception;
use mikkle\tp_wechat\base\ErrCode;
use mikkle\tp_wechat\src\Card;
use mikkle\tp_wechat\src\Custom;
use mikkle\tp_wechat\src\Device;
use mikkle\tp_wechat\src\Extend;
use mikkle\tp_wechat\src\Hardware;
use mikkle\tp_wechat\src\Media;
use mikkle\tp_wechat\src\Menu;
use mikkle\tp_wechat\src\Message;
use mikkle\tp_wechat\src\Oauth;
use mikkle\tp_wechat\src\Poi;
use mikkle\tp_wechat\src\Receive;
use mikkle\tp_wechat\src\Script;
use mikkle\tp_wechat\src\User;

use think\Config;

class Wechat
{
    static protected $instance;


    public function __construct(array $options = [])
    {
        $options = self::getOptions($options);
        $sn = md5("{$options["appid"]}{$options["appsecret"]}");
        $this->sn = $sn;

    }


    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $options
     * @return Message
     * @throws Exception
     */
    static public function message($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn]["message"])){
            return self::$instance[$sn]["message"];
        }else{
            return self::$instance[$sn]["message"] = new Message($options);
        }
    }

    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $options
     * @return Device
     * @throws Exception
     */
    static public function device($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn]["device"])){
            return self::$instance[$sn]["device"];
        }else{
            return self::$instance[$sn]["device"] = new Device($options);
        }
    }


    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $options
     * @return Receive
     * @throws Exception
     */
    static public function receive($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn]["receive"])){
            return self::$instance[$sn]["receive"];
        }else{
            return self::$instance[$sn]["receive"] = new Receive($options);
        }
    }


    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $options
     * @return Menu
     * @throws Exception
     */
    static public function menu($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn]["menu"])){
            return self::$instance[$sn]["menu"];
        }else{
            return self::$instance[$sn]["menu"] = new Menu($options);
        }
    }

    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $options
     * @return Script
     * @throws Exception
     */
    static public function script($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn]["script"])){
            return self::$instance[$sn]["script"];
        }else{
            return self::$instance[$sn]["script"] = new Script($options);
        }
    }

    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $options
     * @return User
     * @throws Exception
     */
    static public function user($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn]["user"])){
            return self::$instance[$sn]["user"];
        }else{
            return self::$instance[$sn]["user"] = new User($options);
        }
    }

    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $options
     * @return Card
     * @throws Exception
     */
    static public function card($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn]["card"])){
            return self::$instance[$sn]["card"];
        }else{
            return self::$instance[$sn]["card"] = new Card($options);
        }
    }

    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $options
     * @return Hardware
     * @throws Exception
     */
    static public function hardware($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn]["hardware"])){
            return self::$instance[$sn]["hardware"];
        }else{
            return self::$instance[$sn]["hardware"] = new Hardware($options);
        }
    }

    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $options
     * @return Extend
     * @throws Exception
     */
    static public function extend($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn]["extend"])){
            return self::$instance[$sn]["extend"];
        }else{
            return self::$instance[$sn]["extend"] = new Extend($options);
        }
    }

    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $options
     * @return Custom
     * @throws Exception
     */
    static public function custom($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn]["custom"])){
            return self::$instance[$sn]["custom"];
        }else{
            return self::$instance[$sn]["custom"] = new Custom($options);
        }
    }

    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $options
     * @return Oauth
     * @throws Exception
     */
    static public function oauth($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn]["oauth"])){
            return self::$instance[$sn]["oauth"];
        }else{
            return self::$instance[$sn]["oauth"] = new Oauth($options);
        }
    }

    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $options
     * @return Media
     * @throws Exception
     */
    static public function media($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn]["media"])){
            return self::$instance[$sn]["media"];
        }else{
            return self::$instance[$sn]["media"] = new Media($options);
        }
    }


    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $options
     * @return Poi
     * @throws Exception
     */
    static public function poi($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn]["poi"])){
            return self::$instance[$sn]["poi"];
        }else{
            return self::$instance[$sn]["poi"] = new Poi($options);
        }
    }



    protected static function getSn(array $options = []){
        $options =self::getOptions($options);
        return md5("{$options["appid"]}{$options["appsecret"]}");
    }



    protected static function getOptions( $options = []){
        if (empty($options)&& !empty( Config::get("wechat.default_options_name"))){
            $options = Config::get("wechat.".Config::get("wechat.default_options_name"));
        }elseif(is_string($options)&&!empty( Config::get("wechat.$options"))){
            $options = Config::get("wechat.$options");
        }
        if (empty($options)) {
            $error[]="获取Token参数缺失";
            throw new Exception("微信配置参数不存在");
            return false ;
        }elseif(isset($options["appid"])&&isset($options["appsecret"])){
            return $options ;
        }else{
            throw new Exception("微信配置参数不完整");
            return false ;
        }
    }

    static public function getErrText($code){
        return ErrCode::getErrText($code);
    }




}