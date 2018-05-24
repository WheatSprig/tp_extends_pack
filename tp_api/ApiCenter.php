<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/3/30
 * Time: 15:10
 */

namespace mikkle\tp_api;

use mikkle\tp_master\Exception;
use mikkle\tp_master\Request;
use mikkle\tp_master\Log;
use mikkle\tp_master\Loader;
class ApiCenter
{
    protected $parameter;
    protected $append;
    protected $data;
    protected $saveData;
    protected $validate;
    protected $validateClass;
    protected $scene;
    protected $model;
    protected $modelType;
    protected $modelClass;
    protected $updateMap;
    protected $isUpdate;
    protected $allowField = true ;
    protected $pk;
    protected $request;
    protected $error;
    protected $result;
    protected $debug;
    protected static $instance;

    public function __construct($options=[])
    {
        $this->request=Request::instance();
        if(isset($options["data"])){
            $this->data=$options["data"];
        }
        if(isset($options["validate"])){
            $this->validate=$options["validate"];
        }
        if(isset($options["model"])){
            $this->model=$options["model"];
        }
    }

    /**
     * 静态初始化方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $options
     * @return static
     */
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }

    /**
     * 设置获取参数数组
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $parameter
     * @return $this
     * @throws Exception
     */
    public function setParameter(array $parameter=[]){
        switch (true){
            case (!empty($parameter)&&is_array($parameter)):
                $this->parameter=$parameter;
                return $this;
                break;
            default:
                throw new Exception("设置获取参数数组的值出错!");
        }
    }

    /**
     * 单个添加获取参数数组
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $saveName
     * @param string $inputName
     * @param string $dataType
     * @return $this
     * @throws Exception
     */
    public function addParameter($saveName,$inputName="",$dataType=""){
        if (!is_string($saveName)||empty($saveName)){
            throw new Exception("设置参数的类型必须为字符串");
        }
        if (empty($inputName)){
            $inputName=$saveName;
        }
        switch ($dataType) {
            case "string":
            case "s":
                $dataType = "/s";
                break;
            case "int":
            case "d":
                $dataType = "/d";
                break;
            case "array":
            case "a":
                $dataType = "/a";
                break;
            case "bool":
            case "b":
                $dataType = "/b";
                break;
            case "float":
            case "f":
                $dataType = "/f";
                break;
            default:
                $dataType = "";
        }
        $this->parameter["$saveName"]="{$inputName}{$dataType}";
        return $this;

    }

    /**
     * 设置追加的参数
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $append
     * @return $this
     * @throws Exception
     */
    public function setAppend(array $append=[]){
        switch (true){
            case (!empty($append)&&is_array($append)):
                $this->append=$append;
                return $this;
                break;
            default:
                throw new Exception("设置获取参数数组的值出错!");
        }
    }

    /**
     * 添加单个附加数据
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $saveName
     * @param $saveData
     * @return $this
     * @throws Exception
     */
    public function addAppendData($saveName,$saveData){
        if (!is_string($saveName)||!is_numeric($saveData)){
            throw new Exception("设置附加存储值必须使用字符串");
        }
        $this->append[$saveName]=$saveData;
        return $this;
    }

    /**
     * 设置保存的值
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $data
     * @return $this
     * @throws Exception
     */
    public function setData($data=[]){
        switch (true){
            case (!empty($data)&&is_array($data)):
                $this->data=$data;
                return $this;
                break;
            default:
                throw new Exception("设置的值出错!");
        }
    }

    /**
     * 设置验证器或者验证规则[array]
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $validate
     * @return $this
     * @throws Exception
     */
    public function setValidate($validate=[]){
        switch (true){
            case ($validate===false):
                return $this;
                break;
            case (empty($validate)):
                throw new Exception("设置的值不能为空!");
                break;
            case (!is_string($validate)&&!is_array($validate)):
                throw new Exception("设置值的类型必须是字符串!");
                break;
            default:
                $this->validate=$validate;
                return $this;
        }
    }

    /**
     * 设置model
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param string $model
     * @return $this
     * @throws Exception
     */
    public function setModel($model=""){
        switch (true){
            case (empty($model)):
                throw new Exception("设置的值不能为空!");
                break;
            case (!is_string($model)):
                throw new Exception("设置值的类型必须是字符串!");
                break;
            default:
                $this->model=$model;
                return $this;
        }
    }

    public function setModelType($modelType=""){
        switch (true){
            case (empty($modelType)):
                throw new Exception("设置的值不能为空!");
                break;
            case (!is_string($modelType)):
                throw new Exception("设置值的类型必须是字符串!");
                break;
            default:
                $this->modelType=$modelType;
                return $this;
        }
    }


    /**
     * 设置pk
     * @title setPk
     * @description
     * @author Mikkle
     * @url

     * @param $pk
     * @return $this
     */
    public function setPk($pk){
        $this->pk=$pk;
        return $this;
    }

    /**
     * 设置升级条件
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $map
     * @return $this
     */
    public function setUpdateMap($map){
        $this->updateMap=$map;
        $this->isUpdate=true;
        return $this;
    }


    /**
     * 设置可以允许的字段
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $allowField
     * @return $this
     */
    public function setAllowField($allowField){
        $this->allowField=$allowField;
        return $this;
    }

    /**
     * 存储数据 数据存在自动判断添加和升级
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return bool
     */
    public function save(){
        try{
            if (!$this->actionHandle()){
                throw new Exception($this->getError());
            }
            $this->checkPk();
            if ($this->updateMap && isset($this->saveData[$this->pk])){
                if (!isset($this->updateMap[$this->pk] )){
                    unset($this->saveData[$this->pk]);
                }
            }
            if(empty($this->updateMap)&&isset($this->saveData[$this->pk])){
                $this->updateMap[$this->pk] = $this->saveData[$this->pk];
                unset($this->saveData[$this->pk]);
            }
            if ($this->updateMap){
                $this->result = $this->modelClass->allowField($this->allowField)->save($this->saveData,$this->updateMap);
            }else{
                $this->result = $this->modelClass->allowField($this->allowField)->save($this->saveData);
            }
            return $this->result;

        }catch (Exception $e){
            $this->error=$e->getMessage();
            Log::error($e->getMessage());
            return false;
        }
    }

    /**
     * 升级数据
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return bool
     */
    public function update(){
        try{
            if (!$this->actionHandle()){
                throw new Exception($this->getError());
            }
            $this->checkPk();
            if ($this->updateMap && isset($this->saveData[$this->pk])){
                if (!isset($this->updateMap[$this->pk] )){
                    unset($this->saveData[$this->pk]);
                }
            }
            if (empty($this->updateMap)&&!isset($this->saveData[$this->pk])){
                throw new Exception("升级条件缺失");
            }else if(empty($this->updateMap)&&isset($this->saveData[$this->pk])){
                $this->updateMap[$this->pk] = $this->saveData[$this->pk];
                unset($this->saveData[$this->pk]);
            }
            if ($this->updateMap){
                $this->result = $this->modelClass->allowField($this->allowField)->save($this->saveData,$this->updateMap);
                return $this->result;
            }else{
                throw new Exception("升级条件不存在");
            }
        }catch (Exception $e){
            $this->error=$e->getMessage();
            Log::error($e->getMessage());
            return false;
        }
    }


    /**
     * 强制为添加数据(会过滤掉PK字段)
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return bool
     */
    public function add(){
        try{
            if (!$this->actionHandle()){
                throw new Exception($this->getError());
            }
            $this->checkPk();
            if ($this->isUpdate&&isset($this->saveData[$this->pk])){
                unset($this->saveData[$this->pk]);
                $this->isUpdate=false;
            }
            $this->result = $this->modelClass->allowField($this->allowField)->save($this->saveData);
            return $this->result;
        }catch (Exception $e){
            $this->error=$e->getMessage();
            Log::error($e->getMessage());
            return false;
        }
    }

    /**
     * 执行model方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $actionName
     * @return bool
     */
    public function execModelAction($actionName){
        try{
            if (!$this->actionHandle()){
                throw new Exception($this->getError());
            }
            if (!method_exists($this->modelClass, $actionName)) {
                throw new Exception("自定义的方法不存在");
            }
            $this->result = $this->modelClass->$actionName($this->saveData);
            return $this->result;
        }catch (Exception $e){
            $this->error=$e->getMessage();
            Log::error($e->getMessage());
            return false;
        }
    }


    protected function actionHandle()
    {
        try{
            $this->buildSaveData();
            if (!$this->checkSaveDate()){
                return false;
            }
            if (empty($this->modelType)){
                $this->modelClass=Loader::model($this->model);
            }else{
                $this->modelClass=Loader::model($this->model,$this->modelType);
            }
            return true;
        }catch (Exception $e){
            Log::error($e->getMessage());
            return false;
        }
    }

    protected function checkPk(){
        if (empty($this->pk)){
            $this->pk= $this->modelClass->getPK();
        }
        if (isset($this->saveData[$this->pk])){
            $this->isUpdate=true;
        }
    }



    protected function checkSaveDate(){
        switch (true) {
            case (empty($this->saveData)):
                $this->error="要保存的数据为空";
                return false;
                break;
            case (empty($this->validate)):
                return true;
                break;
            case (is_array($this->validate)):
                $this->validateClass = Loader::validate();
                $this->validateClass->rule($this->validate);
                break;
            default:
                if (strpos($this->validate, '.')) {
                    // 支持场景
                    list($this->validate, $this->scene) = explode('.', $this->validate);
                }
                $this->validateClass = Loader::validate($this->validate);
                if (!empty($scene)) {
                    $this->validateClass->scene($scene);
                }
        }
        if (!$this->validateClass->check($this->saveData)) {
            $this->error=$this->validateClass->getError();
            return false;
        } else {
            return true;
        }

    }

    protected function buildSaveData(){

        switch (true) {
            case (empty($this->parameter)&&empty($this->data)):
                $this->saveData=$this->request->param();
                break;
            case (!empty($this->parameter)&&!empty($this->data)):
                throw new Exception("参数和指定值不可同时设置,容许指定请使用append方法");
                break;
            case ($this->parameter):
                $this->saveData = $this->buildParameter($this->parameter);
                break;
            case ($this->data):
                $this->saveData = $this->data;
                break;
            default:
                ;
        }
        if ($this->append) {
            $this->saveData=array_merge($this->saveData,$this->append);
        }
    }

    /**
     * 获取参数的方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $array
     * @return array
     */
    static public function buildParameter($array)
    {
        $data=[];
        $request=Request::instance();
        foreach( $array as $item=>$value ){
            $data[$item] = trim($request->param($value));
        }
        return $data;
    }


    /**
     * 获取model
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return mixed
     */
    public function getModel(){
        return $this->modelClass;
    }

    /**
     * 获取错误信息
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return mixed|string
     */
    public function getError(){
        switch (true) {
            case (empty($this->error)):
                return "";
                break;
            case (is_string($this->error)):
                return $this->error;
                break;
            case (is_array($this->error)&&isset($this->error["msg"])):
                return $this->error["msg"];
                break;
            case (is_array($this->error)):
                return json_encode($this->error);
                break;
            default:
                return json_encode($this->error);
        }
    }

}