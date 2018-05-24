<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/9/15
 * Time: 17:57
 */

namespace mikkle\tp_wechat;


use mikkle\tp_wechat\base\BaseWechatApi;

/**
 * 微信信息同送接口操作类
 * 推荐继承或者复制本类进行修改即可
 * Power: Mikkle
 * Email：776329498@qq.com
 * Class WechatApi
 * @package mikkle\tp_wechat
 */
class WechatApi extends BaseWechatApi
{
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
     * 默认音乐信息回复内容处理方法
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
     * 卡类事件 处理方法
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
     * 其他未知事件处理方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return string
     */
    protected function returnEventOthers()
    {
        return $reply['message'] = 'success';
    }




}