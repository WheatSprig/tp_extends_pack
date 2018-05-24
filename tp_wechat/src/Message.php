<?php
namespace mikkle\tp_wechat\src;
use mikkle\tp_wechat\base\WechatBase;
use mikkle\tp_wechat\support\Curl;
use mikkle\tp_wechat\support\StaticFunction;
use think\Log;

/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/8/30
 * Time: 15:43
 */
class Message extends WechatBase
{


    public function  __construct($options)
    {
        parent::__construct($options);
        $this->getToken();
    }


    /**
     * 获取模板列表
     * @return bool|array
     */
    public function getAllPrivateTemplate()
    {
        if (!$this->access_token) {
            return false;
        }
        $curl_url = self::API_URL_PREFIX . "/template/get_all_private_template?access_token={$this->access_token}";
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
     * 获取设置的行业信息
     * @return bool|array
     */
    public function getTMIndustry()
    {
        if (!$this->access_token) {
            return false;
        }
        $curl_url = self::API_URL_PREFIX . "/template/get_industry?access_token={$this->access_token}";
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
     * 删除模板消息
     * @param string $tpl_id
     * @return bool
     */
    public function delPrivateTemplate($tpl_id)
    {
        if (!$this->access_token || empty($tpl_id)){
            return false;
        }
        $data = ['template_id' => $tpl_id];
        $curl_url = self::API_URL_PREFIX . "/template/del_private_template?access_token={$this->access_token}";
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
     * 模板消息 设置所属行业
     * @param string $id1 公众号模板消息所属行业编号，参看官方开发文档 行业代码
     * @param string $id2 同$id1。但如果只有一个行业，此参数可省略
     * @return bool|mixed
     */
    public function setTMIndustry($id1, $id2 = '')
    {
        if (!$this->access_token){
            return false;
        }
        $data = [];
        if (empty($id1)){
            return false;
        }
        $data['industry_id1'] = $id1;
        if(empty($id2)){
            $data['industry_id2'] = $id2;
        }
        $curl_url = self::API_URL_PREFIX .  "/template/api_set_industry?access_token={$this->access_token}";
        $result = Curl::curlPost($curl_url,$data) ;

        if ($result) {
            $json = StaticFunction::parseJSON($result);
            if (!$json || !empty($json['errcode'])) {
                $this->errMsg = $json['errmsg'];
                $this->errCode = $json['errcode'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json;
        }
        return false;
    }

    /**
     * 模板消息 添加消息模板
     * 成功返回消息模板的调用id
     * @param string $tpl_id 模板库中模板的编号，有“TM**”和“OPENTMTM**”等形式
     * @return bool|string
     */
    public function addTemplateMessage($tpl_id)
    {
        if (!$this->access_token|| empty($tpl_id)){
            return false;
        }
        $data = ['template_id_short' => $tpl_id];
        $curl_url = self::API_URL_PREFIX . "/template/api_add_template?access_token={$this->access_token}";
        $result = Curl::curlPost($curl_url,$data) ;
        if ($result) {
            $json = StaticFunction::parseJSON($result);
            if (!$json || !empty($json['errcode'])) {
                $this->errMsg = $json['errmsg'];
                $this->errCode = $json['errcode'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json['template_id'];
        }
        return false;
    }

    /**
     * 发送模板消息
     * @param array $data 消息结构
     * {
     *      "touser":"OPENID",
     *       "template_id":"ngqIpbwh8bUfcSsECmogfXcV14J0tQlEpBO27izEYtY",
     *       "url":"http://weixin.qq.com/download",
     *       "topcolor":"#FF0000",
     *       "data":{
     *           "参数名1": {
     *           "value":"参数",
     *           "color":"#173177"     //参数颜色
     *       },
     *       "Date":{
     *           "value":"06月07日 19时24分",
     *           "color":"#173177"
     *       },
     *       "CardNumber":{
     *           "value":"0426",
     *           "color":"#173177"
     *      },
     *      "Type":{
     *          "value":"消费",
     *          "color":"#173177"
     *       }
     *   }
     * }
     * @return bool|array
     */
    public function sendTemplateMessage($data)
    {
        if (!$this->access_token || empty($data)){
            Log::error("未登录");
            return false;
        }
        $curl_url = self::API_URL_PREFIX . "/message/template/send?access_token={$this->access_token}";
        $result = Curl::curlPost($curl_url,$data) ;
        if ($result) {
            $json = StaticFunction::parseJSON($result);
            if (!$json || !empty($json['errcode'])) {
                $this->errMsg = $json['errmsg'];
                $this->errCode = $json['errcode'];
                return $this->checkRetry(__FUNCTION__, func_get_args());
            }
            return $json['msgid'];
        }
        return false;
    }

    /**
     * 根据标签进行群发 ( 订阅号与服务号认证后均可用 )
     * @param array $data 消息结构
     * 注意: 视频需要在调用uploadMedia()方法后，再使用 uploadMpVideo() 方法生成，
     *       然后获得的 mediaid 才能用于群发，且消息类型为 mpvideo 类型。
     * @return bool|array
     * {
     *     "touser"=>array(
     *         "OPENID1",
     *         "OPENID2"
     *     ),
     *      "msgtype"=>"mpvideo",
     *      // 在下面5种类型中选择对应的参数内容
     *      // mpnews | voice | image | mpvideo => array( "media_id"=>"MediaId")
     *      // text => array ( "content" => "hello")
     * }
     */
    public function sendMassMessage($data)
    {
        if (!$this->access_token || empty($data)){
            return false;
        }

        $curl_url = self::API_URL_PREFIX . "/message/mass/send?access_token={$this->access_token}";
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
     * 根据标签进行群发 ( 订阅号与服务号认证后均可用 )
     * @param array $data 消息结构
     * 注意：视频需要在调用uploadMedia()方法后，再使用 uploadMpVideo() 方法生成，
     *       然后获得的 mediaid 才能用于群发，且消息类型为 mpvideo 类型。
     * @return bool|array
     * {
     *     "filter"=>array(
     *         "is_to_all"=>False,     //是否群发给所有用户.True不用分组id，False需填写分组id
     *         "group_id"=>"2"     //群发的分组id
     *     ),
     *      "msgtype"=>"mpvideo",
     *      // 在下面5种类型中选择对应的参数内容
     *      // mpnews | voice | image | mpvideo => array( "media_id"=>"MediaId")
     *      // text => array ( "content" => "hello")
     * }
     */
    public function sendGroupMassMessage($data)
    {
        if (!$this->access_token || empty($data)){
            return false;
        }

        $curl_url = self::API_URL_PREFIX . "/message/mass/sendall?access_token={$this->access_token}";
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
     * 删除群发图文消息 ( 订阅号与服务号认证后均可用 )
     * @param string $msg_id 消息ID
     * @return bool
     */
    public function deleteMassMessage($msg_id)
    {
        if (!$this->access_token || empty($msg_id)){
            return false;
        }
        $data = ['msg_id' => $msg_id];
        $curl_url = self::API_URL_PREFIX . "/message/mass/delete?access_token={$this->access_token}";
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
     * 预览群发消息 ( 订阅号与服务号认证后均可用 )
     * @param array $data
     * 注意: 视频需要在调用uploadMedia()方法后，再使用 uploadMpVideo() 方法生成，
     *       然后获得的 mediaid 才能用于群发，且消息类型为 mpvideo 类型。
     * @消息结构
     * {
     *     "touser"=>"OPENID",
     *      "msgtype"=>"mpvideo",
     *      // 在下面5种类型中选择对应的参数内容
     *      // mpnews | voice | image | mpvideo => array( "media_id"=>"MediaId")
     *      // text => array ( "content" => "hello")
     * }
     * @return bool|array
     */
    public function previewMassMessage($data)
    {
        if (!$this->access_token || empty($data)){
            return false;
        }

        $curl_url = self::API_URL_PREFIX . "/message/mass/preview?access_token={$this->access_token}";
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
     * 查询群发消息发送状态 ( 订阅号与服务号认证后均可用 )
     * @param string $msg_id 消息ID
     * @return bool|array
     * {
     *     "msg_id":201053012, //群发消息后返回的消息id
     *     "msg_status":"SEND_SUCCESS", //消息发送后的状态，SENDING表示正在发送 SEND_SUCCESS表示发送成功
     * }
     */
    public function queryMassMessage($msg_id)
    {
        if (!$this->access_token || empty($msg_id)){
            return false;
        }

        $data = ['msg_id' => $msg_id];
        $curl_url = self::API_URL_PREFIX . "/message/mass/get?access_token={$this->access_token}";
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




}