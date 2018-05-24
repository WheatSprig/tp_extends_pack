<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/9/11
 * Time: 11:48
 */

namespace mikkle\tp_wechat\src;


use mikkle\tp_wechat\base\WechatBase;
use mikkle\tp_wechat\support\Curl;
use mikkle\tp_wechat\support\StaticFunction;

class Hardware extends WechatBase
{
    const DEVICE_AUTHORIZE_DEVICE = '/device/authorize_device?'; //设备设全
    const DEVICE_GETQRCODE = '/device/getqrcode?';               //设备授权新接口
    const DEVICE_CREATE_QRCODE = '/device/create_qrcode?';       //获取设备二维码
    const DEVICE_GET_STAT = '/device/get_stat?';                 //获取设备状态
    const DEVICE_TRANSMSG = '/device/transmsg?';                 //主动发送消息给设备
    const DEVICE_COMPEL_UNBINDHTTPS = '/device/compel_unbind?';  //强制解绑用户和设备


    public function  __construct(array $option)
    {
        parent::__construct($option);
        $this->getToken();
    }

    /**
     * 强制解绑用户和设备
     * @param $data
     * @return bool|mixed
     */
    public function deviceCompelUnbindhttps($data)
    {
        if (!$this->access_token || empty($data)) {
            return false;
        }

        $curl_url = self::API_BASE_URL_PREFIX . self::DEVICE_COMPEL_UNBINDHTTPS ."access_token={$this->access_token}";
        $result = Curl::curlPost($curl_url,$data) ;
        if ($result) {
            $json = StaticFunction::parseJSON($result);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }


    public function transmsg($data)
    {
        if (!$this->access_token || empty($data)) {
            return false;
        }

        $curl_url = self::API_BASE_URL_PREFIX . self::DEVICE_TRANSMSG ."access_token={$this->access_token}";
        $result = Curl::curlPost($curl_url,$data) ;
        if ($result) {
            $json = StaticFunction::parseJSON($result);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

    public function getQrcode($product_id)
    {
        if (!$this->access_token || empty($product_id)) {
            return false;
        }

        $curl_url = self::API_BASE_URL_PREFIX . self::DEVICE_GETQRCODE ."access_token={$this->access_token}&product_id=$product_id";
        $result = Curl::curlGet($curl_url) ;

        if ($result) {
            $json = StaticFunction::parseJSON($result);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

    /**
     * 设备授权
     * @param $data
     * @return bool|mixed
     */
    public function deviceAuthorize($data)
    {
        if (!$this->access_token || empty($data)) {
            return false;
        }

        $curl_url = self::API_BASE_URL_PREFIX . self::DEVICE_AUTHORIZE_DEVICE ."access_token={$this->access_token}";
        $result = Curl::curlPost($curl_url,$data) ;
        if ($result) {
            $json = StaticFunction::parseJSON($result);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

    /**
     * 获取设备二维码
     * @param $data
     * @return bool|mixed
     */
    public function getDeviceQrcode($data)
    {
        if (!$this->access_token || empty($data)) {
            return false;
        }

        $curl_url = self::API_BASE_URL_PREFIX . self::DEVICE_CREATE_QRCODE ."access_token={$this->access_token}";
        $result = Curl::curlPost($curl_url,$data) ;

        if ($result) {
            $json = StaticFunction::parseJSON($result);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

    /**
     * 获取设备状态
     * @param $device_id
     * @return bool|mixed
     */
    public function getDeviceStat($device_id)
    {
        if (!$this->access_token|| empty($device_id)) {
            return false;
        }

        $curl_url = self::API_BASE_URL_PREFIX . self::DEVICE_GET_STAT ."access_token={$this->access_token}&device_id=$device_id";
        $result = Curl::curlGet($curl_url) ;

        if ($result) {
            $json = StaticFunction::parseJSON($result);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

}