<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/4/6
 * Time: 11:54
 */

namespace mikkle\tp_wxpay\src;


use mikkle\tp_master\Exception;
use mikkle\tp_wxpay\base\Tools;
use mikkle\tp_wxpay\base\WxpayClientBase;

class OrderQuery extends WxpayClientBase
{
    protected $url = "https://api.mch.weixin.qq.com/pay/orderquery";

    protected function checkParams()
    {
        if($this->params["out_trade_no"] == null &&
            $this->params["transaction_id"] == null)
        {
            throw new Exception("订单查询接口中，out_trade_no、transaction_id至少填一个！"."<br>");
        }
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


}