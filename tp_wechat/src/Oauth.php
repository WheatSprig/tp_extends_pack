<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/9/11
 * Time: 16:13
 */

namespace mikkle\tp_wechat\src;


use mikkle\tp_wechat\base\WechatBase;
use mikkle\tp_wechat\support\Curl;
use mikkle\tp_master\Log;
use mikkle\tp_wechat\support\StaticFunction;

class Oauth extends WechatBase
{

    const OAUTH_PREFIX = 'https://open.weixin.qq.com/connect/oauth2';
    const OAUTH_AUTHORIZE_URL = '/authorize?';
    const OAUTH_TOKEN_URL = '/sns/oauth2/access_token?';
    const OAUTH_REFRESH_URL = '/sns/oauth2/refresh_token?';
    const OAUTH_USERINFO_URL = '/sns/userinfo?';
    const OAUTH_AUTH_URL = '/sns/auth?';
    /* 获取粉丝信息 */
    const USER_INFO_URL = '/user/info?';

    /**
     * Oauth 授权跳转接口
     * @param string $callback 授权回跳地址
     * @param string $state 为重定向后会带上state参数（填写a-zA-Z0-9的参数值，最多128字节）
     * @param string $scope 授权类类型(可选值snsapi_base|snsapi_userinfo)
     * @return string
     */
    public function getOauthRedirect($callback, $state = '', $scope = 'snsapi_base')
    {
        $redirect_uri = urlencode($callback);
        return self::OAUTH_PREFIX . self::OAUTH_AUTHORIZE_URL . "appid={$this->appId}&redirect_uri={$redirect_uri}&response_type=code&scope={$scope}&state={$state}#wechat_redirect";
    }

    /**
     * 通过 code 获取 AccessToken 和 openid
     * @return bool|array
     */
    public function getOauthAccessToken()
    {
        $code = isset($_GET['code']) ? $_GET['code'] : '';
        if (empty($code)) {
            Log::notice("getOauthAccessToken Fail, Because there is no access to the code value in get.");
            return false;
        }
        $result = Curl::curlGet(self::API_BASE_URL_PREFIX . self::OAUTH_TOKEN_URL . "appid={$this->appId}&secret={$this->secret}&code={$code}&grant_type=authorization_code");
        if ($result) {
            $json = StaticFunction::parseJSON($result);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                Log::notice("WechatOauth::getOauthAccessToken Fail.{$this->errMsg} [{$this->errCode}]");
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 刷新access token并续期
     * @param string $refresh_token
     * @return bool|array
     */
    public function getOauthRefreshToken($refresh_token)
    {
        $result = Curl::curlGet(self::API_BASE_URL_PREFIX . self::OAUTH_REFRESH_URL . "appid={$this->appId}&grant_type=refresh_token&refresh_token={$refresh_token}");
        if ($result) {
            $json = StaticFunction::parseJSON($result);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                Log::notice("WechatOauth::getOauthRefreshToken Fail.{$this->errMsg} [{$this->errCode}]");
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 获取授权后的用户资料
     * @param string $access_token
     * @param string $openid
     * @return bool|array {openid,nickname,sex,province,city,country,headimgurl,privilege,[unionid]}
     * 注意：unionid字段 只有在用户将公众号绑定到微信开放平台账号后，才会出现。建议调用前用isset()检测一下
     */
    public function getOauthUserInfo($access_token, $openid)
    {
        $result = Curl::curlGet(self::API_BASE_URL_PREFIX . self::OAUTH_USERINFO_URL . "access_token={$access_token}&openid={$openid}");
        if ($result) {
            $json = StaticFunction::parseJSON($result);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                Log::notice("WechatOauth::getOauthUserInfo Fail.{$this->errMsg} [{$this->errCode}]");
                return false;
            }
            return $json;
        }
        return false;
    }

    /**
     * 检验授权凭证是否有效
     * @param string $access_token
     * @param string $openid
     * @return bool 是否有效
     */
    public function getOauthAuth($access_token, $openid)
    {
        $result = Curl::curlGet(self::API_BASE_URL_PREFIX . self::OAUTH_AUTH_URL . "access_token={$access_token}&openid={$openid}");
        if ($result) {
            $json = StaticFunction::parseJSON($result);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                Log::notice("WechatOauth::getOauthAuth Fail.{$this->errMsg} [{$this->errCode}]");
                return false;
            } else if ($json['errcode'] == 0) {
                return true;
            }
        }
        return false;
    }

    /**
     * 获取关注者详细信息
     * @param string $openid
     * @return bool|array {subscribe,openid,nickname,sex,city,province,country,language,headimgurl,subscribe_time,[unionid]}
     * @注意：unionid字段 只有在粉丝将公众号绑定到微信开放平台账号后，才会出现。建议调用前用isset()检测一下
     */
    public function getUserInfo($openid)
    {
        $this->getToken();
        if (!$this->access_token || empty($openid)){
            return false;
        }
        $curl_url = self::API_URL_PREFIX .  self::USER_INFO_URL . "access_token={$this->access_token}&openid={$openid}";
        $result = Curl::curlGet($curl_url) ;
       return $this->resultJsonWithRetry($result,__FUNCTION__, func_get_args());
    }

}