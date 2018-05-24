<?php
/**
 * Created by PhpStorm.
 * User: 77632
 * Date: 2017/12/8
 * Time: 10:23
 */

namespace mikkle\tp_master;


use think\Facade;
/**
 * @see \think\Cache
 * @mixin \think\Cache
 * @method \think\cache\Driver connect(array $options = [],  $name = false) static 连接缓存
 * @method \think\cache\Driver init(array $options = []) static 初始化缓存
 * @method \think\cache\Driver store(string $name = '') static 切换缓存类型
 * @method bool has(string $name) static 判断缓存是否存在
 * @method mixed get(string $name,  $default = false) static 读取缓存
 * @method mixed pull(string $name) static 读取缓存并删除
 * @method mixed set(string $name, mixed $value, int $expire = null) static 设置缓存
 * @method mixed remember(string $name, mixed $value, int $expire = null) static 如果不存在则写入缓存
 * @method mixed inc(string $name, int $step = 1) static 自增缓存（针对数值缓存）
 * @method mixed dec(string $name, int $step = 1) static 自减缓存（针对数值缓存）
 * @method bool rm(string $name) static 删除缓存
 * @method bool clear(string $tag = null) static 清除缓存
 * @method mixed tag(string $name, mixed $keys = null, bool $overlay = false) static 缓存标签
 * @method object handler() static 返回句柄对象，可执行其它高级方法
 */
class Cache extends Facade
{
    protected static function getFacadeClass()
    {
        return 'think\Cache';
    }

}