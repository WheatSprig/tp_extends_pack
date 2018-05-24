<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/9/12
 * Time: 14:28
 */

namespace mikkle\tp_wechat\src;


use mikkle\tp_wechat\base\WechatBase;
use mikkle\tp_wechat\support\Curl;
use mikkle\tp_master\Log;
use mikkle\tp_wechat\support\Prpcrypt;
use mikkle\tp_wechat\support\StaticFunction;

class Receive extends WechatBase
{

    /** 消息回复类型 */
    const MSGTYPE_TEXT = 'text';
    const MSGTYPE_LINK = 'link';
    const MSGTYPE_NEWS = 'news';
    const MSGTYPE_IMAGE = 'image';
    const MSGTYPE_VOICE = 'voice';
    const MSGTYPE_EVENT = 'event';
    const MSGTYPE_MUSIC = 'music';
    const MSGTYPE_VIDEO = 'video';
    const MSGTYPE_LOCATION = 'location';

    const EVENT_SUBSCRIBE = 'subscribe';       //订阅
    const EVENT_UNSUBSCRIBE = 'unsubscribe';   //取消订阅
    const EVENT_SCAN = 'SCAN';                 //扫描带参数二维码
    const EVENT_LOCATION = 'LOCATION';         //上报地理位置
    const EVENT_MENU_VIEW = 'VIEW';                     //菜单 - 点击菜单跳转链接
    const EVENT_MENU_CLICK = 'CLICK';                   //菜单 - 点击菜单拉取消息
    const EVENT_MENU_SCAN_PUSH = 'scancode_push';       //菜单 - 扫码推事件(客户端跳URL)
    const EVENT_MENU_SCAN_WAITMSG = 'scancode_waitmsg'; //菜单 - 扫码推事件(客户端不跳URL)
    const EVENT_MENU_PIC_SYS = 'pic_sysphoto';          //菜单 - 弹出系统拍照发图
    const EVENT_MENU_PIC_PHOTO = 'pic_photo_or_album';  //菜单 - 弹出拍照或者相册发图
    const EVENT_MENU_PIC_WEIXIN = 'pic_weixin';         //菜单 - 弹出微信相册发图器
    const EVENT_MENU_LOCATION = 'location_select';      //菜单 - 弹出地理位置选择器
    const EVENT_SEND_MASS = 'MASSSENDJOBFINISH';        //发送结果 - 高级群发完成
    const EVENT_SEND_TEMPLATE = 'TEMPLATESENDJOBFINISH';//发送结果 - 模板消息发送结果
    const EVENT_KF_SEESION_CREATE = 'kfcreatesession';  //多客服 - 接入会话
    const EVENT_KF_SEESION_CLOSE = 'kfclosesession';    //多客服 - 关闭会话
    const EVENT_KF_SEESION_SWITCH = 'kfswitchsession';  //多客服 - 转接会话
    const EVENT_CARD_PASS = 'card_pass_check';          //卡券 - 审核通过
    const EVENT_CARD_NOTPASS = 'card_not_pass_check';   //卡券 - 审核未通过
    const EVENT_CARD_USER_GET = 'user_get_card';        //卡券 - 用户领取卡券
    const EVENT_CARD_USER_DEL = 'user_del_card';        //卡券 - 用户删除卡券
    const EVENT_MERCHANT_ORDER = 'merchant_order';        //微信小店 - 订单付款通知


    /* 获取粉丝信息 */
    const USER_INFO_URL = '/user/info?';


    /** 文本过滤 */
    protected $_text_filter = true;

    /** 消息对象 */
    private $_receive;
    protected $encrypt_type;
    protected $encodingAesKey;
    protected $config;

    protected $_msg;


    public function  __construct($options=[])
    {
        parent::__construct($options);
        $this->token = $options["token"];
    }
    /**
     * 获取微信服务器发来的内容
     * @return $this
     */
    public function getRev()
    {
        if ($this->_receive) {
            return $this;
        }
        $postStr = !empty($this->postxml) ? $this->postxml : file_get_contents("php://input");
        !empty($postStr) && $this->_receive = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        return $this;
    }

    /**
     * 获取微信服务器发来的信息数据
     * @return array
     */
    public function getRevData()
    {
        return $this->_receive;
    }

    /**
     * 获取消息发送者
     * @return bool|string
     */
    public function getRevFrom()
    {
        if (isset($this->_receive['FromUserName'])) {
            return $this->_receive['FromUserName'];
        }
        return false;
    }

    /**
     * 获取消息接受者
     * @return bool|string
     */
    public function getRevTo()
    {
        if (isset($this->_receive['ToUserName'])) {
            return $this->_receive['ToUserName'];
        }
        return false;
    }

    /**
     * 获取接收消息的类型
     * @return bool|string
     */
    public function getRevType()
    {
        if (isset($this->_receive['MsgType'])) {
            return $this->_receive['MsgType'];
        }
        return false;
    }

    /**
     * 获取消息ID
     * @return bool|string
     */
    public function getRevID()
    {
        if (isset($this->_receive['MsgId'])) {
            return $this->_receive['MsgId'];
        }
        return false;
    }

    /**
     * 获取消息发送时间
     * @return bool|string
     */
    public function getRevCtime()
    {
        if (isset($this->_receive['CreateTime'])) {
            return $this->_receive['CreateTime'];
        }
        return false;
    }

    /**
     * 获取卡券事件推送 - 卡卷审核是否通过
     * 当Event为 card_pass_check(审核通过) 或 card_not_pass_check(未通过)
     * @return bool|string  返回卡券ID
     */
    public function getRevCardPass()
    {
        if (isset($this->_receive['CardId'])) {
            return $this->_receive['CardId'];
        }
        return false;
    }

    /**
     * 获取卡券事件推送 - 领取卡券
     * 当Event为 user_get_card(用户领取卡券)
     * @return bool|array
     */
    public function getRevCardGet()
    {
        $array = array();
        if (isset($this->_receive['CardId'])) {
            $array['CardId'] = $this->_receive['CardId'];
        }
        if (isset($this->_receive['IsGiveByFriend'])) {
            $array['IsGiveByFriend'] = $this->_receive['IsGiveByFriend'];
        }
        $array['OldUserCardCode'] = $this->_receive['OldUserCardCode'];
        if (isset($this->_receive['UserCardCode']) && !empty($this->_receive['UserCardCode'])) {
            $array['UserCardCode'] = $this->_receive['UserCardCode'];
        }
        return (isset($array) && count($array) > 0) ? $array : false;
    }

    /**
     * 获取卡券事件推送 - 删除卡券
     * 当Event为 user_del_card (用户删除卡券)
     * @return bool|array
     */
    public function getRevCardDel()
    {
        if (isset($this->_receive['CardId'])) {  //卡券 ID
            $array['CardId'] = $this->_receive['CardId'];
        }
        if (isset($this->_receive['UserCardCode']) && !empty($this->_receive['UserCardCode'])) {
            $array['UserCardCode'] = $this->_receive['UserCardCode'];
        }
        return (isset($array) && count($array) > 0) ? $array : false;
    }

    /**
     * 获取接收消息内容正文
     * @return bool
     */
    public function getRevContent()
    {
        if (isset($this->_receive['Content'])) {
            return $this->_receive['Content'];
        } else if (isset($this->_receive['Recognition'])) {
            return $this->_receive['Recognition'];
        }
        return false;
    }

    /**
     * 获取接收消息图片
     * @return array|bool
     */
    public function getRevPic()
    {
        if (isset($this->_receive['PicUrl'])) {
            return [
                'mediaid' => $this->_receive['MediaId'],
                'picurl'  => (string)$this->_receive['PicUrl'],
            ];
        }
        return false;
    }

    /**
     * 获取接收消息链接
     * @return bool|array
     */
    public function getRevLink()
    {
        if (isset($this->_receive['Url'])) {
            return [
                'url'         => $this->_receive['Url'],
                'title'       => $this->_receive['Title'],
                'description' => $this->_receive['Description']
            ];
        }
        return false;
    }

    /**
     * 获取接收地理位置
     * @return bool|array
     */
    public function getRevGeo()
    {
        if (isset($this->_receive['Location_X'])) {
            return [
                'x'     => $this->_receive['Location_X'],
                'y'     => $this->_receive['Location_Y'],
                'scale' => $this->_receive['Scale'],
                'label' => $this->_receive['Label']
            ];
        }
        return false;
    }

    /**
     * 获取上报地理位置事件
     * @return bool|array
     */
    public function getRevEventGeo()
    {
        if (isset($this->_receive['Latitude'])) {
            return array(
                'x'         => $this->_receive['Latitude'],
                'y'         => $this->_receive['Longitude'],
                'precision' => $this->_receive['Precision'],
            );
        }
        return false;
    }

    /**
     * 获取接收事件推送
     * @return bool|array
     */
    public function getRevEvent()
    {
        if (isset($this->_receive['Event'])) {
            $array['event'] = $this->_receive['Event'];
        }
        if (isset($this->_receive['EventKey'])) {
            $array['key'] = $this->_receive['EventKey'];
        }
        return (isset($array) && count($array) > 0) ? $array : false;
    }

    /**
     * 获取自定义菜单的扫码推事件信息
     *
     * 事件类型为以下两种时则调用此方法有效
     * Event    事件类型, scancode_push
     * Event    事件类型, scancode_waitmsg
     * @return bool|array
     */
    public function getRevScanInfo()
    {
        if (isset($this->_receive['ScanCodeInfo'])) {
            if (!is_array($this->_receive['ScanCodeInfo'])) {
                $array = (array)$this->_receive['ScanCodeInfo'];
                $this->_receive['ScanCodeInfo'] = $array;
            } else {
                $array = $this->_receive['ScanCodeInfo'];
            }
        }
        return (isset($array) && count($array) > 0) ? $array : false;
    }

    /**
     * 获取自定义菜单的图片发送事件信息
     *
     * 事件类型为以下三种时则调用此方法有效
     * Event     事件类型，pic_sysphoto        弹出系统拍照发图的事件推送
     * Event     事件类型，pic_photo_or_album  弹出拍照或者相册发图的事件推送
     * Event     事件类型，pic_weixin          弹出微信相册发图器的事件推送
     *
     * @return bool|array
     * array (
     *   'Count' => '2',
     *   'PicList' =>array (
     *         'item' =>array (
     *             0 =>array ('PicMd5Sum' => 'aaae42617cf2a14342d96005af53624c'),
     *             1 =>array ('PicMd5Sum' => '149bd39e296860a2adc2f1bb81616ff8'),
     *         ),
     *   ),
     * )
     *
     */
    public function getRevSendPicsInfo()
    {
        if (isset($this->_receive['SendPicsInfo'])) {
            if (!is_array($this->_receive['SendPicsInfo'])) {
                $array = (array)$this->_receive['SendPicsInfo'];
                if (isset($array['PicList'])) {
                    $array['PicList'] = (array)$array['PicList'];
                    $item = $array['PicList']['item'];
                    $array['PicList']['item'] = array();
                    foreach ($item as $key => $value) {
                        $array['PicList']['item'][$key] = (array)$value;
                    }
                }
                $this->_receive['SendPicsInfo'] = $array;
            } else {
                $array = $this->_receive['SendPicsInfo'];
            }
        }
        return (isset($array) && count($array) > 0) ? $array : false;
    }

    /**
     * 获取自定义菜单的地理位置选择器事件推送
     *
     * 事件类型为以下时则可以调用此方法有效
     * Event     事件类型，location_select        弹出地理位置选择器的事件推送
     *
     * @return bool|array
     * array (
     *   'Location_X' => '33.731655000061',
     *   'Location_Y' => '113.29955200008047',
     *   'Scale' => '16',
     *   'Label' => '某某市某某区某某路',
     *   'Poiname' => '',
     * )
     *
     */
    public function getRevSendGeoInfo()
    {
        if (isset($this->_receive['SendLocationInfo'])) {
            if (!is_array($this->_receive['SendLocationInfo'])) {
                $array = (array)$this->_receive['SendLocationInfo'];
                if (empty($array['Poiname'])) {
                    $array['Poiname'] = "";
                }
                if (empty($array['Label'])) {
                    $array['Label'] = "";
                }
                $this->_receive['SendLocationInfo'] = $array;
            } else {
                $array = $this->_receive['SendLocationInfo'];
            }
        }
        return (isset($array) && count($array) > 0) ? $array : false;
    }

    /**
     * 获取接收语音推送
     * @return bool|array
     */
    public function getRevVoice()
    {
        if (isset($this->_receive['MediaId'])) {
            return array(
                'mediaid' => $this->_receive['MediaId'],
                'format'  => $this->_receive['Format'],
            );
        }
        return false;
    }

    /**
     * 获取接收视频推送
     * @return array|bool
     */
    public function getRevVideo()
    {
        if (isset($this->_receive['MediaId'])) {
            return [
                'mediaid'      => $this->_receive['MediaId'],
                'thumbmediaid' => $this->_receive['ThumbMediaId']
            ];
        }
        return false;
    }

    /**
     * 获取接收TICKET
     * @return bool|string
     */
    public function getRevTicket()
    {
        if (isset($this->_receive['Ticket'])) {
            return $this->_receive['Ticket'];
        }
        return false;
    }

    /**
     * 获取二维码的场景值
     * @return bool|string
     */
    public function getRevSceneId()
    {
        if (isset($this->_receive['EventKey'])) {
            return str_replace('qrscene_', '', $this->_receive['EventKey']);
        }
        return false;
    }

    /**
     * 获取主动推送的消息ID
     * 经过验证，这个和普通的消息MsgId不一样
     * 当Event为 MASSSENDJOBFINISH 或 TEMPLATESENDJOBFINISH
     * @return bool|string
     */
    public function getRevTplMsgID()
    {
        if (isset($this->_receive['MsgID'])) {
            return $this->_receive['MsgID'];
        }
        return false;
    }

    /**
     * 获取模板消息发送状态
     * @return bool|string
     */
    public function getRevStatus()
    {
        if (isset($this->_receive['Status'])) {
            return $this->_receive['Status'];
        }
        return false;
    }

    /**
     * 获取群发或模板消息发送结果
     * 当Event为 MASSSENDJOBFINISH 或 TEMPLATESENDJOBFINISH，即高级群发/模板消息
     * @return bool|array
     */
    public function getRevResult()
    {
        if (isset($this->_receive['Status'])) { //发送是否成功，具体的返回值请参考 高级群发/模板消息 的事件推送说明
            $array['Status'] = $this->_receive['Status'];
        }
        if (isset($this->_receive['MsgID'])) { //发送的消息id
            $array['MsgID'] = $this->_receive['MsgID'];
        }
        //以下仅当群发消息时才会有的事件内容
        if (isset($this->_receive['TotalCount'])) {  //分组或openid列表内粉丝数量
            $array['TotalCount'] = $this->_receive['TotalCount'];
        }
        if (isset($this->_receive['FilterCount'])) { //过滤（过滤是指特定地区、性别的过滤、用户设置拒收的过滤，用户接收已超4条的过滤）后，准备发送的粉丝数
            $array['FilterCount'] = $this->_receive['FilterCount'];
        }
        if (isset($this->_receive['SentCount'])) {  //发送成功的粉丝数
            $array['SentCount'] = $this->_receive['SentCount'];
        }
        if (isset($this->_receive['ErrorCount'])) { //发送失败的粉丝数
            $array['ErrorCount'] = $this->_receive['ErrorCount'];
        }
        if (isset($array) && count($array) > 0) {
            return $array;
        }
        return false;
    }

    /**
     * 获取多客服会话状态推送事件 - 接入会话
     * 当Event为 kfcreatesession 即接入会话
     * @return bool|string
     */
    public function getRevKFCreate()
    {
        if (isset($this->_receive['KfAccount'])) {
            return $this->_receive['KfAccount'];
        }
        return false;
    }

    /**
     * 获取多客服会话状态推送事件 - 关闭会话
     * 当Event为 kfclosesession 即关闭会话
     * @return bool|string
     */
    public function getRevKFClose()
    {
        if (isset($this->_receive['KfAccount'])) {
            return $this->_receive['KfAccount'];
        }
        return false;
    }

    /**
     * 获取多客服会话状态推送事件 - 转接会话
     * 当Event为 kfswitchsession 即转接会话
     * @return bool|array
     */
    public function getRevKFSwitch()
    {
        if (isset($this->_receive['FromKfAccount'])) {  //原接入客服
            $array['FromKfAccount'] = $this->_receive['FromKfAccount'];
        }
        if (isset($this->_receive['ToKfAccount'])) { //转接到客服
            $array['ToKfAccount'] = $this->_receive['ToKfAccount'];
        }
        return (isset($array) && count($array) > 0) ? $array : false;
    }

    /**
     * 发送客服消息
     * @param array $data 消息结构{"touser":"OPENID","msgtype":"news","news":{...}}
     * @return bool|array
     */
    public function sendCustomMessage($data)
    {
        if (!$this->access_token || empty($data)) {
            return false;
        }

        $curl_url = self::API_URL_PREFIX .   "/message/custom/send?access_token={$this->access_token}";
        $result = Curl::curlPost($curl_url,$data) ;

        return $this->resultJsonWithRetry($result,__FUNCTION__, func_get_args());

    }

    /**
     * 转发多客服消息
     * @param string $customer_account
     * @return $this
     */
    public function transferCustomerService($customer_account = '')
    {
        $msg = [
            'ToUserName'   => $this->getRevFrom(),
            'FromUserName' => $this->getRevTo(),
            'CreateTime'   => time(),
            'MsgType'      => 'transfer_customer_service',
        ];
        if ($customer_account) {
            $msg['TransInfo'] = ['KfAccount' => $customer_account];
        }
        $this->Message($msg);
        return $this;
    }

    /**
     * 设置发送消息
     * @param string|array $msg 消息数组
     * @param bool $append 是否在原消息数组追加
     * @return array
     */
    public function Message($msg = '', $append = false)
    {
        if (is_null($msg)) {
            $this->_msg = array();
        } elseif (is_array($msg)) {
            if ($append) {
                $this->_msg = array_merge($this->_msg, $msg);
            } else {
                $this->_msg = $msg;
            }
            return $this->_msg;
        }
        return $this->_msg;
    }

    /**
     * 设置文本消息
     * @param string $text 文本内容
     * @return $this
     */
    public function text($text = '')
    {
        $msg = [
            'ToUserName'   => $this->getRevFrom(),
            'FromUserName' => $this->getRevTo(),
            'MsgType'      => self::MSGTYPE_TEXT,
            'Content'      => $this->_auto_text_filter($text),
            'CreateTime'   => time(),
        ];
        $this->Message($msg);
        return $this;
    }

    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param string $mediaid 设置图片消息
     * @return $this
     */
    public function image($mediaid = '')
    {
        $msg = [
            'ToUserName'   => $this->getRevFrom(),
            'FromUserName' => $this->getRevTo(),
            'MsgType'      => self::MSGTYPE_IMAGE,
            'Image'        => ['MediaId' => $mediaid],
            'CreateTime'   => time(),
        ];
        $this->Message($msg);
        return $this;
    }

    /**
     * 设置语音回复消息
     * @param string $mediaid 语音媒体ID
     * @return $this
     */
    public function voice($mediaid = '')
    {
        $msg = [
            'ToUserName'   => $this->getRevFrom(),
            'FromUserName' => $this->getRevTo(),
            'MsgType'      => self::MSGTYPE_VOICE,
            'Voice'        => ['MediaId' => $mediaid],
            'CreateTime'   => time(),
        ];
        $this->Message($msg);
        return $this;
    }

    /**
     * 设置视频回复消息
     * @param string $mediaid 视频媒体ID
     * @param string $title 视频标题
     * @param string $description 视频描述
     * @return $this
     */
    public function video($mediaid = '', $title = '', $description = '')
    {
        $msg = [
            'ToUserName'   => $this->getRevFrom(),
            'FromUserName' => $this->getRevTo(),
            'MsgType'      => self::MSGTYPE_VIDEO,
            'Video'        => [
                'MediaId'     => $mediaid,
                'Title'       => $title,
                'Description' => $description
            ],
            'CreateTime'   => time(),
        ];
        $this->Message($msg);
        return $this;
    }

    /**
     * 设置音乐回复消息
     * @param string $title 音乐标题
     * @param string $desc 音乐描述
     * @param string $musicurl 音乐地址
     * @param string $hgmusicurl 高清音乐地址
     * @param string $thumbmediaid 音乐图片缩略图的媒体id（可选）
     * @return $this
     */
    public function music($title, $desc, $musicurl, $hgmusicurl = '', $thumbmediaid = '')
    {
        $msg = [
            'ToUserName'   => $this->getRevFrom(),
            'FromUserName' => $this->getRevTo(),
            'CreateTime'   => time(),
            'MsgType'      => self::MSGTYPE_MUSIC,
            'Music'        => [
                'Title'       => $title,
                'Description' => $desc,
                'MusicUrl'    => $musicurl,
                'HQMusicUrl'  => $hgmusicurl
            ],
        ];
        if ($thumbmediaid) {
            $msg['Music']['ThumbMediaId'] = $thumbmediaid;
        }
        $this->Message($msg);
        return $this;
    }

    /**
     * 设置回复图文
     * @param array $newsData
     * @return $this
     */
    public function news($newsData = array())
    {
        $msg = [
            'ToUserName'   => $this->getRevFrom(),
            'FromUserName' => $this->getRevTo(),
            'CreateTime'   => time(),
            'MsgType'      => self::MSGTYPE_NEWS,
            'ArticleCount' => count($newsData),
            'Articles'     => $newsData,
        ];
        $this->Message($msg);
        return $this;
    }

    /**
     * 回复微信服务器
     * @param array $msg 要发送的信息（默认取$this->_msg）
     * @param bool $return 是否返回信息而不抛出到浏览器（默认:否）
     * @return bool|string
     */
    public function reply($msg = array(), $return = false)
    {
        if (empty($msg)) {
            if (empty($this->_msg)) {   //防止不先设置回复内容，直接调用reply方法导致异常
                return false;
            }
            $msg = $this->_msg;
        }
        $xmldata = StaticFunction::arr2xml($msg);
        if ($this->encrypt_type == 'aes') { //如果来源消息为加密方式

            $pc = new Prpcrypt($this->encodingAesKey);
            // 如果是第三方平台，加密得使用 component_appid
            $array = $pc->encrypt($xmldata, empty($this->config['component_appid']) ? $this->appId : $this->config['component_appid']);
            $ret = $array[0];
            if ($ret != 0) {
                Log::notice('encrypt err!');
                return false;
            }
            $timestamp = time();
            $nonce = rand(77, 999) * rand(605, 888) * rand(11, 99);
            $encrypt = $array[1];
            $tmpArr = array($this->access_token, $timestamp, $nonce, $encrypt);
            sort($tmpArr, SORT_STRING);
            $signature = sha1(implode($tmpArr));
            $format = "<xml><Encrypt><![CDATA[%s]]></Encrypt><MsgSignature><![CDATA[%s]]></MsgSignature><TimeStamp>%s</TimeStamp><Nonce><![CDATA[%s]]></Nonce></xml>";
            $xmldata = sprintf($format, $encrypt, $signature, $timestamp, $nonce);
        }
        if ($return) {
            return $xmldata;
        }
        echo $xmldata;
    }

    /**
     * 过滤文字回复\r\n换行符
     * @param string $text
     * @return string
     */
    private function _auto_text_filter($text)
    {
        if (!$this->_text_filter) {
            return $text;
        }
        return str_replace("\r\n", "\n", $text);
    }

    /**
     * 接口验证
     * @return bool
     */
    public function valid()
    {
        $encryptStr = "";
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            $postStr = file_get_contents("php://input");
            $array = (array)simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $this->encrypt_type = isset($_GET["encrypt_type"]) ? $_GET["encrypt_type"] : '';

            if ($this->encrypt_type == 'aes') {
                $encryptStr = $array['Encrypt'];
                $pc = new Prpcrypt($this->encodingAesKey);
                $array = $pc->decrypt($encryptStr, $this->appId);
                if (isset($array[0]) || intval($array[0]) > 0) {
                    $this->errCode = $array[0];
                    $this->errMsg = $array[1];
                    Log::notice("Interface Authentication Failed. {$this->errMsg}[{$this->errCode}]");
                    return false;
                }
                $this->postxml = $array[1];
                empty($this->appid) && $this->appId = $array[2];
            } else {
                $this->postxml = $postStr;
            }
        } elseif (isset($_GET["echostr"])) {

            if ($this->checkSignature()) {
                exit($_GET["echostr"]);
            }
            return false;
        }
        if (!$this->checkSignature($encryptStr)) {
            $this->errMsg = 'Interface authentication failed, please use the correct method to call.';
            return false;
        }
        return true;
    }




    /**
     * 验证来自微信服务器
     * @param string $str
     * @return bool
     */
    private function checkSignature($str = '')
    {

        $signature = isset($_GET["msg_signature"]) ? $_GET["msg_signature"] : (isset($_GET["signature"]) ? $_GET["signature"] : '');
        $timestamp = isset($_GET["timestamp"]) ? $_GET["timestamp"] : '';
        $nonce = isset($_GET["nonce"]) ? $_GET["nonce"] : '';
        $tmpArr = array($this->token, $timestamp, $nonce, $str);
        sort($tmpArr, SORT_STRING);
        if (sha1(implode($tmpArr)) == $signature) {
            return true;
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