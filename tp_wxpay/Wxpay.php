<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/4/4
 * Time: 8:57
 */

namespace mikkle\tp_wxpay;


use mikkle\tp_master\Config;
use mikkle\tp_master\Exception;
use mikkle\tp_wxpay\src\DownloadBill;
use mikkle\tp_wxpay\src\JsApi;
use mikkle\tp_wxpay\src\NativeCall;
use mikkle\tp_wxpay\src\NativeLink;
use mikkle\tp_wxpay\src\Notify;
use mikkle\tp_wxpay\src\OrderQuery;
use mikkle\tp_wxpay\src\ShortUrl;
use mikkle\tp_wxpay\src\UnifiedOrder;

class Wxpay
{
    static protected $instance;
    protected $options=[];
    public function __construct($options=[])
    {
            $this->options=empty($this->options)? $this->getOptions($options) : array_merge( [],$this->getOptions($options));
    }

    public static function instance($options=[])
    {
        $sn = (isset($options["mch_id"]) && isset($options["appid"]))  ? self::getSn($options) :"0";
        if (isset(self::$instance[$sn])){
            return self::$instance[$sn];
        }
        return  self::$instance[$sn]=new static($options);
    }


    protected static function getSn($options)
    {
        return md5("{$options["appid"]}{$options["mch_id"]}");
    }


        protected  function getOptions( $options = []){
        if (empty($options)&& !empty( Config::get("wxpay.default_options_name"))){
            $options = Config::get("wxpay.".Config::get("wxpay.default_options_name"));
        }elseif(is_string($options)&&!empty( Config::get("wxpay.$options"))){
            $options = Config::get("wxpay.$options");
        }
        if (empty($options)&&empty($this->options)) {
            $error[]="微信支付配置参数缺失";
            throw new Exception("微信支付配置参数不存在");
        }elseif(isset($options["appid"])&&isset($options["secret"])&&isset($options["mch_id"])&&isset($options["key"])){
            return $options ;
        }else{
            if (!$this->options){
                throw new Exception("微信支付配置参数不完整");
            }
        }
    }

    public function unifiedOrder(){
            return new UnifiedOrder($this->options);
    }

    /**
     * title
     * description jsApi
     * User: Mikkle
     * QQ:776329498
     * @return JsApi
     */
    public function jsApi(){
        return new JsApi( $this->options );
    }

    public function DownloadBill(){
        return new DownloadBill( $this->options );
    }
    public function NativeCall(){
        return new NativeCall( $this->options );
    }
    public function NativeLink(){
        return new NativeLink( $this->options );
    }

    public function Notify(){
        return new Notify( $this->options );
    }

    public function OrderQuery(){
        return new OrderQuery( $this->options );
    }

    public function ShortUrl(){
        return new ShortUrl( $this->options );
    }






}