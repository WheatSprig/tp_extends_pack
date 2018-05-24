<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/5/17
 * Time: 9:00
 */

namespace mikkle\tp_logic;



use mikkle\tp_master\Db;
use mikkle\tp_master\Debug;
use mikkle\tp_master\Exception;
use mikkle\tp_master\Log;
use mikkle\tp_master\Request;
use mikkle\tp_tools\Rand;

/**
 * title 逻辑层基类
 * User: Mikkle
 * QQ:776329498
 * Class LogicBase
 * @package mikkle\tp_logic
 */
abstract class LogicBase
{
    protected $model;
    protected $error;
    protected $optionNum;
    protected $timeString;
    protected $className;
    protected $functionName;
    protected $args; //请求的参数
    protected $result; //返回值
    protected $isRecord =false;  //是否记录
    protected $recordTable = "mk_log_service_operate" ;
    protected $recordConnect;
    public function __construct()
    {

        date_default_timezone_set('PRC');
        $this->className =get_called_class();
        if ($this->isRecord && $this->recordTable){
            Debug::remark("start");
            $this->optionNum = Rand::createSerialNumberByPrefix("1010");
            $this->timeString = $this->getTimeString();
        }
        $this->_initialize();
    }

    abstract public function _initialize();

    public function addError($error){
        $this->error = is_string($error) ? $error : json_encode($error);
    }
    public function getError(){
        return $this->error;
    }

    protected function getTimeString($time="", $timeString ="Y-m-d H:i:s"){
        switch (true) {
            case (empty($time)):
                $timeInt=time();
                break;
            // 1513699200 "2017-12-20 00:00:00"  1514736000 "2018-1-1"
            case (is_numeric($time) &&((int)$time > strtotime("2000-01-01") && (int) $time < strtotime("2030-01-01") ) ):
                $timeInt = $time;
                break;
            case (is_string($time)):
                $timeInt = strtotime($time);
                if ($timeInt == false) {
                    $timeInt=time();
                }
                break;
            default :
                $timeInt=time();
        }
        return date($timeString,(int)$timeInt) ;
    }

    protected function getTimeInt($time=""){
        switch (true) {
            case (empty($time)):
                $timeInt=time();
                break;
            // 1513699200 "2017-12-20 00:00:00"  1514736000 "2018-1-1"
            case (is_numeric($time) && ((int)$time > strtotime("2000-01-01") && (int) $time < strtotime("2030-01-01") )):
                $timeInt = $time;
                break;
            case (is_string($time)):
                $timeInt = strtotime($time);
                if ($timeInt == false) {
                    $timeInt=time();
                }
                break;
            default :
                $timeInt=time();
        }
        return $timeInt ;
    }

    /*
     * 检查是数组某些值是非为空
     */
    protected function checkArrayValueStatus($array,$value,$error=true){
        switch (true){
            case (empty($array)||!is_array($array)):
                if ($error==true){
                    $this->addError("要检测的数据不存在或者非数组");
                }
                return false;
                break;
            case (is_array($value)):
                foreach ($value as $item){
                    if (!isset($array[$item]) || (empty($array[$item]) && (string)$array[$item]!=="0")){
                        if ($error==true) {
                            $this->addError("要检测的数组数据有不存在键值{$item}");
                        }
                        return false;
                    }
                }
                break;
            case (is_string($value)):
                if (!isset($array[$value]) || (empty($array[$value] ) && (string)$array[$value]!=="0")){
                    if ($error==true) {
                        $this->addError("要检测的数组数据有不存在键值{$value}");
                    }
                    return false;
                }
                break;
            default:
        }
        return true;
    }

    public function __destruct()
    {
        if ($this->isRecord && $this->recordTable) {
            try {
                Debug::remark("end");
                $operateData = [
                    "number" => $this->optionNum,
                    "class" => $this->className,
                    "function" => $this->functionName,
                    "args" => is_string($this->args) ? $this->args : json_encode($this->args),
                    "error" => $this->error ? $this->error : null,
                    "ip" => Request::instance()->ip(),
                    "run_time" => Debug::getRangeTime("start", "end"),
                    "result" => is_string($this->result) ? $this->result : json_encode($this->result),
                    "time" => $this->timeString,
                ];
                if ($this->recordConnect){
                    Db::connect($this->recordConnect)->table($this->recordTable)->insert($operateData);
                }else{
                    Db::table($this->recordTable)->insert($operateData);
                }
            } catch (Exception $e) {
                Log::error($e->getMessage());
            }
        }
    }

}