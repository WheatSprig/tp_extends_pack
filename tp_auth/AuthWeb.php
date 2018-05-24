<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2017/06/13
 * Time: 15:01
 */

namespace mikkle\tp_auth;
use app\base\model\system\SystemAdmin;
use app\base\model\system\SystemNode;
use mikkle\tp_master\Cache;
use mikkle\tp_master\Config;
use mikkle\tp_master\Cookie;
use mikkle\tp_master\Request;
use mikkle\tp_master\Session;
use mikkle\tp_model\ModelTools;

class AuthWeb
{
    static protected $instance;
    public $isLogin;
    protected $loginType;
    protected $uuid;
    protected $adminInfo;
    protected $adminNodeList;
    protected $request;
    protected $module;
    protected $controller;
    protected $action;
    protected $publicWeb=[];
    protected $error;
    public $isSuperAdmin;

    protected function __construct($options=[])
    {
        switch (true) {
            case (!isset($options["type"])):
                $this->loginType = "session";
                break;
            case ($options["type"] == "session"):
                $this->loginType = "session";
                break;
            case ($options["type"] == "redis"):
                $this->loginType = "redis";
                break;

            default:
                $this->loginType = "session";
        }
        //公共页面 必须小写
        $this->publicWeb =[
            "main.index"=>[
                "index"=>true,
                "main"=>true,
                "getmenujson"=>true,
            ],
            "main.login"=>[

            ],
        ];
        if (isset($options["public_web"])&&is_array($options["public_web"])){
            $this->publicWeb = array_merge($this->publicWeb ,$options["public_web"] )  ;
        }
        $this->request = Request::instance();
        $this->module = $this->request->module();
        $this->controller = $this->request->controller();
        $this->action = $this->request->action();
    }


    public function checkNodeAuth()
    {
        if ($this->checkIsSuperAdmin()) {
            return true;
        } else {
            //跳过登录系列的公共页面检测以及主页权限
            if ($this->checkPublicWeb()) {
                return true;
            }
            $node_info = $this->getNodeInfo();
            // dump($node_info);
            if (empty($node_info)) {
                $this->error = '此页面访问权限未开放，请联系管理员';
                return false;
            }
            if ($node_info['auth_grade'] > 0) {
                return $this->checkUserNodeAuthByNodeGuId($node_info['guid']);
            }
            return true;
        }
    }



    public function getUuid(){
        return $this->uuid;
    }

    public function getAdminInfo(){
        return $this->adminInfo;
    }

    public function getError(){
        return $this->error;
    }

    protected function checkUserNodeAuthByNodeGuId($Guid)
    {

        //获取用户 权限列表
        $this->checkAdminRoleMenuList();

        if (!in_array($Guid, $this->adminNodeList)) {
            $this->error="你没有权限，请联系系统管理员";
            return false;
        }else{
            return true;
        }
    }

    protected function getNodeInfo(){

        $node = new SystemNode();
        return $node->getNodeInfo($this->module,$this->controller,$this->action);
    }

    protected function checkAdminRoleMenuList($refresh=false)
    {
        switch (true) {
            case (!empty($this->adminNodeList)):
                return true;
                break;
            case ($refresh || !Cache::has("admin_node_list_{$this->uuid}")):
                $this->adminNodeList = $this->getAdminRoleNodeList($this->uuid);
                Cache::set("admin_node_list_{$this->uuid}", $this->adminNodeList);
                break;
            case (Cache::has("admin_node_list_{$this->uuid}")):
                $this->adminNodeList = Cache::get("admin_node_list_{$this->uuid}");
                break;
            default:
        }
    }

    protected function getAdminRoleNodeList($uuid){
        $admin = new SystemAdmin();
        return $admin->getAdminRoleNodeList($uuid);
    }

    protected function checkPublicWeb()
    {
        return isset($this->publicWeb[strtolower($this->controller)][strtolower($this->action)])? true : false ;
    }

    protected function checkIsSuperAdmin()
    {
        if($this->isSuperAdmin){
                return true;
        }
        return false;
    }


    public function checkLoginGlobal()
    {
        if ($this->isLogin && $this->uuid && $this->adminInfo){
            return $this->isLogin;
        }
        switch ($this->loginType) {
            case 1;
            case "session";
                $this->uuid = Session::get('uuid', 'Global');
                $this->adminInfo = Session::get('admin_info', 'Global');
                if ($this->uuid && $this->adminInfo) {
                    $this->isLogin = true;
                }
                break;
            case 2;
            case "cache";
                $session_id_check = Cookie::get("session_id");
                $this->uuid = Cache::get("uuid_{$session_id_check}");
                $this->adminInfo = Cache::get("admin_info_{$session_id_check}");
                if ($this->uuid && $this->adminInfo) {
                    $this->isLogin = true;
                }
                //刷新 缓存有效期
                Cache::set("uuid_{$session_id_check}", $this->uuid);
                Cache::set("admin_info_{$session_id_check}", $this->adminInfo);
                break;
            case 3:
            case "redis":

                break;
        }
        if($this->adminInfo){
            if (isset($this->adminInfo["grade"]) &&$this->adminInfo["grade"]== 0 ){
                $this->isSuperAdmin = true;
                return true;
            }
        }

        return $this->isLogin;

    }

    public function setLoginGlobal($admin_info = [], $login_code = 0)
    {
        $set_success = false ;
        if ($admin_info&&isset($admin_info["uuid"])) {
            switch ($this->loginType) {
                case 1:
                case "session":
                    Session::set('admin_info', $admin_info, 'Global');
                    Session::set('uuid', $admin_info['uuid'], 'Global');
                    if ((Session::has("uuid", "Global"))) {
                        $set_success = true;
                    }
                    break;
                case 2:
                case "cache":
                    $session_id = ModelTools::createUuid("SN");
                    Cookie::set("session_id", $session_id);
                    Cache::set("admin_info_$session_id", $admin_info);
                    Cache::set("uuid_$session_id", $admin_info['uuid']);
                    $session_id_check = Cookie::get("session_id");
                    if ((Cache::get("uuid_{$session_id_check}"))) {
                        $set_success = true;
                    }
                    break;
                case 3:case "redis":
                break;
            }
        }
        if (!$set_success) return false;
        //保存登录信息
        $this->uuid = $admin_info['uuid'];
        $this->adminInfo = $admin_info;
        $this->isLogin = true ;
        if($this->adminInfo) {
            if (isset($this->adminInfo["grade"]) && $this->adminInfo["grade"] == 0) {
                $this->isSuperAdmin = true;
                return true;
            }
        }
        //强制刷新权限信息
        $this->checkAdminRoleMenuList(true);
        return true;
    }

    public function logoutGlobal(){
        switch ($this->loginType) {
            case 1:
            case "session":
                Session::delete('uuid', 'Global');
                Session::delete('admin_info', 'Global');
                break;
            case 2:
            case "cache":
                $session_id_check = Cookie::get("session_id");
                Cache::rm("uuid_{$session_id_check}");
                Cache::rm("admin_info_{$session_id_check}");
                Cookie::delete("session_id");
                break;
            case 3:case "redis":


            break;
        }
        $this->adminInfo = null;
        $this->uuid = null;
        $this->isSuperAdmin = null;
        return true;
    }


    static public function instance($options=[]){
        $options =self::getOptions($options);
        $sn = self::getSn($options);
        if(isset(self::$instance[$sn])){
            return self::$instance[$sn];
        }else{
            return self::$instance[$sn]= new static($options);
        }
    }

    static protected function getOptions($options=[]){

        if (empty($options)&& !empty( Config::get("auth.default_options_name"))){
            $name = "auth".".".Config::get("auth.default_options_name");
            $options = Config::get("$name");
        }elseif(is_string($options)&&!empty( Config::get("auth.$options"))){
            $options = Config::get("auth.$options");
        }
        return $options;
    }

    static protected function getSn($options){
        return md5(serialize(ksort($options)));
    }

}