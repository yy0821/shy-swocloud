<?php
namespace SwoCloud\Server;

use SwoCloud\Server\Traits\AckTrait;
use SwoCloud\Server\Traits\Config;
use Swoole\Server;

abstract class ServerBase
{
    use AckTrait;
    use Config;

    /**
     * @var \Swoole\Server
     */
    protected $server;
    protected $port;
    protected $host;
    protected $config;
    protected $event = [
        "server" => [
            "start"        => "onStart",
            "managerStart" => "onManagerStart",
            "managerStop"  => "onManagerStop",
            "shutdown"     => "onShutdown",
            "workerStart"  => "onWorkerStart",
            "workerStop"   => "onWorkerStop",
            "workerError"  => "onWorkerError",
        ],
        "sub" => [],
        "ext" => []
    ];

    /**
     * 创建服务
     */
    protected abstract function createServer();
    /**
     * 初始化配置
     */
    protected abstract function initConfig();
    /**
     * 初始化回调函数
     */
    protected abstract function initEvent();


    public function __construct($path)
    {
        $this->loadConfig($path);
        $this->initConfig();
        $this->createServer();
        $this->initEvent();
        $this->setSwooleEvent();
    }

    /**
     * 开启服务
     */
    public function start()
    {
        if (empty($this->server)) {
            return "error";
        }
        $this->createTable();
        $this->server->set($this->config);
        $this->server->start();
    }

    /**
     * 绑定回调函数
     */
    protected function setSwooleEvent()
    {
        foreach ($this->event as $type => $events) {
            foreach ($events as $event => $func) {
                $this->server->on($event, [$this, $func]);
            }
        }
    }

    /**
     * 获取回调函数
     * @return array
     */
    public function getEvent(): array
    {
        return $this->event;
    }

    /**
     * 设置扩展回调函数
     * @param $type
     * @param $event
     * @return $this
     */
    public function setEvent($type, $event)
    {
        if ($type == "server") {
            return $this;
        }
        $this->event[$type] = $event;
        return $this;
    }

    public function onStart(Server $server){}
    public function onManagerStart(Server $server){}
    public function onManagerStop(Server $server){}
    public function onShutdown(Server $server){}
    public function onWorkerStart(Server $server, int $worker_id){}
    public function onWorkerStop(Server $server, int $worker_id){}
    public function onWorkerError(Server $server, int $workerId, int $workerPid, int $exitCode, int $signal){}
}