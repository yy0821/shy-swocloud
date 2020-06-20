<?php

namespace SwoCloud\Server;

use SwoCloud\Supper\Arithmetic;
use Swoole\Http\Request;
use Swoole\Http\Response;
use Swoole\WebSocket\Server;
use Firebase\JWT\JWT;

/**
 * 请求分发类
 * @package SwoCloud\Server
 */
class Dispatcher
{
    /**
     * 注册IM-SERVER到redis中保存
     * @param Route $route
     * @param Server $server
     * @param $fd
     * @param $data
     */
    public function register(Route $route,Server $server,$fd,$data)
    {
        $value = json_encode([
            'host' => $data['host'],
            'port' => $data['port']
        ]);
        $key = $route->getKey();
        $route->getRedis()->sadd($key,$value);

        $server->tick(3000,function ($timer_id,Route $route,Server $server,$fd,$key,$value){
            if (!$server->exist($fd)){
                $route->getRedis()->srem($key,$value);
                $server->clearTimer($timer_id);
                dd($key.'宕机了');
            }

        },$route,$server,$fd,$key,$value);
    }

    /**
     * 广播到各服务器
     * @param Route $route
     * @param Server $server
     * @param $fd
     * @param $data
     */
    public function routeBroadcast(Route $route,Server $server,$fd,$data)
    {
        $imServer = $route->getRedis()->sMembers($route->getKey());
        foreach ($imServer as $k=>$v){
            $v = json_decode($v,true);
            $token = $this->getToken(0,$v['host'].':'.$v['port']);
            $header = ['sec-websocket-protocol'=>$token];
            $route->send($v['host'],$v['port'],$data,$header);
        }
    }

    /**
     * 用户登录 记录用户信息
     * @param Route $route
     * @param Request $request
     * @param Response $response
     */
    public function login(Route $route,Request $request,Response $response)
    {
        $imServer = json_decode($this->getImServer($route),true);
        $url = $imServer['host'].':'.$imServer['port'];
        $uid = $request->post['id'];
        $token = $this->getToken($uid,$url);
        $response->end(json_encode(['token'=>$token,'url'=>$url]));
    }

    /**
     * 获取JWT token
     * @param $uid
     * @param $url
     * @return string
     */
    protected function getToken($uid,$url)
    {
        $key = "swoCloud-server";
        $time = time();
        $payload = array(
            "iss" => "http://example.org",
            "aud" => "http://example.com",
            "iat" => $time,
            "nbf" => $time,
            'exq' => $time + (60 * 60 *24),
            'data' => [
                'uid' => $uid,
                'serverUrl' => $url
            ]
        );
        return JWT::encode($payload, $key);
    }

    /**
     * 获取服务器地址
     * @param Route $route
     * @return bool
     */
    protected function getImServer(Route $route)
    {
        $imServer = $route->getRedis()->sMembers($route->getKey());
        if (!empty($imServer)){
            return Arithmetic::{$route->getArithmetic()}($imServer);
        }
        return false;
    }
}