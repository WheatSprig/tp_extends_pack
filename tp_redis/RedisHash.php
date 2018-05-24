<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/6/21
 * Time: 21:03
 */

namespace mikkle\tp_redis;


use mikkle\tp_master\Db;
use mikkle\tp_master\Exception;
use mikkle\tp_master\Log;

class RedisHash
{
    protected $redisHash = null;//redis实例化时静态变量

    static protected $instance;
    protected $sn;

    protected $table ;
    public $hashKey;
    protected $prefix="Wechat:";   //HashKey前缀
    protected $host="127.0.0.1";
    protected $port= 6379;
    protected $auth="";
    protected $index = 10;  //Hash 我个人默认使用10库

    public function __construct($options=[]){
        $host = trim(isset($options["host"]) ? $options["host"] : $this->host);
        $port = trim(isset($options["port"]) ? $options["port"] : $this->port );
        $auth = trim(isset($options["auth"]) ? $options["auth"] : $this->auth );
        $index = trim(isset($options["index"]) ? $options["index"] : $this->index );
        if (!is_integer($index) && $index>16) {
            $index = $this->index;
        }
        $sn = md5("{$host}{$port}{$auth}{$index}");
        $this->sn = $sn;
        if (!isset($this->redisHash[$this->sn])){
            try{
            $this->redisHash[$this->sn]=new \Redis();
            $this->redisHash[$this->sn]->connect($host,$port);
            $this->redisHash[$this->sn]->auth($auth);
            $this->redisHash[$this->sn]->select($index);
            }catch (Exception $e){
                Log::error($e->getMessage());
            }
        }
        $this->redisHash[$this->sn]->sn=$sn;
        $this->redisHash[$this->sn]->prefix=$this->prefix;
        $this->index = $index;
        return ;
    }

    /**
     * User: Mikkle
     * Q Q:776329498
     * @param array $options
     * @return RedisHash
     */
    public static function instance($options=[])
    {
        return  new RedisHash($options);
    }

    /**
     * 设置key值(table名称)
     * User: Mikkle
     * Q Q:776329498
     * @param $table
     * @return $this
     */
    public function setTable($table){
        $this->redisHash[$this->sn]->table=$table;
        $this->redisHash[$this->sn]->hashKey=null;
        return $this;
    }

    /**
     * 设置key值(PK值)
     * User: Mikkle
     * Q Q:776329498
     * @param $key
     * @return $this
     */
    public function setKey($key){
        $this->redisHash[$this->sn]->key=$key;
        $this->redisHash[$this->sn]->hashKey=null;
        return $this;
    }

    /**
     * 设置过期时间 或指定过期的时间戳
     * User: Mikkle
     * Q Q:776329498
     * @param int $time
     * @return bool
     */
    public function setExpire($time=0){
        $hash_key = $this->getHashKey();
        if(!$hash_key){
            return false;
        }
        switch (true){
            case ($time==0):
                return $this->redisHash[$this->sn]->expire($hash_key,0);
                break;
            case ($time>time()):
                $this->redisHash[$this->sn]->expireAt($hash_key,$time);
                break;
            default:
                return $this->redisHash[$this->sn]->expire($hash_key,$time);
        }
    }

    static function quickGet($key,$field=[]){
        return (new RedisHash())->quickFindByHashKey($key,$field);
    }

    /**
     * 快速获取 不存在查表写入
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $key
     * @param array $field
     * @return bool
     */

    public function quickFindByHashKey($key,$field=[]){
        $return_data = $this->getByHashKey($key,$field);
        if(!empty($return_data)||$return_data==0){

            return $return_data;
        }else{
            $key_array = explode(":",$key);
            switch (true){
                case (count($key_array)!=2):
                    return false;
                    break;
                case (is_numeric($key_array[1])):
                    $pk = "id";
                    break;
                case (is_string($key_array[1])):
                    $pk = "guid";
                    break;
                default:
                    return false;
            }
            $data = Db::table($key_array[0])->where([$pk=>$key_array[1]])->find();

            if ($data){
                $this->setHashKey($key)->set($data);
            }else{
                return false;
            }
            return $this->getByHashKey($key,$field);
        }
    }

    /**
     * 通过Hash获取
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $key
     * @param array $field
     * @return bool
     */
    public function getByHashKey($key,$field=[]){

        switch (true){
            case (empty($key)):
                return false;
                break;
            case (is_string($key)&&empty($field)):
                $key=$this->getTrueHashKeyByHashKey($key);
                $return_data = $this->redisHash[$this->sn]->hGetAll($key);
                break;
            case (is_string($key)&&!empty($field)):
                $key=$this->getTrueHashKeyByHashKey($key);
                $field = is_string($field)?explode(",",$field) : $field;
                $return_data = $this->redisHash[$this->sn]->hMget($key,$field);
                break;
            default:
                return false;
        }
        return $return_data;
    }

    protected function getTrueHashKeyByHashKey($key){
        if(substr($key,0,count($this->prefix))!="$this->prefix"){
            $key=$this->prefix."$key";
        }
        return $key;
    }

    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $key
     * @return bool
     */
    public function exists($key){

        switch (true){
            case (empty($key)):
                return false;
                break;
            case (is_string($key)):
                $return_data = $this->redisHash[$this->sn]->exists($key);
                break;
            default:
                return false;
        }
        return $return_data;

    }

    /**
     * 通用设置字段值
     * User: Mikkle
     * Q Q:776329498
     * @param $field
     * @param string $value
     * @return bool
     */
    public function set($field,$value=""){

        switch(true) {
            //判断为空
            case (is_array($field)&&empty($value)):
                return $this->setAll($field);
                break;
            case  (is_object($field)&&empty($value)):
                return $this->setAll($field->toArray());
                break;
            case  (is_string($field)&&is_string($value)):
                return $this->setField($field,$value);
                break;

            default:
                return false;
        }

    }

    /**
     * 设置指定字段值
     * User: Mikkle
     * Q Q:776329498
     * @param $field
     * @param $value
     * @return bool
     */
    protected function setField($field,$value){
        $hash_key = $this->getHashKey();
        if(!$hash_key){
            return false;
        }
        if(is_string($field)||is_string($value)){
            return $this->redisHash[$this->sn]->hSet( $hash_key, $field, $value );
        }
        return false;
    }



    /**
     * 批量设置字段
     * User: Mikkle
     * Q Q:776329498
     * @param $data 数组
     * @return bool
     */
    protected function setAll($data){

        $hash_key = $this->getHashKey();
        if(!$hash_key){
            return false;
        }
        if(is_array($data)){
            return  $this->redisHash[$this->sn]->hMset($hash_key,$data);
        }else{
            return false;
        }
    }

    /**
     * 获取字段值 支持单字段 字符串逗号分割 数组
     * User: Mikkle
     * Q Q:776329498
     * @param array $field 支持单字段 字符串逗号分割 数组
     * @return bool
     */
    public function get($field=[]){

        switch(true) {
            //判断为空
            case empty($field):
                return $this->getAll();
                break;
            case  is_string($field):
                $field_list = explode(",",$field);
                if (count($field_list)==1){
                    return $this->getByField($field_list[0]);
                }else{
                    return $this->getByArray($field_list);
                }
                break;
            case  is_numeric($field):
                return $this->getByField($field);

                break;
            case is_array($field):
                return $this->getByArray($field);
                break;
            case is_object($field):
                return false;
                break;
            default:
                return $this->getByField($field);
        }
    }

    /**
     * 通过数组获取指定字段值
     * User: Mikkle
     * Q Q:776329498
     * @param array $field_list
     * @return bool
     */
    protected function getByArray($field_list=[]){
        $hash_key = $this->getHashKey();
        if(!$hash_key){
            return false;
        }
        if(empty($field_list)){
            return false;
        }
        return $this->redisHash[$this->sn]->hMget($hash_key,$field_list);
    }

    /**
     * 获取指定字段值
     * User: Mikkle
     * Q Q:776329498
     * @param $field
     * @return bool
     */
    protected function getByField($field){
        $hash_key = $this->getHashKey();
        if(!$hash_key){
            return false;
        }
        if(empty($field)){
            return false;
        }
        return $this->redisHash[$this->sn]->hGet($hash_key,$field);

    }

    /**
     * 获取全部字段值
     * User: Mikkle
     * Q Q:776329498
     * @return bool
     */
    protected function getAll(){
        $hash_key = $this->getHashKey();
        if(!$hash_key){
            return false;
        }
        return $this->redisHash[$this->sn]->hGetAll($hash_key);
    }

    /**
     * 直接设置HashKey值
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $hash_key
     * @return $this
     */
    public function setHashKey($hash_key){
        $this->redisHash[$this->sn]->hashKey=$this->redisHash[$this->sn]->prefix.$hash_key;
        $this->redisHash[$this->sn]->table=null;
        $this->redisHash[$this->sn]->key=null;
        return $this;
    }


    /**
     * 获取HashKey值
     * User: Mikkle
     * Q Q:776329498
     * @return bool|string
     */
    protected function getHashKey()
    {
        try {
            if (isset($this->redisHash[$this->sn]->hashKey)){
                return $this->redisHash[$this->sn]->hashKey;
            }
            if (!isset($this->redisHash[$this->sn]->table) || !isset($this->redisHash[$this->sn]->key)) {
                throw new Exception("数据表或者PK键未建立");
            }
            $this->redisHash[$this->sn]->hashKey = $this->redisHash[$this->sn]->table . ":" . $this->redisHash[$this->sn]->key;
            return $this->redisHash[$this->sn]->prefix .$this->redisHash[$this->sn]->table . ":" . $this->redisHash[$this->sn]->key;
        } catch (Exception $e) {
            return false;
        }
    }

    public function keys($pattern=""){
        if(!empty($pattern)){
            return $this->redisHash[$this->sn]->keys($pattern);
        }
        elseif(empty($pattern)&&isset($this->redisHash[$this->sn]->table)){
            return $this->redisHash[$this->sn]->keys($this->redisHash[$this->sn]->table.":*");
        }else{
            return $this->redisHash[$this->sn]->keys("*");
        }
        return false;
    }
    public function delete($hashKey1, $hashKey2 = null, $hashKeyN = null){
        $key = $this->getHashKey();
        return $this->redisHash[$this->sn]->hDel( $key,$hashKey1, $hashKey2, $hashKeyN );

    }

    public function deleteByKey($key1=null, $key2 = null, $key3 = null ) {
        $key1 = empty($key1)?$this->getHashKey():$key1;
        return $this->redisHash[$this->sn]->delete( $key1, $key2 , $key3 );
    }

    public function clear($pattern="") {
        if(!empty($pattern)){
            return $this->redisHash[$this->sn]->delete($this->keys($pattern));
        }
        if(empty($pattern)&&isset($this->redisHash[$this->sn]->table)){
            return $this->redisHash[$this->sn]->delete($this->keys($this->redisHash[$this->sn]->table.":*"));
        }
        return false;
    }

    public function clearAll() {
        return $this->redisHash[$this->sn]->flushDB();
    }


    public function getCount(){
        return $this->redisHash[$this->sn]->hLen($this->getHashKey());
    }

    public function getValues(){
        return $this->redisHash[$this->sn]->hVals($this->getHashKey());
    }




    /**
     * 魔术方法 有不存在的操作的时候执行
     * @access public
     * @param string $method 方法名
     * @param array $args 参数
     * @return mixed
     */
    public function __call($method, $args)
    {
        call_user_func_array([$this->redisHash[$this->sn], $method], $args);
    }

}