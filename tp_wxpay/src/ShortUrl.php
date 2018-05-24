<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/4/6
 * Time: 11:59
 */

namespace mikkle\tp_wxpay\src;


use mikkle\tp_master\Exception;
use mikkle\tp_wxpay\base\WxpayClientBase;

class ShortUrl extends WxpayClientBase
{
    protected $url = "https://api.mch.weixin.qq.com/tools/shorturl";

    protected function checkParams()
    {
        if($this->params["long_url"] == null )
        {
            throw new Exception("短链接转换接口中，缺少必填参数long_url！"."<br>");
        }
    }


    /**
     * 获取prepay_id
     */
    function getShortUrl()
    {
        $this->result = $this->postXml();
        return  isset($this->result["short_url"])?$this->result["short_url"] :false ;
    }

}