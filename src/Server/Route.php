<?php

namespace SwoCloud\Server;

use Swoole\Coroutine\Http\Client;
use Swoole\WebSocket\Server;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Frame;
use Redis;

/**
 * 1. 检测IM-server的存活状态 (可以根据一定的算法，确定的连接的server，然后返回给客户端，同时做一个心跳的检测)
 * 2. 支持权限认证
 * 3. 根据服务器的状态，按照一定的算法，计算出该客户端连接到哪台IM-server，返回给客户端，客户端再去连接到对应的服务端,保存客户端与IM-server的路由关系
 * 4. 如果 IM-server宕机，会自动从Redis中当中剔除
 * 5. IM-server上线后连接到Route，自动加入Redis
 * 6. 可以接受来自PHP代码、C++程序、Java程序的消息请求，转发给用户所在的IM-server
 * 7. 缓存服务器地址，多次查询redis
 * @package SwoCloud
 */
class Route extends ServerBase
{
    /**@var Redis */
    protected $redis = null;
    protected $dispatcher = null;
    protected $key = 'im_server';
    protected $arithmetic = 'round';

    public function onWorkerStart(\Swoole\Server $server, int $worker_id)
    {
        $this->redis = new Redis();
        $this->redis->pconnect($this->getLoadConfig('database.redis.host'),$this->getLoadConfig('database.redis.port'));
    }

    public function onRequest(Request $request, Response $response)
    {
        if ($request->server['request_uri'] == '/favicon.ico') {
            $response->status(404);
            $response->end();
            return null;
        }
        $response->header('Access-Control-Allow-Origin','*');
        $response->header('Access-Control-Allow-Method','GET,POST');
        $this->getDispatcher()->{$request->post['method']}($this,$request,$response);
    }

    public function onOpen(Server $server, Request $request)
    {
        dd('有连接进来了');
    }

    public function onMessage(Server $server, Frame $frame)
    {
        $data = json_decode($frame->data,true);
        $fd = $frame->fd;
        $this->getDispatcher()->{$data['method']}($this,$server,...[$fd,$data]);
    }

    public function onClose(Server $server, $fd)
    {
        dd('有连接关闭了');
    }

    protected  function initEvent()
    {
        $this->setEvent('sub',[
            'request'   => 'onRequest',
            'open'      => 'onOpen',
            'message'   => 'onMessage',
            'close'     => 'onClose',
        ]);
    }

    protected function initConfig()
    {
        $this->host = $this->getLoadConfig('server.host');
        $this->port = $this->getLoadConfig('server.port');
        $this->config = $this->getLoadConfig('server.config');
    }

    protected  function createServer()
    {
        $this->server = new Server($this->host,$this->port);
    }

    protected function getDispatcher()
    {
        if (empty($this->dispatcher)){
            $this->dispatcher = new Dispatcher();
        }
        return $this->dispatcher;
    }

    public function getRedis()
    {
        return $this->redis;
    }

    public function getKey()
    {
        return $this->key;
    }

    public function getArithmetic()
    {
        return $this->arithmetic;
    }

    public function send($host,$port,$data,$header)
    {
        $uniqueId = session_create_id();

        $cli = new Client($host,$port);
        $cli->setHeaders($header);
        if ($cli->upgrade("/")){
            $cli->push(json_encode([
                'method' => 'routeBroadcast',
                'data' => [
                    'msg' => $data['msg']
                ],
                'msg_id' => $uniqueId
            ]));
        }

        $this->confirmGo($uniqueId,$data,$cli);
    }
}