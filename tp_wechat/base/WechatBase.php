<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/7/24
 * Time: 9:07
 */

namespace mikkle\tp_wechat\base;


use mikkle\tp_master\Cache;
use mikkle\tp_master\Config;
use mikkle\tp_wechat\support\Curl;
use mikkle\tp_master\Log;
use mikkle\tp_wechat\support\StaticFunction;

class WechatBase
{
    protected $appId;
    protected $secret;
    protected $error = [];

    protected $prefix = 'mikkle.wechat.access_token.';
    protected $cacheKey;
    protected $cache;
    protected $access_token;
    protected $retry=false;
    public $errCode = 0;
    public $errMsg = "";
    const API_TOKEN_GET = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&';
    const API_BASE_URL_PREFIX = 'https://api.weixin.qq.com';
    const API_URL_PREFIX = 'https://api.weixin.qq.com/cgi-bin';
    const GET_TICKET_URL = '/ticket/getticket?';
    const AUTH_URL = '/token?grant_type=client_credential&';

    public function __construct($options=[])
    {
        if (empty($options)&& !empty( Config::get("wechat.default_options_name"))){
            $options = Config::get("wechat.default_options_name");
        }
        $this->appId = $options["appid"];
        $this->secret = $options["appsecret"];
        $this->cacheKey = $this->prefix.$options["appid"];

    }


    /**
     * 获取getToken
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param bool|false $isRefresh
     * @return bool|mixed
     */
    public function getToken($isRefresh=false){
        $cache_key = $this->getCacheKey();
        $cache_token = $this->getCache()->get($cache_key);
        if ( empty($cache_token) || $isRefresh ) {
            $result = $this->getWechatToken();
            $expire = isset($result['expires_in']) ? intval($result['expires_in'])-1000 : 7000;
            $cache_token = $result["access_token"];
            $this->getCache()->set($cache_key,$cache_token,$expire);
        }
        if (!$cache_token){
            return false;
        }
        $this->access_token=$cache_token;
        return $cache_token;
    }

    protected function getWechatToken(){
        if(empty($this->appId)||empty($this->secret)){
            $this->error[]="参数丢失";
            return false;
        }
        $url_getToken = self::API_TOKEN_GET."appid={$this->appId}&secret={$this->secret}";
        $result = StaticFunction::parseJSON( Curl::curlGet($url_getToken) );
            if (!$result || isset($result['errcode'])) {
                Log::error("请求数据接口出错:code[{$result['errcode']}],{$result['errmsg']}");
                $this->error[] = $result['errmsg'];
                return false;
            }
        return $result;
    }

    public function getCache()
    {
        if(!$this->cache){
            $this->cache = new Cache();
        }
        return   $this->cache ;
    }

    public function getAppId()
    {
        return $this->appId;
    }

    public function getSecret()
    {
        return $this->secret;
    }
    protected function getCacheKey(){
        if (is_null($this->cacheKey)) {
            return $this->prefix.$this->appId;
        }

        return $this->cacheKey;
    }

    protected function returnGetResult($url,$name,$arguments){
        $result = Curl::curlGet($url) ;
        return $this->resultJsonWithRetry($result,$name,$arguments);
    }

    protected function returnPostResult($url,$data,$name,$arguments){
        $result = Curl::curlPost($url,$data) ;
        return $this->resultJsonWithRetry($result,$name,$arguments);
    }

    protected function resultJsonWithRetry($result,$name,$arguments){

        if ($result) {
            $json = StaticFunction::parseJSON($result);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry($name,$arguments);
            }
            return $json;
        }
        return false;

    }

    protected function resultBoolWithRetry($result,$name,$arguments){

        if ($result) {
            $json = StaticFunction::parseJSON($result);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry($name,$arguments);
            }
            return true;
        }
        return false;
    }


    /**
     * 接口失败重试
     * @param $method   SDK方法名称
     * @param array $arguments SDK方法参数
     * @return bool|mixed
     */
    protected function checkRetry($method, $arguments = array())
    {
        if (!$this->retry && in_array($this->errCode, ['40014', '40001', '41001', '42001'])) {
            Log::notice("Run {$method} Faild. {$this->errMsg}[{$this->errCode}]");
            ($this->retry = true) && $this->getToken(true);
            $this->errCode = 40001;
            $this->errMsg = 'no access';
            Log::notice("Retry Run {$method} ...");
            return call_user_func_array(array($this, $method), $arguments);
        }
        return false;
    }


    public function __destruct()
    {
        // TODO: Implement __destruct() method.
        if($this->error){
            Log::error($this->error);
        }
    }
}