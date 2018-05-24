<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2017/06/20
 * Time: 16:59
 */

namespace mikkle\tp_redis;

use mikkle\tp_master\Db;
use mikkle\tp_master\Exception;
use mikkle\tp_master\Log;

abstract class RedisHashInfoBase
{
    static protected $instance;
    protected $redis;
    protected $infoString ;
    protected $infoId;
    protected $error;
    protected $table;
    protected $pk;
    protected $insert;
    protected $connect;
    protected $data;
    protected $field;
    protected $createTime = "create_time";
    protected $updateTime = "update_time";
    protected $completeTime = "complete_time" ;
    protected $isChange ="is_change";  //改变
    protected $except ;
    protected $expireTime = 3600*24*7;
    protected $redisOptions=[
        "index"=>3
    ];
    public function __construct($info_id,$options=[])
    {
        if ( !empty( $options)){
            $this->redis = new Redis($options);
        }else{
            $this->redis = new Redis($this->redisOptions);
        }

        if ($info_id){
            $this->infoId = $info_id ;
        }
        if (empty($this->infoString)){
            $this->infoString = get_called_class()."_info_";
        }
        $this->_initialize();
    }

    public static function instance($info_id)
    {
        $sn = md5(json_encode($info_id));
        if (self::$instance[$sn]){
            return self::$instance[$sn];
        }
        return  new static($info_id);
    }

    abstract public function _initialize();

    public function checkExists(){
        $key=$this->createInfoKey();
        return $this->redis->exists($key) ? true : false ;
    }

    public function setExpire($time){
        $key=$this->createInfoKey();
       $this->setMemberInSetExpire($time);
        return $this->redis->setExpire($key,$time) ? true : false ;
    }

    public function addMemberToSet($member){
        $key="set_".$this->createInfoKey();
        $this->redis->sAdd( $key ,$member);
        return $this;
    }
    public function checkMemberInSet($member){
        $key="set_".$this->createInfoKey();
        return $this->redis->sIsMember($key, $member);
    }

    public function removeMemberInSet($member){
        $key="set_".$this->createInfoKey();
        return $this->redis->sRemoveMember($key, $member);
    }

    public function getAllMemberInSet(){
        $key="set_".$this->createInfoKey();
        return $this->redis->sMembers($key);
    }

    public function getMemberInSetCount(){
        $key="set_".$this->createInfoKey();
        return $this->redis->sCard($key);
    }

    protected function setMemberInSetExpire($time){
        $key="set_".$this->createInfoKey();
        return $this->redis->setExpire($key, $time);
    }

    public function clearMemberInSet(){
        $key="set_".$this->createInfoKey();
        return $this->redis->setExpire($key, 0.001);
    }




    public function setLock($field,$expire=7200*24*15){
        $key="lock_".$field.$this->createInfoKey();
        return $this->redis->set( $key ,1, $expire);
    }

    public function checkLock($field){
        $key="lock_".$field.$this->createInfoKey();
        return $this->redis->get( $key) ? true : false;
    }
    public function removeLock($field){
        $key="lock_".$field.$this->createInfoKey();
        return $this->redis->delete( $key) ? true : false;
    }


    public function setInfoArray($array){
        //设置已经变化
        $this->setChangeStatus();
        $key = $this->createInfoKey();
        return $this->redis->hMset($key,$array) ? true : false ;
    }

    public function setInfoFieldValue($field,$value){
        //设置已经变化
        $this->setChangeStatus();
        $key=$this->createInfoKey();
        return ($this->redis->hSet($key,$field,$value) === false) ? false : true;
    }

    public function setInfoFieldValueNx($field,$value){
        //设置已经变化
        $this->setChangeStatus();
        $key=$this->createInfoKey();
        return $this->redis->hSetNx($key,$field,$value) === false ?  false : true  ;
    }

    public function setInfoFieldNull($field){
        //设置已经变化
        $this->setChangeStatus();
        $key=$this->createInfoKey();
        return $this->redis->hSet($key,$field,Null) === false ?  false : true  ;
    }

    public function setInfoFieldJson($field,$value){
        //设置已经变化
        $this->setChangeStatus();
        $key=$this->createInfoKey();
        return $this->redis->hSetJson($key,$field,$value) === false ?  false : true  ;
    }

    public function appendArrayInfoFieldJson($field, $value, $item = "")
    {
        //设置已经变化
        $this->setChangeStatus();
        $key = $this->createInfoKey();
        $list = $this->redis->hGetJson($key, $field);
        if ($list) {
            if ($item) {
                $list[$item] = $value;
            } else {
                $list[] = $value;
            }
        } else {
            $list = $value;
        }
        return $this->redis->hSetJson($key, $field, $list) === false ? false : true;
    }

    public function removeArrayInfoFieldJson($field,$item){
        //设置已经变化
        $this->setChangeStatus();
        $key=$this->createInfoKey();
        $list = $this->redis->hGetJson($key,$field) ;
        if ($list){
            unset($list[$item] );
        }
        return $this->redis->hSetJson($key,$field,$list) === false ?  false : true  ;
    }

    public function setInfoFieldIncre($field,$value=1){
        //设置已经变化
        $this->setChangeStatus();
        $key=$this->createInfoKey();
        return $this->redis->hIncre($key,$field,$value)  ;
    }

    public function existsField($field){
       $key =$this->createInfoKey();
        return $this->redis->hExists($key,$field)  ;
    }

    public function getInfoFieldValue($field){
        $key=$this->createInfoKey();
        return $this->redis->hGet($key,$field)  ;
    }

    public function getInfoFieldJson($field){
        $key=$this->createInfoKey();
        return $this->redis->hGetJson($key,$field)  ;
    }

    public function getInfoFieldNum(){
        $key=$this->createInfoKey();
        return $this->redis->hLan($key)  ;
    }

    public function getInfoList($array=[]){
        $key=$this->createInfoKey();
        return $this->redis->hGet($key,$array)  ;
    }

    public function removeField($field){
        //设置已经变化
        $this->setChangeStatus();
        $key=$this->createInfoKey();
        if (is_array($field)){
            $i=0;
            foreach ($field as $value){
                $i=$i+( $this->redis->hDel($key,$value) ? 1:0 );
            }
            return $i;
        }elseif(is_string($field)){
            return $this->redis->hDel($key,$field)  ;
        }
        return false;

    }

    public function delete(){
        $key=$this->createInfoKey();
        return $this->redis->delete($key)  ;
    }

    protected function createInfoKey(){
        if (empty($this->infoString)){
            throw  new  Exception("未设置数据中心前缀字符串");
        }
        return $this->infoString.$this->infoId;
    }


    protected function insertHandle(){

    }

    protected function updateHandle(){

    }

    public function updateTableData($fieldList=[],$complete=false){
        try {
            if (!$this->table || ! $this->pk|| !$this->checkExists()) {
                throw  new  Exception("未设置Redis对应的数据表或数据");
            }
            $fieldList = empty($fieldList) ? $this->getTableFieldList() : $fieldList;
            if ($this->checkTableDataExists()){
                $this->updateHandle();
                $this->setInfoFieldValue($this->updateTime,time());
                $info = $this->getInfoList( $fieldList );

                if ($complete && isset( $fieldList[$this->completeTime])){
                    $info[$this->completeTime]=time();
                }
                unset( $info[$this->pk]);
                unset( $info["id"]);
                if (Db::table($this->table)->where([
                    $this->pk => $this->infoId,
                ])->update($info)) {
                    return true;
                } else {
                    Log::notice("方式为升级失败");
                   // Log::notice($info);
                    return false;
                }
            }else{
                $this->insertHandle();
                $this->setInfoFieldValue($this->createTime,time());
                $info = $this->getInfoList( $fieldList );
                if ($complete && isset( $fieldList[$this->completeTime])){
                    $info[$this->completeTime]=time();
                }
                unset( $info["id"]);
                if (Db::table($this->table)->insert($info)) {
                    return true;
                } else {
                    Log::error("方式为写入失败");
                    Log::notice($info);
                    return false;
                }
            }
        } catch (Exception $e) {
            Log::error($e);
            return false;
        }
    }


    protected function getTableFieldList(){
        if ($this->table){
            return Db::cache($this->table."_field_list" , 60)->getTableInfo($this->table, 'fields');
        }else{
            return [];
        }
    }

    /**
     * title 检查数据表的数据是否存在
     * description checkTableDataExists
     * User: Mikkle
     * QQ:776329498
     * @return bool
     */
    public function checkTableDataExists(){
        try {
            if ($this->table && $this->pk) {
                return (Db::table($this->table)->where([
                        $this->pk => $this->infoId,
                    ])->count() >0) ? true : false;
            } else {
                return false;
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    /**
     * title 删除数据表的数据
     * description deleteTableData
     * User: Mikkle
     * QQ:776329498
     * @return bool
     */
    public function deleteTableData(){
        try {
            if ($this->table && $this->pk) {
                return (Db::table($this->table)->where([
                        $this->pk => $this->infoId,
                    ])->delete() == 1) ? true : false;
            } else {
                return false;
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }



    public function initTableData(){
        try {
            if ($this->table && $this->pk) {
                $list =  Db::table($this->table)->where([
                        $this->pk => $this->infoId,
                    ])->find();
                if ($list){
                    return $this->setInfoArray( $list );
                }else{
                    return false;
                }
            } else {
                return false;
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    protected function deleteHtml($str)
    {
        $str = trim($str); //清除字符串两边的空格
        $str = strip_tags($str,""); //利用php自带的函数清除html格式
        $str = preg_replace("/\t/","",$str); //使用正则表达式替换内容，如：空格，换行，并将替换为空。
        $str = preg_replace("/\r\n/","",$str);
        $str = preg_replace("/\r/","",$str);
        $str = preg_replace("/\n/","",$str);
        $str = preg_replace("/ /","",$str);
        $str = preg_replace("/  /","",$str);  //匹配html中的空格
        return trim($str); //返回字符串
    }

    protected function checkChange(){
        $key=$this->createInfoKey();
        return ($this->redis->hGet($key,$this->isChange)==1) ? true : false;
    }

    protected function setChangeStatus($status = 1){
        $key=$this->createInfoKey();
        $this->redis->hSet($key,$this->isChange,$status);
        return $this;
    }

    /*
  * 检查是注重某些值是非为空
  */
    protected function checkArrayValueEmpty($array,$value,$error=true){
        switch (true){
            case (empty($array)||!is_array($array)):
                if ($error==true){
                    $this->addError("要检测的数据不存在或者非数组");
                }
                return false;
                break;
            case (is_array($value)):
                foreach ($value as $item){
                    if (!isset($array[$item]) || (empty($array[$item]) && $array[$item]!==0)){
                        if ($error==true) {
                            $this->addError("要检测的数组数据有不存在键值{$item}");
                        }
                        return false;
                    }
                }
                break;
            case (is_string($value)):
                if (!isset($array[$value]) || empty($array[$value] && $array[$value]!==0)){
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

    public function addError($error){
        $this->error = is_string($error) ? $error : json_encode($error);
    }

    public function getError(){
        return $this->error;
    }

    public function __destruct()
    {


    }


}