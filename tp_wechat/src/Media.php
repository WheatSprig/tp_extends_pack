<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/9/11
 * Time: 16:57
 */

namespace mikkle\tp_wechat\src;


use mikkle\tp_wechat\base\WechatBase;
use mikkle\tp_wechat\support\Curl;
use mikkle\tp_wechat\support\StaticFunction;

class Media extends WechatBase
{


    const UPLOAD_MEDIA_URL = 'http://file.api.weixin.qq.com/cgi-bin';
    const MEDIA_UPLOAD_URL = '/media/upload?';
    const MEDIA_UPLOADIMG_URL = '/media/uploadimg?'; //图片上传接口
    const MEDIA_GET_URL = '/media/get?';
    const MEDIA_VIDEO_UPLOAD = '/media/uploadvideo?';
    const MEDIA_FOREVER_UPLOAD_URL = '/material/add_material?';
    const MEDIA_FOREVER_NEWS_UPLOAD_URL = '/material/add_news?';
    const MEDIA_FOREVER_NEWS_UPDATE_URL = '/material/update_news?';
    const MEDIA_FOREVER_GET_URL = '/material/get_material?';
    const MEDIA_FOREVER_DEL_URL = '/material/del_material?';
    const MEDIA_FOREVER_COUNT_URL = '/material/get_materialcount?';
    const MEDIA_FOREVER_BATCHGET_URL = '/material/batchget_material?';
    const MEDIA_UPLOADNEWS_URL = '/media/uploadnews?';


    public function  __construct($option)
    {
        parent::__construct($option);
        $this->getToken();
    }
    /**
     * 上传临时素材，有效期为3天(认证后的订阅号可用)
     * 注意：上传大文件时可能需要先调用 set_time_limit(0) 避免超时
     * 注意：临时素材的media_id是可复用的！
     * @param array $file_url
     * @param string $type 类型：图片:image 语音:voice 视频:video 缩略图:thumb
     * @return bool|array
     */
    public function uploadMedia( $file_url, $type)
    {
        if (!$this->access_token || empty($file_url) || !in_array($type,["image","voice","video","thumb"])) {
            return false;
        }
        $data['media'] = Curl::getCurlFileMedia($file_url);
        $curl_url = self::API_URL_PREFIX . self::MEDIA_UPLOAD_URL . "access_token={$this->access_token}&type={$type}";
        $result = Curl::CurlFile($curl_url, $data);
        //dump($result);
        return $this->resultJsonWithRetry($result,__FUNCTION__, func_get_args());
    }

    /**
     * 获取临时素材(认证后的订阅号可用)
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $media_id
     * @param bool|false $is_video
     * @return bool|mixed
     */
    public function getMedia($media_id, $is_video = false)
    {
        if (!$this->access_token || empty($media_id)) {
            return false;
        }
        // $media_id iZyXlaEO3G95GwCc8hZR6_I8CJbLIMaMB__r9flRPbH5X_R9xcaaEeL29l64GHF4
        //原先的上传多媒体文件接口使用 self::UPLOAD_MEDIA_URL 前缀
        //如果要获取的素材是视频文件时，不能使用https协议，必须更换成http协议
        $url_prefix = $is_video ? str_replace('https', 'http', self::API_URL_PREFIX) : self::API_URL_PREFIX;

        $curl_url = $url_prefix . self::MEDIA_GET_URL . "access_token={$this->access_token}". '&media_id=' . $media_id;
        $result = Curl::curlGet($curl_url) ;
        return $result;
    }

    /**
     * 获取临时素材(认证后的订阅号可用) 包含返回的http头信息
     * @param string $media_id 媒体文件id
     * @param bool $is_video 是否为视频文件，默认为否
     * @return bool|array
     */
    public function getMediaWithHttpInfo($media_id, $is_video = false)
    {
        if (!$this->access_token ||empty($media_id)) {
            return false;
        }
        //原先的上传多媒体文件接口使用 self::UPLOAD_MEDIA_URL 前缀
        //如果要获取的素材是视频文件时，不能使用https协议，必须更换成http协议
        $url_prefix = $is_video ? str_replace('https', 'http', self::API_URL_PREFIX) : self::API_URL_PREFIX;
        $url = $url_prefix . self::MEDIA_GET_URL . "access_token={$this->access_token}" . '&media_id=' . $media_id;
        $oCurl = curl_init();
        if (stripos($url, "https://") !== false) {
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($oCurl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($oCurl, CURLOPT_SSLVERSION, 1);
        }
        curl_setopt($oCurl, CURLOPT_URL, $url);
        curl_setopt($oCurl, CURLOPT_RETURNTRANSFER, 1);
        $sContent = curl_exec($oCurl);
        $aStatus = curl_getinfo($oCurl);

        $result = [];

        if (intval($aStatus["http_code"]) !== 200) {
            return false;
        }

        if ($sContent) {
            if (is_string($sContent)) {
                $json = json_decode($sContent, true);
                if (isset($json['errcode'])) {
                    $this->errCode = $json['errcode'];
                    $this->errMsg = $json['errmsg'];
                    return $this->checkRetry(__FUNCTION__, func_get_args());
                }
            }
            $result['content'] = $sContent;
            $result['info'] = $aStatus;
            return $result;
        }
        return false;
    }

    /**
     * 上传图片，本接口所上传的图片不占用公众号的素材库中图片数量的5000个的限制。图片仅支持jpg/png格式，大小必须在1MB以下。 (认证后的订阅号可用)
     * 注意：上传大文件时可能需要先调用 set_time_limit(0) 避免超时
     * 注意：数组的键值任意，但文件名前必须加@，使用单引号以避免本地路径斜杠被转义
     * @param array $file_url
     * @return bool|array
     */
    public function uploadImg($file_url)
    {
        if (!$this->access_token || empty($file_url)) {
            return false;
        }
            $data['media'] = Curl::getCurlFileMedia($file_url);
        $curl_url = self::API_URL_PREFIX . self::MEDIA_UPLOADIMG_URL . "access_token={$this->access_token}";
        $result = Curl::CurlFile($curl_url, $data);
        return $this->resultJsonWithRetry($result,__FUNCTION__, func_get_args());
    }

    /**
     * 上传永久素材(认证后的订阅号可用)
     * 新增的永久素材也可以在公众平台官网素材管理模块中看到
     * 注意：上传大文件时可能需要先调用 set_time_limit(0) 避免超时
     * 注意：数组的键值任意，但文件名前必须加@，使用单引号以避免本地路径斜杠被转义
     * @param array $file_url
     * @param string $type 类型：图片:image 语音:voice 视频:video 缩略图:thumb
     * @param bool $is_video 是否为视频文件，默认为否
     * @param array $video_info 视频信息数组，非视频素材不需要提供 array('title'=>'视频标题','introduction'=>'描述')
     * @return bool|array
     */
    public function uploadForeverMedia($file_url, $type, $is_video = false, $video_info = array())
    {
        if (!$this->access_token || empty($file_url)) {
            return false;
        }
        $data['media'] = Curl::getCurlFileMedia($file_url);
        if($is_video){
            $data['description'] = StaticFunction::jsonEncode($video_info);
        }
        $curl_url = self::API_URL_PREFIX . self::MEDIA_FOREVER_UPLOAD_URL . "access_token={$this->access_token}";
        $result = Curl::CurlFile($curl_url, $data);
        return $this->resultJsonWithRetry($result,__FUNCTION__, func_get_args());

    }

    /**
     * 上传永久图文素材(认证后的订阅号可用)
     * 新增的永久素材也可以在公众平台官网素材管理模块中看到
     * @param array $data 消息结构{"articles":[{...}]}
     * @return bool|array
     */
    public function uploadForeverArticles($data)
    {
        if (!$this->access_token || empty($data)) {
            return false;
        }
        $curl_url = self::API_URL_PREFIX . self::MEDIA_FOREVER_NEWS_UPLOAD_URL ."access_token={$this->access_token}";
        return $this->returnPostResult($curl_url,$data,__FUNCTION__, func_get_args());

    }

    /**
     * 修改永久图文素材(认证后的订阅号可用)
     * 永久素材也可以在公众平台官网素材管理模块中看到
     * @param string $media_id 图文素材id
     * @param array $data 消息结构{"articles":[{...}]}
     * @param int $index 更新的文章在图文素材的位置，第一篇为0，仅多图文使用
     * @return bool|array
     */
    public function updateForeverArticles($media_id, $data, $index = 0)
    {
        if (!$this->access_token || empty($media_id) || empty($data)) {
            return false;
        }
        if (!isset($data['index'])) {
            $data['index'] = $index;
        }
        if (!isset($data['media_id'])) {
            $data['media_id'] = $media_id;
        }
        $curl_url = self::API_URL_PREFIX . self::MEDIA_FOREVER_NEWS_UPDATE_URL . "access_token={$this->access_token}";
        return $this->returnPostResult($curl_url, $data, __FUNCTION__, func_get_args());
    }

    /**
     * 获取永久素材(认证后的订阅号可用)
     * 返回图文消息数组或二进制数据，失败返回false
     * @param string $media_id 媒体文件id
     * @param bool $is_video 是否为视频文件，默认为否
     * @return bool|array
     */
    public function getForeverMedia($media_id, $is_video = false)
    {
        if (!$this->access_token|| empty($media_id)) {
            return false;
        }
        $data = ['media_id' => $media_id];

        $curl_url = self::API_URL_PREFIX . self::MEDIA_FOREVER_GET_URL ."access_token={$this->access_token}";
        $result = Curl::curlPost($curl_url,$data) ;

        if ($result) {
            if (is_string($result)) {
                $json = StaticFunction::parseJSON($result);
                if ($json) {
                    if (isset($json['errcode'])) {
                        $this->errCode = $json['errcode'];
                        $this->errMsg = $json['errmsg'];
                        return $this->checkRetry(__FUNCTION__, func_get_args());
                    }
                    return $json;
                }
                return $result;
            }
            return $result;
        }
        return false;
    }

    /**
     * 删除永久素材(认证后的订阅号可用)
     * @param string $media_id 媒体文件id
     * @return bool
     */
    public function delForeverMedia($media_id)
    {
        if (!$this->access_token || empty($media_id)) {
            return false;
        }
        $data = ['media_id' => $media_id];

        $curl_url = self::API_URL_PREFIX . self::MEDIA_FOREVER_DEL_URL ."access_token={$this->access_token}";
        $result = Curl::curlPost($curl_url,$data) ;
        return $this->resultBoolWithRetry($result,__FUNCTION__, func_get_args());
    }

    /**
     * 获取永久素材列表(认证后的订阅号可用)
     * @param string $type 素材的类型,图片（image）、视频（video）、语音 （voice）、图文（news）
     * @param int $offset 全部素材的偏移位置，0表示从第一个素材
     * @param int $count 返回素材的数量，取值在1到20之间
     * @return bool|array
     * 返回数组格式:
     * array(
     *  'total_count'=>0, //该类型的素材的总数
     *  'item_count'=>0,  //本次调用获取的素材的数量
     *  'item'=>array()   //素材列表数组，内容定义请参考官方文档
     * )
     */
    public function getForeverList($type, $offset, $count)
    {
        if (!$this->access_token  || empty($type) || empty($offset) || empty($count)) {
            return false;
        }
        $data = ['type' => $type, 'offset' => $offset, 'count' => $count,];

        $curl_url = self::API_URL_PREFIX . self::MEDIA_FOREVER_BATCHGET_URL . "access_token={$this->access_token}";
        return $this->returnPostResult($curl_url, $data, __FUNCTION__, func_get_args());
    }

    /**
     * 获取永久素材总数(认证后的订阅号可用)
     * @return bool|array
     * 返回数组格式:
     * array(
     *  'voice_count'=>0, //语音总数量
     *  'video_count'=>0, //视频总数量
     *  'image_count'=>0, //图片总数量
     *  'news_count'=>0   //图文总数量
     * )
     */
    public function getForeverCount()
    {
        if (!$this->access_token) {
            return false;
        }

        $curl_url = self::API_URL_PREFIX . self::MEDIA_FOREVER_COUNT_URL."access_token={$this->access_token}";
        return $this->returnGetResult($curl_url,__FUNCTION__, func_get_args());
    }

    /**
     * 上传图文消息素材，用于群发(认证后的订阅号可用)
     * @param array $data 消息结构{"articles":[{...}]}
     * @return bool|array
     */
    public function uploadArticles($data)
    {
        if (!$this->access_token  || empty($data)) {
            return false;
        }

        $curl_url = self::API_URL_PREFIX . self::MEDIA_UPLOADNEWS_URL ."access_token={$this->access_token}";
        return $this->returnPostResult($curl_url,$data,__FUNCTION__, func_get_args());

    }

    /**
     * 上传视频素材(认证后的订阅号可用)
     * @param array $data 消息结构
     * {
     *     "media_id"=>"",     //通过上传媒体接口得到的MediaId
     *     "title"=>"TITLE",    //视频标题
     *     "description"=>"Description"        //视频描述
     * }
     * @return bool|array
     * {
     *     "type":"video",
     *     "media_id":"mediaid",
     *     "created_at":1398848981
     *  }
     */
    public function uploadMpVideo($data)
    {
        if (!$this->access_token   || empty($data)) {
            return false;
        }

        $curl_url = self::UPLOAD_MEDIA_URL . self::MEDIA_VIDEO_UPLOAD ."access_token={$this->access_token}";
        return $this->returnPostResult($curl_url,$data,__FUNCTION__, func_get_args());

    }


}