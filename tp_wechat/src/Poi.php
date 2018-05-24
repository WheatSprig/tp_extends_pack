<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/9/9
 * Time: 14:05
 */

namespace mikkle\tp_wechat\src;


use mikkle\tp_wechat\base\WechatBase;
use mikkle\tp_wechat\support\Curl;
use mikkle\tp_wechat\support\StaticFunction;

class Poi extends WechatBase
{
    /** 创建门店 */
    const POI_ADD = '/cgi-bin/poi/addpoi?';

    /** 查询门店信息 */
    const POI_GET = '/cgi-bin/poi/getpoi?';

    /** 获取门店列表 */
    const POI_GET_LIST = '/cgi-bin/poi/getpoilist?';

    /** 修改门店信息 */
    const POI_UPDATE = '/cgi-bin/poi/updatepoi?';

    /** 删除门店 */
    const POI_DELETE = '/cgi-bin/poi/delpoi?';

    /** 获取门店类目表 */
    const POI_CATEGORY = '/cgi-bin/poi/getwxcategory?';

    public function  __construct($option)
    {
        parent::__construct($option);
        $this->getToken();
    }


    /**
     * 创建门店
     * @link https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1444378120&token=&lang=zh_CN
     * @param array $data
     * @return bool
     */
    public function addPoi($data)
    {
        if (!$this->access_token || empty($data)) {
            return false;
        }

        $curl_url = self::API_URL_PREFIX . self::POI_ADD ."access_token={$this->access_token}";
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
     * 删除门店
     * @link https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1444378120&token=&lang=zh_CN
     * @param string $poi_id JSON数据格式
     * @return bool|array
     */
    public function delPoi($poi_id)
    {
        if (!$this->access_token || empty($poi_id)) {
            return false;
        }
        $data = ['poi_id' => $poi_id ];

        $curl_url = self::API_URL_PREFIX . self::POI_DELETE ."access_token={$this->access_token}";
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
     * 修改门店服务信息
     * @link https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1444378120&token=&lang=zh_CN
     * @param array $data
     * @return bool
     */
    public function updatePoi($data)
    {
        if (!$this->access_token || empty($data)) {
            return false;
        }

        $curl_url = self::API_URL_PREFIX . self::POI_UPDATE ."access_token={$this->access_token}";
        $result = Curl::curlPost($curl_url,$data) ;

        if ($result) {
            $json = StaticFunction::parseJSON($result);
            if (!$json || !empty($json['errcode'])) {
                $this->errCode = $json['errcode'];
                $this->errMsg = $json['errmsg'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return true;
        }
        return false;
    }

    /**
     * 查询门店信息
     * @link https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1444378120&token=&lang=zh_CN
     * @param string $poi_id
     * @return bool
     */
    public function getPoi($poi_id)
    {
        if (!$this->access_token || empty($poi_id)) {
            return false;
        }
        $data = ['poi_id' => $poi_id];
        $curl_url = self::API_URL_PREFIX . self::POI_GET ."access_token={$this->access_token}";
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
     * 查询门店列表
     * @link https://mp.weixin.qq.com/wiki?t=resource/res_main&id=mp1444378120&token=&lang=zh_CN
     * @param int $begin 开始位置，0 即为从第一条开始查询
     * @param int $limit 返回数据条数，最大允许50，默认为20
     * @return bool|array
     */
    public function getPoiList($begin = 0, $limit = 50)
    {
        if (!$this->access_token) {
            return false;
        }
        $limit > 50 && $limit = 50;
        $data = ['begin' => $begin, 'limit' => $limit];

        $curl_url = self::API_URL_PREFIX . self::POI_GET_LIST ."access_token={$this->access_token}";
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
     * 获取商家门店类目表
     * @return bool|string
     */
    public function getCategory()
    {
        if (!$this->access_token ) {
            return false;
        }

        $curl_url = self::API_URL_PREFIX . self::POI_CATEGORY ."access_token={$this->access_token}";
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