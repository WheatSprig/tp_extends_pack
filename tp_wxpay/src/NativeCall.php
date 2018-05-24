<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/4/4
 * Time: 18:01
 */

namespace mikkle\tp_wxpay\src;


use mikkle\tp_wxpay\base\Tools;
use mikkle\tp_wxpay\base\WxpayServerBase;
/**
 * 请求商家获取商品信息接口
 * 获取产品后使用returnParams返回传输数据
 */
class NativeCall extends WxpayServerBase
{
    /**
     * 生成接口参数xml
     */
    protected function createXml()
    {
        if($this->returnParams["return_code"] == "SUCCESS"){
            $this->returnParams["appid"] = $this->appid;//公众账号ID
            $this->returnParams["mch_id"] = $this->mchId;//商户号
            $this->returnParams["nonce_str"] = Tools::createNoncestr();//随机字符串
            $this->returnParams["sign"] = Tools::getSignByKey($this->returnParams,$this->key);//签名
        }
        return Tools::arrayToXml($this->returnParams);
    }

    /**
     * 获取product_id
     */
    function getProductId()
    {
        $product_id = $this->data["product_id"];
        return $product_id;
    }
}