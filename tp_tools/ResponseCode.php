<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/07/28
 * Time: 11:23
 */

namespace mikkle\tp_tools;


use mikkle\tp_master\Config;

/**
 *
 * ShowCode::jsonCode(1001)
 * Power: Mikkle
 * Email：776329498@qq.com
 * Class ShowCode
 * @package mikkle\tp_tools
 */
class ResponseCode
{
    /**
     * 返回data值的Code码
     */
    static protected $successCode = [
        "1001",
        "1066", //充值信息通知
        "1077", //获取支付地址错误
        "1088",  //补录取车  通知指定收费员
        "1099",  //有付款的账单  通知当前站点所有的收费员
        "1111", //消息通知
    ];

    /**
     * 定义返回码的数组名称
     */
    static protected $returnCodeName=[
        "codeName"=>"response_code",
        "dataName"=>"response_data",
        "messageName"=>"response_msg",
    ];

    /**
     * 定义返回码的massage名称
     */
    static protected $returnCode=[
        '1001' => '操作成功',
        '1002' => '你想做什么呢', //非法的请求方式 非ajax
        '1003' => '请求参数错误', //如参数不完整,类型不正确
        '1004' => '请先登陆再访问', //未登录 或者 未授权
        '1005' => '请求授权不符', ////非法的请求  无授权查看
        '1006' => '数据加载失败', //
        '1007' => '数据修改失败', //
        '1008' => '系统错误', //
        '1009' => '签名错误', //
        '1010' => '数据不存在', //
        '1020' => '验证码输入不正确', //
        '1021' => '用户账号或密码错误', //
        '1022' => '用户账号被禁用', //
        '1030' => '数据操作失败', //
        '1044' => '该车辆在其他停车场有在停车辆数据', //
        '1077' => '获取支付地址错误', //
        '1099' => '账单已经结清,', //
    ];

    /**
     * 默认的返回码
     */
    static protected $defaultCode = [
        'code' => '1099',
        'msg' => '未知服务器消息',
        'data' => [],
    ];


    /**
     * 返回码主方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param string $code 返回码
     * @param array $data 返回值
     * @param string $msg 返回消息的说明
     * @param array $append 附加信息
     * @return array
     */
    static public function code($code = '', $data = [], $msg = '' , array $append=[]){
        $returnCode = self::$defaultCode;
        if (empty($code)) {
            return $returnCode;
        }else{
            $returnCode["code"] = $code;
        }
        if (in_array($code,self::$successCode) || isset(self::$successCode[$code])){
            if (is_string($data)||is_numeric($data )){
                $returnCode["data"]  = (string)$data;
            }elseif(is_array($data )){
                foreach ( $data as $i=>$value){
                    if (is_string($value)||is_numeric($value )){
                        $returnCode["data"] [$i] = (string)$value;
                    }else{
                        $returnCode["data"] [$i] = $value;
                    }
                }
            }
        }
        if(!empty($msg)){
            $returnCode['msg'] = $msg;
        }else if (isset(self::$returnCode[$code]) ) {
            $returnCode['msg'] = self::$returnCode[$code];
        }
        $return = [
            self::$returnCodeName["codeName"] => "{$returnCode["code"]}",
            self::$returnCodeName["dataName"] => $returnCode["data"],
            self::$returnCodeName["messageName"] => (string)$returnCode["msg"],
        ];
        if (!empty($append)&& is_array($append)){
            $return=array_merge($return,$append);
        }
        if(empty( $return[self::$returnCodeName["dataName"] ] )){
            unset( $return[self::$returnCodeName["dataName"] ]);
        }
        return $return;

    }

    /**
     * 别名方法 无data返回值
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param string $code
     * @param string $msg
     * @param array $append
     * @return array
     */
    static public function codeWithoutData($code = '', $msg = '',array $append=[]){
        return self::code($code,[],$msg,$append);
    }

    /**
     * title 别名方法 返回json格式返回码
     * description jsonCode
     * User: Mikkle
     * QQ:776329498
     * @param string $code
     * @param array $data
     * @param string $msg
     * @param array $append
     * @return array
     */
    static public function jsonCode($code = '', $data = [], $msg = '', array $append=[]){
        self::returnJsonType();
        return self::code($code,$data,$msg,$append);
    }
    /**
     * 别名方法 返回json格式返回码 无data值
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param string $code
     * @param string $msg
     * @param array $append
     * @return array
     */
    static public function jsonCodeWithoutData($code = '', $msg = '' ,array $append=[]){
        self::returnJsonType();
        return self::code($code,[],$msg,$append);
    }




    static public function returnJsonType(){
        Config::set("default_return_type","json");
    }



}