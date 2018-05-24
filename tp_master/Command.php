<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2018/09/19
 * Time: 10:26
 */

namespace mikkle\tp_master;


abstract class Command extends \think\console\Command
{

    protected function execute(\think\console\Input $input, \think\console\Output $output){

        $this->executeHandle($input,  $output);
    }
    abstract protected function executeHandle($input,  $output);
}