<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/4/6
 * Time: 10:27
 */

namespace mikkle\tp_wxpay\src;


use mikkle\tp_master\Exception;
use mikkle\tp_wxpay\base\Tools;
use mikkle\tp_wxpay\base\WxpayClientBase;
use mikkle\tp_wxpay\config\WxpayValue;

class DownloadBill extends WxpayClientBase
{
    //设置接口链接
    protected $url = "https://api.mch.weixin.qq.com/pay/downloadbill";
    protected function checkParams()
    {
        if($this->params["bill_date"] == null )
        {
            throw new Exception("对账单接口中，缺少必填参数bill_date！"."<br>");
        }
    }


    public function setBillDate($billDate,$billType = "ALL"){
        if (!in_array( $billType,[WxpayValue::$billType_ALL,WxpayValue::$billType_SUCCESS,WxpayValue::$billType_REFUND,WxpayValue::$billType_RECHARGE_REFUND,])){
            throw new Exception("对账单接口中，参数bill_type错误！"."<br>");
        }
        $this->setParam([
            "bill_date"=>$billDate,
            "bill_type"=>$billType,
        ]);
        return $this;
    }

    /**
     * 	作用：获取结果，默认不使用证书
     */
    function getBillResult()
    {
        return $this->postXml() ;
    }
}