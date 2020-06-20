<?php

namespace SwoCloud\Server\Traits;

use Swoole\Coroutine\Http\Client;
use Swoole\Table;
use Co;

/**
 * 消息确认类
 * @package SwoCloud\Server\Traits
 */
trait AckTrait
{
    /**
     * @var Table
     */
    protected $table;

    /**
     * 创建共享内存
     */
    public function createTable()
    {
        $this->table = new Table(1024);
        $this->table->column('ack', Table::TYPE_INT,2);
        $this->table->column('num', Table::TYPE_INT,2);
        $this->table->create();
    }

    /**
     * 消息确认
     * @param $uniqueId
     * @param $data
     * @param Client $client
     */
    public function confirmGo($uniqueId,$data,Client $client)
    {
        go(function () use ($uniqueId,$data,$client){
            while (true){
                Co::sleep(1);

                $ackInfo = json_decode(($client->recv(0.2)->data),true);
                if (isset($ackInfo['method']) && $ackInfo['method'] == 'ack'){
                    $this->table->incr($ackInfo['msg_id'],'ack');
                }

                $task = $this->table->get($uniqueId);

                if ($task['ack'] > 0 || $task['num'] >= 3){
                    $this->table->del($uniqueId);
                    $client->close();
                    break;
                }else{
                    $client->push(json_encode([
                        'method' => 'routeBroadcast',
                        'data' => [
                            'msg' => $data['msg']
                        ],
                        'msg_id' => $uniqueId
                    ]));
                }
                $this->table->incr($uniqueId,'num');
            }
        });
    }
}