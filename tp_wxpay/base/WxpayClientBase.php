<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/4/4
 * Time: 9:19
 */

namespace mikkle\tp_wxpay\base;


use mikkle\tp_master\Exception;
use mikkle\tp_master\Log;

abstract class WxpayClientBase
{
     //请求地址
     protected $url;
     //参数
     protected $params;
     protected $paramList;

     protected $options;
     protected $appid;
     protected $secret;
     protected $mchId;
     protected $key;
     protected $certPath;
     protected $keyPath;
     //微信返回值
     protected $response;

     protected $result;
     protected $error;
     protected $curlTimeout=30;

     public function __construct($options)
     {
         $this->setOptions($options);
         $this->_initialize();
     }
    public function _initialize()
    {

    }

     protected function setOptions($options){
             if (isset($options["appid"]) &&isset($options["mch_id"]) &&isset($options["key"]) ){
                 $this->options=empty($this->options)? $options :  array_merge($this->options,$options);
                 $this->appid=$options["appid"];
                 @$this->secret=$options["secret"];
                 $this->mchId=$options["mch_id"];
                 $this->key=$options["key"];
             }else{
                 throw  new  Exception("缺失重要的参数对象");
             }
             if (isset($options["cert_path"])&&isset($options["key_path"])){
                 $this->certPath=$options["cert_path"];
                 $this->keyPath=$options["key_path"];
             }
             if (empty($this->options)){
                 throw  new  Exception("参数缺失");
             }
     }

    /**
     * title 作用：设置请求参数 支持数组批量设置
     * description setParam
     * User: Mikkle
     * QQ:776329498
     * @param $param
     * @param string $paramValue
     * @return $this
     */
    function setParam($param, $paramValue="")
    {
        switch (true){
            case(is_string($param) &&( is_string($paramValue)||is_numeric($paramValue)) ):
                $this->params[Tools::trimString($param)] = Tools::trimString($paramValue);
                break;
            case (is_array( $param) && empty( $paramValue)):
                foreach ($param as $item=>$value){
                    if (is_string($item) && ( is_string($value)||is_numeric($value))){
                        $this->params[Tools::trimString($item)] = Tools::trimString($value);
                    }
                }
                break;
            default:
        }
        return $this;
    }
    /*
     * 	作用：检查参数是否完整
     */
    abstract protected function checkParams();
    /*
     * 	作用：设置标配的请求参数，生成签名，生成接口参数xml
     */
    function createXml()
    {
        $this->checkParams();
        $this->params["appid"] = $this->options["appid"];                  //config('wechat_appid');//公众账号ID
        $this->params["mch_id"] =   $this->options["mch_id"];           //config('wechat_mchid');//商户号
        $this->params["nonce_str"] = Tools::createNonceStr();//随机字符串
        $this->params["sign"] = Tools::getSignByKey($this->params,$this->options["key"]);//签名
        return  Tools::arrayToXml($this->params);
    }

    /**
     * 	作用：post请求xml
     */
    public function postXml()
    {
        $xml = $this->createXml();
        $this->response = Tools::postXmlCurl($xml,$this->url);
        return $this->response;
    }

    /**
     * 	作用：使用证书post请求xml
     */
    public function postXmlSSL()
    {
        if (isset($this->options["cert_path"])&&isset($this->options["key_path"])){
            $certPath=$this->options["cert_path"];
            $keyPath =$this->options["key_path"];
        }else{
            throw  new  Exception("缺失证书相关参数");
        }
        $xml = $this->createXml();
        $this->response = Tools::postXmlSSLCurl($xml,$this->url,$certPath,$keyPath,$this->curlTimeout);
        return $this->response;
    }


    /**
     * 	作用：获取结果，默认不使用证书
     */
    function getResult()
    {
        $this->postXml();
        $this->result = Tools::xmlToArray($this->response);
        return $this->result;
    }

    public function getResponse(){
        return $this->response;
    }


}