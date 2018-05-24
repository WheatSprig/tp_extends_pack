<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/4/4
 * Time: 16:40
 */

namespace mikkle\tp_wxpay\src;


use mikkle\tp_wxpay\base\Tools;
use mikkle\tp_wxpay\base\WxpayClientBase;

class JsApi extends WxpayClientBase
{
    protected $openid;
    public function checkParams()
    {


    }

    /**
     * 作用：生成可以获得code的url
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $redirectUrl
     * @return string
     */
    function createOauthUrlForCode($redirectUrl)
    {
        $urlObj["appid"] = $this->appid;
        $urlObj["redirect_uri"] = $redirectUrl;
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_base";
        $urlObj["state"] = "STATE"."#wechat_redirect";
        $bizString = Tools::formatBizQueryParaMap($urlObj, false);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?".$bizString;
    }

    /*
     * 	作用：通过curl向微信提交code，以获取openid
     */
    function getOpenidByCode($code)
    {
        $result= Tools::postCurl( $this->createOauthUrlForOpenid($code) ,[]) ;
        $data = json_decode($result,true);
        $this->openid = $data['openid'];
        return $this->openid;
    }

    /*
     * 	作用：生成可以获得openid的url
     */
    function createOauthUrlForOpenid($code)
    {
        $urlObj["appid"] = $this->appid;
        $urlObj["secret"] = $this->secret;
        $urlObj["code"] = $code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = Tools::formatBizQueryParaMap($urlObj, false);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?".$bizString;
    }

    /**
     * title 通过统一订单号 获取Jsapi支付参数
     * description getJsPayParamsByPrepayId
     * User: Mikkle
     * QQ:776329498
     * @param $prepayId
     * @return string
     */
    public function getJsPayParamsByPrepayId($prepayId)
    {
        $jsApiObj["appId"] = config('wechat_appid');
        $timeStamp = time();
        $jsApiObj["timeStamp"] = "$timeStamp";
        $jsApiObj["nonceStr"] = Tools::createNoncestr();
        $jsApiObj["package"] = "prepay_id={$prepayId}";
        $jsApiObj["signType"] = "MD5";
        $jsApiObj["paySign"] = Tools::getSignByKey($jsApiObj,$this->key);
        return  json_encode($jsApiObj);
    }


}