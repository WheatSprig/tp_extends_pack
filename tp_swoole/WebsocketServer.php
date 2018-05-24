<?php
/**
 * Created by PhpStorm.
 * Power By Mikkle
 * Email：776329498@qq.com
 * Date: 2017/06/6
 * Time: 14:07
 */

namespace mikkle\tp_swoole;


class WebsocketServer
{

    protected $server;
    protected $host="0.0.0.0";
    protected $port=9501;
    protected $option=[];

    public function __construct() {
        $this->server = new \swoole_websocket_server($this->host, $this->port);
        $this->server->on('open', function (\swoole_websocket_server $server, \swoole_http_request $request) {
            $this->onOpen($server, $request);
        });
        $this->server->on('message', function (\swoole_websocket_server $server, \swoole_websocket_frame $frame) {
            $this->onMessage($server, $frame);
        });
        //request方法回调函数
        $this->server->on('request', function (\swoole_http_request $request, \swoole_http_response $response) {
            $this->onRequest($this->server,$request,$response);
        });

        $this->server->on('close', function (\swoole_websocket_server $server, $fd) {
            $this->onClose($server, $fd);

        });
        // 设置参数
        if (!empty($this->option)) {
            $this->server->set($this->option);
        }
        $this->initialize($this->server);
        $this->server->start();


    }

    /**
     * 初始化
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param \swoole_websocket_server $server
     */
    protected function initialize(\swoole_websocket_server $server){

    }


    /**
     * 链接成功回调方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param \swoole_websocket_server $server
     * @param \swoole_http_request $request
     */
    protected function onOpen(\swoole_websocket_server $server, \swoole_http_request $request){
        $server->push($request->fd, "你的FD:$request->fd");
        echo "server: handshake success with fd{$request->fd}\n";
    }

    /**
     * 信息回调方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param \swoole_websocket_server $server
     * @param \swoole_websocket_frame $frame
     */
    protected function onMessage(\swoole_websocket_server $server, \swoole_websocket_frame $frame){

        echo "receive from {$frame->fd}:{$frame->data},opcode:{$frame->opcode},fin:{$frame->finish}\n";
        @$server->push($frame->fd, "this is server:{$frame->data}");
    }

    /**
     * request 回调方法
     * Power: Mikkle
     * Email：776329498@qq.com
     * @param \swoole_websocket_server $server
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     */
    protected function onRequest(\swoole_websocket_server $server, \swoole_http_request $request,\swoole_http_response $response){

        // 接收http请求从get获取message参数的值，给用户推送
        // $this->server->connections 遍历所有websocket连接用户的fd，给所有用户推送
        foreach ($server->connections as $fd) {
            @$server->push($fd, $request->get['message']);
        }
    }

    protected function onClose(\swoole_websocket_server $server, $fd){
        $server->close($fd);
        echo "client {$fd} closed\n";
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
        call_user_func_array([$this->server, $method], $args);
    }




}