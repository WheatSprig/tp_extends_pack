<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/11/27
 * Time: 13:53
 */

namespace mikkle\tp_master;


use think\Facade;
/**
 * @see \think\Log
 * @mixin \think\Log
 * @method \think\Log init(array $config = []) static 日志初始化
 * @method mixed getLog(string $type = '') static 获取日志信息
 * @method \think\Log record(mixed $msg, string $type = 'info', array $context = []) static 记录日志信息
 * @method \think\Log clear() static 清空日志信息
 * @method \think\Log key(string $key) static 当前日志记录的授权key
 * @method bool check(array $config) static 检查日志写入权限
 * @method bool save() static 保存调试信息
 * @method void write(mixed $msg, string $type = 'info', bool $force = false) static 实时写入日志信息
 * @method void log(string $level,mixed $message, array $context = []) static 记录日志信息
 * @method void emergency(mixed $message, array $context = []) static 记录emergency信息
 * @method void alert(mixed $message, array $context = []) static 记录alert信息
 * @method void critical(mixed $message, array $context = []) static 记录critical信息
 * @method void error(mixed $message, array $context = []) static 记录error信息
 * @method void warning(mixed $message, array $context = []) static 记录warning信息
 * @method void notice(mixed $message, array $context = []) static 记录notice信息
 * @method void info(mixed $message, array $context = []) static 记录info信息
 * @method void debug(mixed $message, array $context = []) static 记录debug信息
 * @method void sql(mixed $message, array $context = []) static 记录sql信息
 */
class Log extends Facade
{
    protected static function getFacadeClass()
    {
        return 'think\Log';
    }

}