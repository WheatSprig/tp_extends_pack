<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/9/15
 * Time: 17:54
 */

namespace mikkle\tp_wechat\base;

use mikkle\tp_master\Config;
use mikkle\tp_master\Exception;
use mikkle\tp_wechat\Wechat;
use mikkle\tp_master\Hook;
use mikkle\tp_master\Log;
use mikkle\tp_master\Request;

/**
 * 微信消息接口基类
 * Power: Mikkle
 * Email：776329498@qq.com
 * Class BaseWeApi
 * @package app\center\controller
 */

abstract class BaseWechatApi
{
    protected $app_id;
    //微信参数 建议继承覆盖此参数
    protected $options;
    protected $weObj;
    protected $openid;
    protected $type;
    protected $data;
    protected $fans;
    protected $request;
    //是否开启对接 建议继承覆盖此参数
    protected $valid = true;    //
    protected $isHook = false;  //是否开启钩子

    public function __construct($options = [])
    {
        $this->request = Request::instance();
        $this->options = !empty($options) ? $options : $this->options;
        if (empty($options)&& !empty( Config::get("wechat.default_options_name"))){
            $this->options = Config::get("wechat.".Config::get("wechat.default_options_name"));
        }elseif(is_string($options)&&!empty( Config::get("wechat.$options"))){
            $this->options = Config::get("wechat.$options");
        }
        if(empty($this->options)){
            throw new Exception("微信配置参数错误");
        }
        $this->app_id = $this->options['appid'];
        $this->weObj = Wechat::receive($this->options);
    }


    /**
     * 微信接收主方法
     * Power: Mikkle
     * Email：776329498@qq.com
     */
    public function index()
    {
        try {
            //是否是对接模式
            if ($this->valid == true) {
                $this->weObj->valid();
            }

            //分解数据获得常用字段
            $get_rev = $this->weObj->getRev();

            if (empty($get_rev)) {
                $this->we_dump('false');
            }
            $this->openid = $get_rev->getRevFrom();
            $this->type = $get_rev->getRevType();
            $this->data = $get_rev->getRevData();

            if (empty($this->data)) {
                die("What do you want to do?");
            }

            //用户检测，如果有就存入data,没有则存入数据库
            $fans = $this->hasSaveFans();
            if (!$fans) {
                throw new Exception("获取粉丝信息错误");
            }

            if($this->isHook){
                Hook::listen("wechat_receive", $this->weObj);
            }

            //补充常用相关数据到DATA
            $this->data['appid'] = $this->app_id;
            $this->data['openid'] = $fans['openid'];
            $this->data['nickname'] = $fans['nickname'];
            $this->fans = $fans;


            //判断消息是否存在
            if ($this->saveWeMessage() === false) {
                $this->weObj->text('处理中，请稍后！')->reply();
                die;
            };
            $reply = [];

            //根据消息类型 获取不同回复内容
            $message = $this->messageTypeHandleCenter();
           // $this->we_dump($message);
            if (!empty($message)) {
                //处理兼容
                if (!is_array($message)) {
                    $reply['message'] = $message;
                } else {
                    $reply = $message;
                }

                if (!isset($reply['type'])){
                    $reply['type'] = 'text';
                    $reply['message'] = isset($reply['message']) ? $reply['message'] : '知道吗!我爱你!请原谅我不知道如何回答你的问题!';
                }

                if(is_array($reply['message'])&&$reply['type'] == 'text'){
                    $reply['message']=json_encode($reply['message']);
                }

                switch($reply['type']){
                    case "text":
                        $this->weObj->text($reply['message'])->reply();
                        break;
                    case "news":
                        $this->weObj->news($reply['message'])->reply();
                        break;
                    case "image":
                        $this->weObj->image($reply['message'])->reply();
                        break;
                    case "":
                      //  $this->weObj->news($reply)->reply();
                        break;
                    default;
                        $this->weObj->$reply['type']($reply['message'])->reply();

                }
                return;
            //    $this->we_dump($reply);
            }
            die;

        } catch (Exception $e) {
            Log::error($e->getMessage());
            $this->weObj->text("本宝宝生病了,请原谅我不知道如何回答你的问题!")->reply();
            die();
        }
    }

    /**
     * 默认文本消息回复内容
     *
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return string
     */
    protected function returnMessageText()
    {
        return '发送的是' . $this->weObj->getRevContent();
    }

    /**
     * 默认图片信息回复内容
     *
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return array
     */
    protected function returnMessageImage()
    {
        $newsarray = [
            [
                'Title' => '你的图片发送成功',
                'Description' => '这是你发的图片吧',
                'PicUrl' => $this->data['PicUrl'],
                'Url' => $this->request->domain(),
            ],
        ];
        $reply = ['type' => 'news', 'message' => $newsarray];
        return $reply;
    }

    /**
     * 默认语音信息回复内容处理方法
     *
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return string
     */
    protected function returnMessageVoice()
    {
        if (!empty($this->data['Content'])) {
            $keyword = $this->data['Content'];  //TODO::语音识别 语义分析
        } else {
            $keyword = $this->data->type;
        }
        $reply = '发送的语音翻译是:' . $keyword;
        return $reply;
    }

    /**
     * 默认音乐信息回复列表处理方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return string
     */
    protected function returnMessageMusic()
    {
        return '发送的是音乐';
    }

    /**
     * 默认视频信息回复处理方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return string
     */
    protected function returnMessageVideo()
    {
        return '发送的是视频';
    }

    /**
     * 默认发送地理位置回复信息处理方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return string
     */
    protected function returnMessageLocation()
    {
        return '发送的是地理位置';
    }

    /**
     * 默认链接回复内容处理方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return string
     */
    protected function returnMessageLink()
    {
        return '发送的是链接';
    }

    /**
     * 默认关注回复处理方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return array
     */
    protected function returnEventSubscribe()
    {
        return ['type' => 'text', 'message' => '感谢你的关注'];
    }

    /**
     * 默认取消关注回复处理方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return array
     */
    protected function returnEventUnsubscribe()
    {
        return ['type' => 'text', 'message' => '期待你的再次关注'];
    }

    /**
     * 默认扫码事件处理方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return array
     */
    protected function returnEventScan()
    {
        $message = $this->weObj->getRevEvent();
        if (isset($message["key"])) {
            return ['type' => 'text', 'message' => "你的扫码成功,扫码内容:{$message['key']}"];
        }
        return ['type' => 'text', 'message' => '你的扫码成功'];
    }

    /**
     * 默认上报地理事件处理方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return array
     */
    protected function returnEventLocation()
    {
        return ['type' => 'text', 'message' => '地理位置上报成功'];
    }

    /**
     * 默认点击菜单关键字处理方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return array
     */
    protected function returnEventClick()
    {
        $message = $this->weObj->getRevEvent();
        if (isset($message["key"])) {
            return ['type' => 'text', 'message' => "你的点击成功,点击关键字内容:{$message['key']}"];
        }
        return ['type' => 'text', 'message' => '你的点击成功'];
    }

    /**
     * 菜单调用扫码事件处理方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $type
     * @return array
     */
    protected function returnEventMenuScan($type)
    {
        $message = $this->weObj->getRevEvent();
        switch ($type) {
            case "push":  //推送
                if (isset($message["key"])) {
                    return ['type' => 'text', 'message' => "你的点击菜单扫码,关键字内容:{$message['key']}"];
                }
                return ['type' => 'text', 'message' => '你的点击菜单扫码成功'];
                break;
            case "waitmsg":
                //等候

                if (isset($message["key"])) {
                    return ['type' => 'text', 'message' => "你的点击菜单扫码等待中,关键字内容:{$message['key']}"];
                }
                return ['type' => 'text', 'message' => '你的点击菜单扫码等待中'];
                break;
            default:
                ;
        }
    }

    /**
     * 通过菜单上传图片处理方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $type // sys 系统 | photo  相册 | weixin 微信相册
     * @return array
     */
    protected function returnEventMenuPic($type)
    {
        $message = $this->weObj->getRevEvent();
        if (isset($message["key"])) {
            return ['type' => 'text', 'message' => "你的通过菜单上传图片成功,图片方式{$type},关键字内容:{$message['key']}"];
        }
        return ['type' => 'text', 'message' => "你的通过菜单上传图片成功,图片方式{$type}"];
    }


    /**
     * 菜单上报地理事件
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return string
     */
    protected function returnEventMenuLocation()
    {
        return $reply['message'] = 'success';
    }

    /**
     * 群发成功推送结果处理方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return string
     */
    protected function returnEventSendMass()
    {
        return $reply['message'] = 'success';
    }

    /**
     * 模版消息接收结果处理方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return string
     */
    protected function returnEventSendTemplate()
    {
        return $reply['message'] = 'success';
    }

    /**
     * 客服事件处理方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $type //create | close |  switch
     * @return string
     */
    protected function returnEvenKfSession($type)
    {
        return $reply['message'] = 'success';
    }

    /**
     * 卡类时间 处理方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $type //pass | notpass | user_get |user_dell
     * @return string
     */
    protected function returnEventCard($type)
    {
        return $reply['message'] = 'success';
    }

    /**
     * wifi连一连处理方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return string
     */
    protected function returnEventWifiConnected()
    {
        return $reply['message'] = 'success';
    }

    /**
     * 其他未知事件处理方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return string
     */
    protected function returnEventOthers()
    {
        return $reply['message'] = 'success';
    }


    /**
     * 周围摇一摇事件处理方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return string
     */
    protected function returnEventAroundUserSnake()
    {
        return $reply['message'] = 'success';
    }


    /**
     * 处理微信消息总处理入口
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return array|string
     */
    private function messageTypeHandleCenter()
    {
        switch ($this->type) {
            case WeChatCode::MSGTYPE_TEXT:
                if ($this->isHook) {
                    Hook::listen("wechat_message_text", $this->weObj);
                }
                return $this->returnMessageText();
                break;
            case WeChatCode::MSGTYPE_IMAGE:
                if ($this->isHook) {
                    Hook::listen("wechat_message_image", $this->weObj);
                }
                return $this->returnMessageImage();
                break;
            case WeChatCode::MSGTYPE_VOICE:
                if ($this->isHook) {
                    Hook::listen("wechat_message_voice", $this->weObj);
                }
                return $this->returnMessageVoice();
                break;
            case WeChatCode::MSGTYPE_MUSIC:
                if ($this->isHook) {
                    Hook::listen("wechat_message_music", $this->weObj);
                }
                return $this->returnMessageMusic();
                break;

            case WeChatCode::MSGTYPE_VIDEO:
                if ($this->isHook) {
                    Hook::listen("wechat_message_video", $this->weObj);
                }
                return $this->returnMessageVideo();
                break;
            case WeChatCode::MSGTYPE_LOCATION:
                if ($this->isHook) {
                    Hook::listen("wechat_message_location", $this->weObj);
                }
                return $this->returnMessageLocation();
                break;
            case WeChatCode::MSGTYPE_LINK:
                if ($this->isHook) {
                    Hook::listen("wechat_message_link", $this->weObj);
                }
                return $this->returnMessageLink();
                break;
            case WeChatCode::MSGTYPE_EVENT:
                //事件处理中心
                $reply = $this->messageEventHandleCenter();
                break;
            default:
                $reply = ['type' => 'text', 'message' => 'success'];
                break;

        }
        return $reply;
    }

    /**
     * 处理微信事件消息处理中心
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return array|string
     */
    private function messageEventHandleCenter()
    {
        try {
            $reply = "";
            switch ($this->data['Event']) {
                case WeChatCode::EVENT_SUBSCRIBE:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_subscribe", $this->weObj);
                    }
                    $reply = $this->returnEventSubscribe();
                    break;
                case WeChatCode::EVENT_UNSUBSCRIBE:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_unsubscribe", $this->weObj);
                    }
                    $reply = $this->returnEventUnsubscribe();
                    break;
                case WeChatCode::EVENT_SCAN:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_scan", $this->weObj);
                    }
                    $reply = $this->returnEventScan();
                    break;
                case WeChatCode::EVENT_LOCATION:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_location", $this->weObj);
                    }
                    $reply = $this->returnEventLocation();
                    break;
                case WeChatCode::EVENT_MENU_CLICK:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_click", $this->weObj);
                    }
                    $reply = $this->returnEventClick();
                    break;
                case WeChatCode::EVENT_MENU_SCAN_PUSH:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_menu_scan_push", $this->weObj);
                    }
                    $reply = $this->returnEventMenuScan("push");
                    break;
                case WeChatCode::EVENT_MENU_SCAN_WAITMSG:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_menu_scan_waitmsg", $this->weObj);
                    }
                    $reply = $this->returnEventMenuScan("waitmsg");
                    break;
                case WeChatCode::EVENT_MENU_PIC_SYS:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_menu_pic_sys", $this->weObj);
                    }
                    $reply = $this->returnEventMenuPic("sys");
                    break;
                case WeChatCode::EVENT_MENU_PIC_PHOTO:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_menu_pic_photo", $this->weObj);
                    }
                    $reply = $this->returnEventMenuPic("photo");
                    break;
                case WeChatCode::EVENT_MENU_PIC_WEIXIN:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_menu_pic_weixin", $this->weObj);
                    }
                    $reply = $this->returnEventMenuPic("weixin");
                    break;
                case WeChatCode::EVENT_MENU_LOCATION:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_menu_location", $this->weObj);
                    }
                    $reply = $this->returnEventMenuLocation();
                    break;
                case WeChatCode::EVENT_SEND_MASS:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_send_mass", $this->weObj);
                    }
                    $reply = $this->returnEventSendMass();

                    break;
                case WeChatCode::EVENT_SEND_TEMPLATE:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_send_template", $this->weObj);
                    }
                    $reply = $this->returnEventSendTemplate();
                    //模板消息发送成功
                    break;
                case WeChatCode::EVENT_KF_SESSION_CREATE:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_kf_create", $this->weObj);
                    }
                    $reply = $this->returnEvenKfSession("create");
                    break;
                case WeChatCode::EVENT_KF_SESSION_CLOSE:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_kf_close", $this->weObj);
                    }
                    $reply = $this->returnEvenKfSession("close");
                    break;
                case WeChatCode::EVENT_KF_SESSION_SWITCH:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_kf_switch", $this->weObj);
                    }
                    $reply = $this->returnEvenKfSession("switch");
                    break;
                case WeChatCode::EVENT_CARD_PASS:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_card_pass", $this->weObj);
                    }
                    $reply = $this->returnEventCard("notpass");

                    break;
                case WeChatCode::EVENT_CARD_NOTPASS:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_card_notpass", $this->weObj);
                    }
                    $reply = $this->returnEventCard("notpass");
                    break;
                case WeChatCode::EVENT_CARD_USER_GET:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_card_user_get", $this->weObj);
                    }
                    $reply = $this->returnEventCard("user_get");
                    break;
                case WeChatCode::EVENT_CARD_USER_DEL:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_menu_user_del", $this->weObj);
                    }
                    $reply = $this->returnEventCard("user_dell");
                    break;
                case WeChatCode::EVENT_WIFI_CONNECTED :
                    if ($this->isHook) {
                        Hook::listen("wechat_event_wifi_connected", $this->weObj);
                    }
                    $reply = $this->returnEventWifiConnected();
                    break;
                case WeChatCode::EVENT_SHAKEAROUND_USER_SHAKE :
                    if ($this->isHook) {
                        Hook::listen("wechat_event_around_user_shake", $this->weObj);
                    }
                    $reply = $this->returnEventAroundUserSnake();
                    break;
                default:
                    if ($this->isHook) {
                        Hook::listen("wechat_event_others", $this->weObj);
                    }
                    $reply = $this->returnEventOthers();
                    break;

                    break;
            }
            return $reply;
        } catch (Exception $e) {
            return "回复事件信息出错了 {$e->getMessage()}";
        }

    }

    /**
     * 保存信息方法
     * 建议根据你的需求重写此方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return bool
     */
    protected function saveWeMessage()
    {
        return true;
    }

    /**
     * 获取用户信息
     * 建议根据你的需求重写此方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param string $openid
     * @return array|bool
     */
    protected function hasSaveFans($openid = '')
    {
        try {
            $openid = $openid ?: $this->openid;
            if (empty($openid)) {
                return false;
            }
            $fans = $this->weObj->getUserInfo($openid);
            return $fans;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }


    /**
     * 调试内容给客户端
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $value
     */
    protected function we_dump($value)
    {
        ob_start();
        var_dump($value);
        $back = ob_get_clean();
        $this->weObj->text($back)->reply();
        ob_end_flush();
        die;
        return;
    }


}
