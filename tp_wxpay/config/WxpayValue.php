<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/4/4
 * Time: 15:22
 */

namespace mikkle\tp_wxpay\config;


class WxpayValue
{
    static public $tradeType_MWEB = "MWEB";
    static public $tradeType_JSAPI = "JSAPI";
    static public $billType_ALL= "ALL";
    static public $billType_SUCCESS= "SUCCESS"; //返回成功订单
    static public $billType_REFUND= "REFUND";  //返回当日退款订单
    static public $billType_RECHARGE_REFUND= "RECHARGE_REFUND";  //当日充值退款订单
}