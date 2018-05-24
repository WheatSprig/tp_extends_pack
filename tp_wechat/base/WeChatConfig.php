<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/7/25
 * Time: 15:18
 */

namespace mikkle\tp_wechat\base;


class WeChatConfig
{

    protected $options_mysql=[
        //存储公众号数据表名称
        "table_name"=>"mk_we",
        //存储token字段名称
        "token_field_name"=>"token",
        //存储appid字段名称
        "appid_field_name"=>"appid",
        //存储secret字段名称
        "secret_field_name"=>"appsecret",
        //存储encodingaeskey字段名称
        "encodingaeskey_field_name"=>"encodingaeskey",
    ];



}