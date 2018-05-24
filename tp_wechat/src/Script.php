<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/9/2
 * Time: 10:56
 */

namespace mikkle\tp_wechat\src;


use mikkle\tp_wechat\base\WechatBase;
use mikkle\tp_master\Cache;
use mikkle\tp_wechat\support\Curl;
use mikkle\tp_wechat\support\StaticFunction;

class Script extends WechatBase
{
    public $jsapi_ticket;

    public $jsapi_ticket_cache;
    public function  __construct(array $option)
    {
        parent::__construct($option);
        $this->getToken();
        $this->jsapi_ticket_cache = "mikkle_wechat_jsapi_ticket_{$this->appId}";
    }
    /**
     * JSAPI授权TICKET
     * @var string
     */


    /**
     * 删除JSAPI授权TICKET
     * @param string $appid
     * @return bool
     */
    public function resetJsTicket($appid = '')
    {
        $this->jsapi_ticket = '';
        Cache::rm($this->jsapi_ticket_cache);
        return true;
    }

    /**
     * 获取JSAPI授权TICKET
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return bool|mixed
     */
    public function getJsTicket()
    {
        if (!$this->access_token || !$this->appId){
            return false;
        }


        # 尝试从缓存中读取
        $jsapi_ticket = $this->getCache()->get($this->jsapi_ticket_cache);
        if ($jsapi_ticket) {
            return $this->jsapi_ticket = $jsapi_ticket;
        }

        # 调接口获取
        $result = Curl::curlGet(self::API_URL_PREFIX . self::GET_TICKET_URL . "access_token={$this->access_token}" . '&type=jsapi');
        if ($result) {
            $json = StaticFunction::parseJSON($result);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            $this->jsapi_ticket = $json['ticket'];
            $this->getCache()->set($this->jsapi_ticket_cache, $this->jsapi_ticket, $json['expires_in'] ? intval($json['expires_in']) - 100 : 3600);
            return $this->jsapi_ticket;
        }
        return false;
    }

    /**
     * 获取JsApi使用签名
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $url
     * @return array|bool
     */
    public function getJsSign($url)
    {
        if(empty($url))        return false;
        if (!$this->jsapi_ticket) {
            $this->getJsTicket();
            if (!$this->jsapi_ticket) return false;
        }
        $data = [
            "jsapi_ticket" => $this->jsapi_ticket,
            "timestamp"    =>  time() ,
            "noncestr"     => ''. StaticFunction::createRandStr(16) ,
            "url"          => trim($url),
        ];
        return [
            "url"       => $url,
            'debug'     => false,
            "appId"     => $this->appId,
            "nonceStr"  => $data['noncestr'],
            "timestamp" => $data['timestamp'],
            "signature" => StaticFunction::getSignature($data, 'sha1'),
            'jsApiList' => [
                'onMenuShareTimeline', 'onMenuShareAppMessage', 'onMenuShareQQ', 'onMenuShareWeibo', 'onMenuShareQZone',
                'hideOptionMenu', 'showOptionMenu', 'hideMenuItems', 'showMenuItems', 'hideAllNonBaseMenuItem', 'showAllNonBaseMenuItem',
                'chooseImage', 'previewImage', 'uploadImage', 'downloadImage', 'closeWindow', 'scanQRCode', 'chooseWXPay',
                'translateVoice', 'getNetworkType', 'openLocation', 'getLocation',
                'openProductSpecificView', 'addCard', 'chooseCard', 'openCard',
                'startRecord', 'stopRecord', 'onVoiceRecordEnd', 'playVoice', 'pauseVoice', 'stopVoice', 'onVoicePlayEnd', 'uploadVoice', 'downloadVoice',
                'openWXDeviceLib', 'closeWXDeviceLib', 'getWXDeviceInfos', 'sendDataToWXDevice', 'disconnectWXDevice', 'getWXDeviceTicket', 'connectWXDevice',
                'startScanWXDevice', 'stopScanWXDevice', 'onWXDeviceBindStateChange', 'onScanWXDeviceResult', 'onReceiveDataFromWXDevice',
                'onWXDeviceBluetoothStateChange', 'onWXDeviceStateChange'
            ]
        ];
    }


}