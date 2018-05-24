<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/9/11
 * Time: 15:21
 */

namespace mikkle\tp_wechat\src;


use mikkle\tp_wechat\base\WechatBase;
use mikkle\tp_wechat\support\Curl;

class Custom extends WechatBase
{

    /** 多客服相关地址 */
    const CUSTOM_SERVICE_GET_RECORD = '/customservice/getrecord?';
    const CUSTOM_SERVICE_GET_KFLIST = '/customservice/getkflist?';
    const CUSTOM_SERVICE_GET_ONLINEKFLIST = '/customservice/getonlinekflist?';
    const CUSTOM_SESSION_CREATE = '/customservice/kfsession/create?';
    const CUSTOM_SESSION_CLOSE = '/customservice/kfsession/close?';
    const CUSTOM_SESSION_SWITCH = '/customservice/kfsession/switch?';
    const CUSTOM_SESSION_GET = '/customservice/kfsession/getsession?';
    const CUSTOM_SESSION_GET_LIST = '/customservice/kfsession/getsessionlist?';
    const CUSTOM_SESSION_GET_WAIT = '/customservice/kfsession/getwaitcase?';
    const CS_KF_ACCOUNT_ADD_URL = '/customservice/kfaccount/add?';
    const CS_KF_ACCOUNT_UPDATE_URL = '/customservice/kfaccount/update?';
    const CS_KF_ACCOUNT_DEL_URL = '/customservice/kfaccount/del?';
    const CS_KF_ACCOUNT_UPLOAD_HEADIMG_URL = '/customservice/kfaccount/uploadheadimg?';


    public function  __construct(array $option)
    {
        parent::__construct($option);
        $this->getToken();
    }

    /**
     * 获取多客服会话记录
     * @param array $data 数据结构 {"starttime":123456789,"endtime":987654321,"openid":"OPENID","pagesize":10,"pageindex":1,}
     * @return bool|array
     */
    public function getCustomServiceMessage($data)
    {
        if (!$this->access_token  || empty($data)) {
            return false;
        }

        $curl_url = self::API_URL_PREFIX . self::CUSTOM_SERVICE_GET_RECORD ."access_token={$this->access_token}";
        return $this->returnPostResult($curl_url,$data,__FUNCTION__, func_get_args());
    }

    /**
     * 获取多客服客服基本信息
     *
     * @return bool|array
     */
    public function getCustomServiceKFlist()
    {
        if (!$this->access_token ) {
            return false;
        }

        $curl_url = self::API_URL_PREFIX . self::CUSTOM_SERVICE_GET_KFLIST ."access_token={$this->access_token}";
        return $this->returnGetResult($curl_url,__FUNCTION__, func_get_args());
    }

    /**
     * 获取多客服在线客服接待信息
     *
     * @return bool|array
     */
    public function getCustomServiceOnlineKFlist()
    {
        if (!$this->access_token ) {
            return false;
        }


        $curl_url = self::API_URL_PREFIX . self::CUSTOM_SERVICE_GET_ONLINEKFLIST ."access_token={$this->access_token}";
        return $this->returnGetResult($curl_url,__FUNCTION__, func_get_args());
    }

    /**
     * 创建指定多客服会话
     * @tutorial 当用户已被其他客服接待或指定客服不在线则会失败
     * @param string $openid //用户openid
     * @param string $kf_account //客服账号
     * @param string $text //附加信息，文本会展示在客服人员的多客服客户端，可为空
     * @return bool|array
     */
    public function createKFSession($openid, $kf_account, $text = '')
    {
        if (!$this->access_token  || empty($openid) || empty($kf_account)) {
            return false;
        }
        $data = ["openid" => $openid, "kf_account" => $kf_account];
        if(!empty($text)){
            $data["text"] = $text;
        }

        $curl_url = self::API_BASE_URL_PREFIX . self::CUSTOM_SESSION_CREATE ."access_token={$this->access_token}";
        return $this->returnPostResult($curl_url,$data,__FUNCTION__, func_get_args());
    }

    /**
     * 关闭指定多客服会话
     * @tutorial 当用户被其他客服接待时则会失败
     * @param string $openid //用户openid
     * @param string $kf_account //客服账号
     * @param string $text //附加信息，文本会展示在客服人员的多客服客户端，可为空
     * @return bool | array            //成功返回json数组
     * {
     *   "errcode": 0,
     *   "errmsg": "ok",
     * }
     */
    public function closeKFSession($openid, $kf_account, $text = '')
    {
        if (!$this->access_token || empty($openid) || empty($kf_account)) {
            return false;
        }
        $data = ["openid" => $openid, "kf_account" => $kf_account];
        if ($text) {
            $data["text"] = $text;
        }

        $curl_url = self::API_BASE_URL_PREFIX . self::CUSTOM_SESSION_CLOSE . "access_token={$this->access_token}";
        return $this->returnPostResult($curl_url, $data, __FUNCTION__, func_get_args());
    }

    /**
     * 获取用户会话状态
     * @param string $openid //用户openid
     * @return bool | array            //成功返回json数组
     * {
     *     "errcode" : 0,
     *     "errmsg" : "ok",
     *     "kf_account" : "test1@test",    //正在接待的客服
     *     "createtime": 123456789,        //会话接入时间
     *  }
     */
    public function getKFSession($openid)
    {
        if (!$this->access_token  || empty($openid)) {
            return false;
        }

        $curl_url = self::API_BASE_URL_PREFIX . self::CUSTOM_SESSION_GET ."access_token={$this->access_token}". '&openid=' . $openid;
        return $this->returnGetResult($curl_url,__FUNCTION__, func_get_args());
    }

    /**
     * 获取指定客服的会话列表
     * @param string $kf_account //用户openid
     * @return bool | array            //成功返回json数组
     *  array(
     *     'sessionlist' => array (
     *         array (
     *             'openid'=>'OPENID',             //客户 openid
     *             'createtime'=>123456789,  //会话创建时间，UNIX 时间戳
     *         ),
     *         array (
     *             'openid'=>'OPENID',             //客户 openid
     *             'createtime'=>123456789,  //会话创建时间，UNIX 时间戳
     *         ),
     *     )
     *  )
     */
    public function getKFSessionlist($kf_account)
    {
        if (!$this->access_token  || empty($kf_account)) {
            return false;
        }

        $curl_url = self::API_BASE_URL_PREFIX . self::CUSTOM_SESSION_GET_LIST ."access_token={$this->access_token}". '&kf_account=' . $kf_account;
        return $this->returnGetResult($curl_url,__FUNCTION__, func_get_args());
    }

    /**
     * 获取未接入会话列表
     * @return bool|array
     */
    public function getKFSessionWait()
    {
        if (!$this->access_token ) {
            return false;
        }

        $curl_url = self::API_BASE_URL_PREFIX . self::CUSTOM_SESSION_GET_WAIT ."access_token={$this->access_token}";
        return $this->returnGetResult($curl_url,__FUNCTION__, func_get_args());

    }

    /**
     * 添加客服账号
     *
     * @param string $account 完整客服账号(账号前缀@公众号微信号，账号前缀最多10个字符)
     * @param string $nickname 客服昵称，最长6个汉字或12个英文字符
     * @param string $password 客服账号明文登录密码，会自动加密
     * @return bool|array
     */
    public function addKFAccount($account, $nickname, $password)
    {
        if (!$this->access_token   || empty($account)|| empty($nickname)|| empty($password)) {
            return false;
        }
        $data = ["kf_account" => $account, "nickname" => $nickname, "password" => md5($password)];

        $curl_url = self::API_BASE_URL_PREFIX . self::CS_KF_ACCOUNT_ADD_URL ."access_token={$this->access_token}";
        return $this->returnPostResult($curl_url,$data,__FUNCTION__, func_get_args());
    }

    /**
     * 修改客服账号信息
     *
     * @param string $account //完整客服账号，格式为：账号前缀@公众号微信号，账号前缀最多10个字符，必须是英文或者数字字符
     * @param string $nickname //客服昵称，最长6个汉字或12个英文字符
     * @param string $password //客服账号明文登录密码，会自动加密
     * @return bool|array
     * 成功返回结果
     * {
     *   "errcode": 0,
     *   "errmsg": "ok",
     * }
     */
    public function updateKFAccount($account, $nickname, $password)
    {
        if (!$this->access_token  || empty($account)|| empty($nickname)|| empty($password)) {
            return false;
        }
        $data = ["kf_account" => $account, "nickname" => $nickname, "password" => md5($password)];

        $curl_url = self::API_BASE_URL_PREFIX . self::CS_KF_ACCOUNT_UPDATE_URL ."access_token={$this->access_token}";
        return $this->returnPostResult($curl_url,$data,__FUNCTION__, func_get_args());
    }

    /**
     * 删除客服账号
     * @param string $account 完整客服账号(账号前缀@公众号微信号，账号前缀最多10个字符)
     * @return bool|array
     */
    public function deleteKFAccount($account)
    {
        if (!$this->access_token  || empty($account)) {
            return false;
        }


        $curl_url = self::API_BASE_URL_PREFIX . self::CS_KF_ACCOUNT_DEL_URL ."access_token={$this->access_token}" . '&kf_account=' . $account;
        return $this->returnGetResult($curl_url,__FUNCTION__, func_get_args());
    }

    /**
     * 上传客服头像
     * @param string $account 完整客服账号(账号前缀@公众号微信号，账号前缀最多10个字符)
     * @param string $file_url 头像文件完整路径,如：'D:\user.jpg'。头像文件必须JPG格式，像素建议640*640
     * @return bool|array
     */
    public function setKFHeadImg($account, $file_url)
    {
        if (!$this->access_token  || empty($account)|| empty($file_url)) {
            return false;
        }

        $data['media'] = Curl::getCurlFileMedia($file_url);
        $curl_url = self::API_BASE_URL_PREFIX . self::CS_KF_ACCOUNT_UPLOAD_HEADIMG_URL . "access_token={$this->access_token}&type={$type}";
        $result = Curl::CurlFile($curl_url, $data);
        //dump($result);
        return $this->resultJsonWithRetry($result,__FUNCTION__, func_get_args());

    }


}