<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/3/30
 * Time: 14:19
 */

namespace mikkle\tp_api;


use mikkle\tp_master\Config;
use think\Controller;

class EmptyController extends Controller
{
    public function _initialize()
    {

    }

    public function _empty(){
        $controller = $this->request->controller();
        $action = $this->request->action();
        $config = Config::get("{$controller}.{$action}");
        if (!empty($config) && is_array( $config) && isset( $config['action_name'] )) {
            $action_name = $config['action_name'];
            if (method_exists($this, $action_name)) {
                return $this->$action_name($action);
            } else {
                $this->error('你配置的方法不存在');
            }
        } else {
            $this->error('你配置的参数不存在');
        }
    }


}