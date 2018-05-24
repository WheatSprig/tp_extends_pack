<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2017/9/19
 * Time: 10:10
 */

namespace mikkle\tp_worker;



use mikkle\tp_master\Exception;
use mikkle\tp_master\Log;

/**
 * title  定时队列类
 * User: Mikkle
 * QQ:776329498
 * Class TimingWorkerBase
 * @package mikkle\tp_worker
 * 创建定时队列类并继承使用方法
 * class Test extends TimingWorkerBase
 * {
 * protected function runHandle($data)
 * {
 * Log::notice(  "测试".RandNumCenter::getTimeString()  );
 * }
 * }
 *
 * 添加方法定时队列方法
 *  \app\worker\Test::add(["name"=>"mikkle",],30);
 */

abstract class TimingWorkerBase extends WorkerBase
{
    protected $listName;
    protected $listData;
    protected $listNum;
    protected $lockName;

    public function _initialize($options = [])
    {
        $this->listData = "{$this->listName}_data";
        $this->listNum = "{$this->listName}_num";
    }


    /**
     *      * 快速定时任务
     *
     * 当命令行未运行 直接执行
     * description add
     * User: Mikkle
     * QQ:776329498
     * @param $data
     * @param $runTime
     * @param array $options
     * @param string $handleName
     * @return bool
     */
    static public function add($data, $runTime = 0, $handleName = "run", $options = [])
    {
        try {
            $data = json_encode($data);
            $instance = static::instance($options);
            switch (true) {
                case (self::checkCommandRun()):
                    $time = $instance->getRunTime($runTime);
                    $num = $instance->redis()->incre($instance->listNum);
                    Log::notice("添加了 $num 号定时任务");
                    $instance->redis()->zAdd($instance->listName, [$time => $num]);
                    $instance->redis()->hSet($instance->listData, $num, $data);
                    Log::notice("Timing Command service start work!!");
                    $instance->runWorker($handleName);
                    break;
                default:
                    Log::notice("Timing Command service No away!!");
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
        try {
            $i = 0;
            $instance = static::instance();
            //读取并删除定时任务
            $workList = $instance->redis()->zRangByScore($instance->listName, 0, time());
            $instance->redis()->zDelete($instance->listName, $workList);
            //剩余任务数
            $re = $instance->redis()->zCard($instance->listName);
            if ( $workList ){
                foreach ($workList as $num) {
                    try {
                        $redisData = $instance->redis()->hGet($instance->listData, $num);
                        if ($redisData) {
                            $data = json_decode($redisData, true);
                            $result = $instance->runHandle($data);
                            Log::notice("执行{$num}编号任务");
                            if ($instance->saveLog) {
                                $instance->saveRunLog($result, $data);
                            }
                            $instance->redis()->hDel($instance->listData, $num);
                        }
                    } catch (Exception $e) {
                        Log::error($e->getMessage());
                        $instance->redis()->zAdd($instance->listData, [(time() + 300) => $num]);
                    }
                    $i++;
                    sleep(1);
                }
            }
            if ( $re=== 0) {
                $instance->clearWorker();
            }
            echo "执行了{$i}次任务,剩余未执行任务[{$re}]项" . PHP_EOL;
            Log::notice("执行了{$i}次任务,剩余未执行任务[{$re}]项");
        } catch (Exception $e) {
            //Log::error($e);
            Log::error($e->getMessage());
            echo($e->getMessage());
        }
    }


    protected function getRunTime($time = 0)
    {
        $now = time();
        switch (true) {
            case ($time == 0):
                return $now;
                break;
            case (is_int($time) && 30 * 3600 * 24 > $time):
                return $now + $time;
                break;
            case (is_int($time) && $now < $time):
                return $time;
                break;
            default:
                return $now + (int)$time;
        }
    }
}