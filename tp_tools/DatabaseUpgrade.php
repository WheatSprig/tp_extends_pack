<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/06/29
 * Time: 9:46
 */

namespace mikkle\tp_tools;
use mikkle\tp_master\Db;
use mikkle\tp_master\Exception;
use mikkle\tp_master\Log;

class DatabaseUpgrade
{
    static protected $instance;
    protected $originDb;
    protected $updateDb;
    protected $originDatabaseName;
    protected $updateDatabaseName;
    public function __construct($options=[])
    {


    }
    public static function instance($options = [])
    {
        if (is_null(self::$instance)) {
            self::$instance = new static($options);
        }
        return self::$instance;
    }

    /**
     * 输出数据库不同
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param string $DatabaseName
     * @param string $originDatabaseName
     */
    public function getDiffer($DatabaseName="",$originDatabaseName=""){
        try{
            $this->setUpdateDb($DatabaseName,$originDatabaseName);
            $tables=$this->checkTableDiffer();
            if (!empty($tables)){
                dump($tables);
            }else{
                echo "暂无不存在的表".PHP_EOL;
            }
            $fields=$this->checkTableFieldDiffer();
            if (!empty($fields)){
                dump($fields);
            }else{
                echo "暂无不存在的字段".PHP_EOL;
            }
            return;
        }catch (Exception $e){
            Log::error($e->getMessage());
        }


    }

    /**
     * 比对更新数据库
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param string $updateDatabaseName
     * @param string $originDatabaseName
     */
    public function updateDatabase($updateDatabaseName="yzf",$originDatabaseName=""){
        try{
            $this->setUpdateDb($updateDatabaseName,$originDatabaseName);
            $tables=$this->checkTableDiffer();
            if (!empty($tables)){
                $this->createTable($this->updateDb,$tables);
            }else{
                echo "暂无不存在的表".PHP_EOL;
            }
            $fields=$this->checkTableFieldDiffer();
            if (!empty($fields)){
                $this->createTableField($fields);
            }else{
                echo "暂无不存在的字段".PHP_EOL;
            }
            return;
        }catch (Exception $e){
            Log::error($e->getMessage());
        }

    }


    /**
     * 设置比对更新的数据库
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param string $updateDatabaseName
     * @param string $originDatabaseName
     */
    protected function setUpdateDb($updateDatabaseName="yzf",$originDatabaseName = "yzfErp"){
        $this->originDatabaseName = $originDatabaseName;
        $this->updateDatabaseName = $updateDatabaseName;
        $originConnect = "mysql://root:Haode1234567890@127.0.0.1:3306/{$this->originDatabaseName}#utf8";
        $updateConnect = "mysql://root:Haode1234567890@127.0.0.1:3306/{$updateDatabaseName}#utf8";
        $this->originDb=Db::connect($originConnect);
        $this->updateDb=Db::connect($updateConnect);
    }

    /**
     * 检测数据表不同
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return array
     */
    protected function checkTableDiffer(){
        $originTable=$this->getTable($this->originDb);
        $updateTable=$this->getTable($this->updateDb);
        $differ = $this->checkArrayDiffer($originTable,$updateTable);
        return $differ;
    }

    /**
     * 获取所有数据表
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $db
     * @return mixed
     */
    protected function getTable($db){
        $table = $db->query("show tables");
        foreach ($table as $item=>$value){
            $table[$item]=array_values($value);
        }
        return $table;
    }

    /**
     * 比对字段不同
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return array
     */
    protected function checkTableFieldDiffer(){
        $origin=$this->getTableField($this->originDb,$this->originDatabaseName);

        $update=$this->getTableField($this->originDb,$this->updateDatabaseName);
        $differ = $this->checkArrayDiffer($origin,$update);
       return $differ;
    }


    /**
     * 获取字段
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $db
     * @param $databaseName
     * @return mixed
     */
    protected function getTableField($db,$databaseName){
        $sql="SELECT TABLE_NAME,COLUMN_NAME,COLUMN_TYPE,IS_NULLABLE,CHARACTER_SET_NAME,COLUMN_KEY,EXTRA from information_schema.COLUMNS where  TABLE_SCHEMA='{$databaseName}'";
        return $db->query($sql);
    }

    /**
     * 创建数据表
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $db
     * @param $tables
     */
    protected function createTable($db,$tables){
        foreach ($tables as $table){
            $db->query("CREATE TABLE {$table} LIKE {$this->originDatabaseName}.{$table}");
            $notice =  "数据库[{$this->updateDatabaseName}]创建表[{$table}]成功".PHP_EOL;
            Log::notice("$notice");
            echo $notice;
        }
    }

    /**
     * 创建或者更新字段
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $fields
     */
    protected function createTableField($fields){
        foreach ($fields as $field){
            if (isset($field["TABLE_NAME"])&&isset($field["COLUMN_NAME"])){
                $null = $field["TABLE_NAME"] ? " NULL " : " NOT NULL ";
                if(!$this->hasColumn($field["COLUMN_NAME"],$field["TABLE_NAME"])){
                    $sql="ALTER TABLE `{$field["TABLE_NAME"]}` ADD `{$field["COLUMN_NAME"]}` {$field["COLUMN_TYPE"]} {$null} ";
                    $type="添加";
                }else{
                    $sql="ALTER TABLE `{$field["TABLE_NAME"]}` MODIFY `{$field["COLUMN_NAME"]}` {$field["COLUMN_TYPE"]} {$null} ";
                    $type="修改";
                }
                $notice =  "数据库[{$this->updateDatabaseName}]表[{$field["TABLE_NAME"]}]{$type}字段[{$field["COLUMN_NAME"]}]成功".PHP_EOL;
                Log::notice("$notice");
                echo $notice;
                $this->updateDb->query($sql);
            }
        }
    }

    /**
     * 判断字段存在
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $column
     * @param $table
     * @return bool
     */
    protected function hasColumn($column,$table){
        if (empty($table)||$column){
            $this->error="hasColumn方法参数缺失";
            return false;
        }
        $sql = "SELECT * FROM information_schema.columns WHERE table_schema=CurrentDatabase AND table_name = '{$table}' AND column_name = '{$column}'";
        return $this->query($sql) ? true : false;
    }


    /**
     * 比对数组不同
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $arr1
     * @param $arr2
     * @return array
     */
    protected function checkArrayDiffer($arr1,$arr2){
            $arr3=array();
            foreach ($arr1 as $key => $value) {
                if(!in_array($value,$arr2)){
                    //如果A不在B里 则证明B里没有这个字段或表...则存起来.
                    if (is_array($value)&&count($value)==1){
                        $arr3[]=$value[0];
                    }else{
                        $arr3[]=$value;
                    }
                }
            }
            return $arr3;
    }
}


