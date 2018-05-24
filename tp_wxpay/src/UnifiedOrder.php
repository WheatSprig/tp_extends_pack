<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/4/4
 * Time: 12:23
 */

namespace mikkle\tp_wxpay\src;


use mikkle\tp_master\Exception;
use mikkle\tp_wxpay\base\Tools;
use mikkle\tp_wxpay\base\WxpayClientBase;

class UnifiedOrder extends WxpayClientBase
{

    protected $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
    public function _initialize()
    {
        $this->params["spbill_create_ip"] =Tools::getRealIp();//终端ip

    }

    protected function checkParams()
    {
        //检测必填参数
        if ($this->params["out_trade_no"] == null) {
            throw new Exception("缺少统一支付接口必填参数out_trade_no！" . "<br>");
        } elseif ($this->params["body"] == null) {
            throw new Exception("缺少统一支付接口必填参数body！" . "<br>");
        } elseif ($this->params["total_fee"] == null) {
            throw new Exception("缺少统一支付接口必填参数total_fee！" . "<br>");
        } elseif ($this->params["notify_url"] == null) {
            throw new Exception("缺少统一支付接口必填参数notify_url！" . "<br>");
        } elseif ($this->params["trade_type"] == null) {
            throw new Exception("缺少统一支付接口必填参数trade_type！" . "<br>");
        } elseif ($this->params["trade_type"] == "JSAPI" &&
            $this->params["openid"] == NULL) {
            throw new Exception("统一支付接口中，缺少必填参数openid！trade_type为JSAPI时，openid为必填参数！" . "<br>");
        } elseif ($this->params["trade_type"] == "NATIVE" &&
            $this->params["product_id"] == null ) {
            throw new Exception("统一支付接口中，缺少必填参数 product_id！trade_type为NATIVE时，product_id 为必填参数！" . "<br>");
        }
    }

    /**
     * 获取prepay_id
     */
    public function getPrepayId()
    {
        $this->postXml();
        $this->result = Tools::xmlToArray($this->response);
        $prepay_id = isset($this->result["prepay_id"]) ? $this->result["prepay_id"] : false ;
        return $prepay_id;
    }

    public function getPayUrl(){
        $this->postXml();
        $this->result = Tools::xmlToArray($this->response);
        if (isset( $this->result["trade_type"] ) && $this->result["trade_type"] == "MWEB"){
            return isset($this->result["mweb_url"]) ? $this->result["mweb_url"] : false ;
        }
        if ( isset( $this->result["trade_type"] ) && $this->result["trade_type"] == "NATIVE"){
            return isset($this->result["code_url"]) ? $this->result["code_url"] : false ;
        }
        return false;
    }

}