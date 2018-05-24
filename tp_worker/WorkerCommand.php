<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2017/06/19
 * Time: 10:14
 */

namespace mikkle\tp_worker;

use mikkle\tp_master\Command;
use mikkle\tp_master\Exception;
use mikkle\tp_master\Log;


/**
 * Created by PhpStorm.
 * Power by Mikkle
 * QQ:776329498
 * Date: 2017/6/12
 * Time: 15:07
 */
abstract class WorkerCommand extends Command
{
    protected $sleep = 5;
    protected $redis;
    protected $listName;
    protected $pcntl;

    public function __construct($name = null)
    {
        $this->redis = $this->redis();
        $this->listName = "worker_list";
        $this->pcntl = true;
        parent::__construct($name);
    }

    protected function configure()
    {
        $this->setName('mikkle')->setDescription('Here is the mikkle\'s command ');
    }

    protected function executeHandle($input, $output)
    {
        while (true) {
            //标记后端服务运行中
            $this->signWorking();
            echo "==================================================" . PHP_EOL;
            $this->autoClass();
            echo "==================================================" . PHP_EOL;
            $this->sleep();
        }
    }


    /**
     * 自动执行
     * Power: Mikkle
     * Email：776329498@qq.com
     * @return bool
     */
    protected function autoClass()
    {
        $works = $this->getWorks();
        if ($works) {
            foreach ($works as $work => $item) {
                if ($this->pcntl) {
                    $this->pcntlWorker($work, $item);
                } else {
                    $this->runWorker($work, $item);
                }
            }
        } else {
            return false;
        }
    }

    public function getWorks()
    {
        try {
            return $this->redis->hget($this->listName);
        } catch (Exception $e) {
            return false;
        }
    }


    /**
     * 检测执行方法是否存在
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $work
     * @param $item
     * @return bool
     */
    protected function checkWorkerExists($work, $item)
    {
        if (class_exists($work)) {
            if (method_exists($work, $item)) {
                return true;
            } else {
                return false;
            }
        }

    }

    /**
     * 运行任务
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $work
     * @param $item
     */
    protected function runWorker($work, $item)
    {
        try {
            if ($this->checkWorkerExists($work, $item)) {
                echo "执行[{$work}]任务" . PHP_EOL;
                $work::$item();
                Log::notice("执行[{$work}::{$item}]任务");
            } elseif ($this->checkWorkerExists($work, "run")) {
                echo "执行[{$work}]任务" . PHP_EOL;
                $work::run();
                Log::notice("执行[{$work}::run]任务");
            } else {
                echo "执行[{$work}::{$item}]任务的默认和指定方法都不存在" . PHP_EOL;
                $this->redis->hdel($this->listName, $item);
            }
        } catch (Exception $e) {
            echo "执行[{$work}]任务失败" . PHP_EOL;
            Log::notice($e->getMessage());
            if ($this->pcntl) {
                $this->pcntlKill();
            }
        }
    }


    /**
     * 分进程
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param $work
     * @param $item
     */
    protected function pcntlWorker($work, $item)
    {
        try {
            // 通过pcntl得到一个子进程的PID
            $pid = pcntl_fork();
            if ($pid == -1) {
                // 错误处理：创建子进程失败时返回-1.
                die ('could not fork');
            } else if ($pid) {
                // 父进程逻辑

                // 等待子进程中断，防止子进程成为僵尸进程。
                // WNOHANG为非阻塞进程，具体请查阅pcntl_wait PHP官方文档
                pcntl_wait($status, WNOHANG);
            } else {
                // 子进程逻辑
                $pid_2 = pcntl_fork();
                if ($pid_2 == -1) {
                    // 错误处理：创建子进程失败时返回-1.
                    die ('could not fork');
                } else if ($pid_2) {
                    // 父进程逻辑
                    echo "父进程逻辑开始" . PHP_EOL;
                    // 等待子进程中断，防止子进程成为僵尸进程。
                    // WNOHANG为非阻塞进程，具体请查阅pcntl_wait PHP官方文档
                    pcntl_wait($status, WNOHANG);
                    echo "父进程逻辑结束" . PHP_EOL;
                } else {
                    // 子进程逻辑
                    echo "子进程逻辑开始" . PHP_EOL;

                    $this->runWorker($work, $item);

                    echo "子进程逻辑结束" . PHP_EOL;
                    $this->pcntlKill();
                }
                $this->pcntlKill();
            }
        } catch (Exception $e) {
            Log::error($e->getMessage());
        }
    }

    /**
     * Kill子进程
     * Power: Mikkle
     * Email：776329498@qq.com
     */
    protected function pcntlKill()
    {
        // 为避免僵尸进程，当子进程结束后，手动杀死进程
        if (function_exists("posix_kill")) {
            posix_kill(getmypid(), SIGTERM);
        }
        system('kill -9 ' . getmypid());
        exit ();
    }

    public function signWorking()
    {
        self::redis()->set("command", "true");
        self::redis()->setExpire("command", 30);
    }

    public function sleep($second = "")
    {
        $second = $second ? $second : $this->sleep;
        //  echo "开始睡眠{$second}秒!当前时间:" . date('h:i:s') . PHP_EOL;
        sleep(sleep($second));   //TP5的命令行 sleep($second) 不生效
        echo "睡眠{$second}秒成功!当前时间:" . date('h:i:s') . PHP_EOL;
    }

    /**
     * @return int
     */
    public function getSleep()
    {
        return $this->sleep;
    }

    /**
     * @param  int $sleep
     * @return void
     */
    public function setSleep($sleep)
    {
        $this->sleep = $sleep;
    }

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

}