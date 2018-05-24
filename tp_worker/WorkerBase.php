<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2017/6/19
 * Time: 10:10
 */

namespace mikkle\tp_worker;


use mikkle\tp_master\Db;
use mikkle\tp_master\Exception;
use mikkle\tp_master\Log;
use mikkle\tp_tools\Time;

abstract class WorkerBase
{
    protected $listName;
    protected $redis;
    protected $workList;
    protected $workerName;
    public static $instance;
    protected $connect = [];
    protected $saveLog = false;
    protected $tableName = "mk_log_service_queue";
    protected $error;

    /**
     * Base constructor.
     * @param array $options
     */

    public function __construct($options = [])
    {
        $this->redis = $this->redis();
        $this->workList = "worker_list";
        $this->workerName = get_called_class();
        $this->listName = md5($this->workerName);
        $this->_initialize($options);
    }

    public function _initialize($options=[])
    {

    }

    abstract protected function runHandle($data);

    /**
     * @title redis
     * @description redis加载自定义Redis类
     * User: Mikkle
     * QQ:776329498
     * @return \mikkle\tp_redis\Redis
     */
    protected static function redis()
    {
        return WorkerRedis::instance();
    }


    /**
     * @title runWorker
     * @description 标注命令行执行此任务
     * User: Mikkle
     * QQ:776329498
     * @param string $handleName
     */
    public function runWorker($handleName = "run")
    {
        $this->redis()->hset($this->workList, $this->workerName, $handleName);
    }

    /**
     * 标注命令行清除此任务
     * Power: Mikkle
     * Email：776329498@qq.com
     */
    public function clearWorker()
    {
        $this->redis()->hdel($this->workList, $this->workerName);
    }


    /**
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param array $options
     * @return static
     */
    static public function instance($options = [])
    {
        if (isset(static::$instance)) {
            return static::$instance;
        } else {
            return new static($options);
        }
    }

    /**
     *      * 快速添加模版消息任务
     *
     * 当命令行未运行 直接执行
     * description add
     * User: Mikkle
     * QQ:776329498
     * @param $data
     * @param array $options
     * @param string $handleName
     * @return bool
     */
    static public function add($data, $options = [], $handleName = "run")
    {
        try {
            $data = json_encode($data);
            $instance = static::instance($options);
            switch (true) {
                case (self::checkCommandRun()):
                    $instance->redis()->lpush($instance->listName, $data);
                    Log::notice("Command service start work!!");
                    $instance->runWorker($handleName);
                    break;
                default:
                    Log::notice("Command service No away!!");
                    $instance->runHandle($data);
            }
            return true;
        } catch (Exception $e) {
            Log::error($e->getMessage());
            return false;
        }
    }

    /**
     * 命令行执行的方法
     * Power: Mikkle
     * Email：776329498@qq.com
     */
    static public function run()
    {
        $instance = static::instance();
        try {
            $i = 0;
            while (true) {
                $redisData = $instance->redis->rpop($instance->listName);
                if ($redisData) {
                    $data = json_decode($redisData, true);
                    $result = $instance->runHandle($data);
                    if ($instance->saveLog) {
                        $instance->saveRunLog($result, $data);
                    }
                } else {
                    $instance->clearWorker();
                    break;
                }
                $i++;
                sleep(1);
            }
            echo "执行了{$i}次任务" . PHP_EOL;
        } catch (Exception $e) {
            Log::error($e);
            Log::error($e->getMessage());
            echo($e->getMessage());
        }
    }


    /**
     * 检测命令行是否执行中
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return bool
     */
    static public function checkCommandRun()
    {
        return self::redis()->get("command") ? true : false;
    }

    public function getError()
    {
        if (is_array($this->error)) {
            return json_encode($this->error);
        }
        return $this->error;
    }

    /*
 * 检查是注重某些值是非为空
 */
    protected function checkArrayValueEmpty($array, $value, $error = true)
    {
        switch (true) {
            case (empty($array) || !is_array($array)):
                if ($error == true) {
                    $this->addError("要检测的数据不存在或者非数组");
                }
                return false;
                break;
            case (is_array($value)):
                foreach ($value as $item) {
                    if (!isset($array[$item]) || (empty($array[$item]) && $array[$item] !== 0)) {
                        if ($error == true) {
                            $this->addError("要检测的数组数据有不存在键值{$item}");
                        }
                        return false;
                    }
                }
                break;
            case (is_string($value)):
                if (!isset($array[$value]) || empty($array[$value] && $array[$value] !== 0)) {
                    if ($error == true) {
                        $this->addError("要检测的数组数据有不存在键值{$value}");
                    }
                    return false;
                }
                break;
            default:
        }
        return true;
    }

    public function addError($error)
    {
        $this->error = is_string($error) ? $error : json_encode($error);
    }


    protected function saveRunLog($result, $data)
    {
        try {
            $operateData = [
                "class" => $this->workerName,
                "args" => json_encode($data),
                "result" => $result ? "true" : "false",
                "error" => $this->error ? $this->getError() : null,
                "time" => Time::getDefaultTimeString(),
            ];
            Db::connect($this->connect)->table($this->tableName)->insert($operateData);
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    protected function sleep($time = 1)
    {
        sleep(sleep($time));
    }

}