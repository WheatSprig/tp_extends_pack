<?php
/**
 * Created by PhpStorm.
 * User: Mikkle
 * QQ:776329498
 * Date: 2017/06/13
 * Time: 15:14
 */

namespace mikkle\tp_controller;

use think\Controller;

class ControllerBase extends Controller
{
    protected $error;
    public function addError($error){
        $this->error = is_string($error) ? $error : json_encode($error);
    }
    public function getError(){
        return $this->error;
    }

}