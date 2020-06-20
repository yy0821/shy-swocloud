<?php
namespace SwoCloud\Console;

class Input
{
    /**
     * 测试打印
     * @param $message
     * @param null $description
     */
    public static function info($message, $description = null)
    {
        echo "======>>> ".$description." start\n";
        if (is_array($message)) {
            echo var_export($message, true);
        } else if (is_string($message)) {
            echo $message."\n";
        } else {
            var_dump($message);
        }
        echo  "======>>> ".$description." end\n";
    }
}
