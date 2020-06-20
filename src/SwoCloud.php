<?php

namespace SwoCloud;

class SwoCloud
{
    protected const SWOCLOUD_WELCOME = "
      _____                    
     /  __/             ____   
     \__ \  | | /| / / / __ \  
     __/ /  | |/ |/ / / /_/ /  
    /___/   |__/\__/  \____/  
    ";

    public function run($path)
    {
        echo self::SWOCLOUD_WELCOME."\n";
        (new \SwoCloud\Server\Route($path))->start();
    }
}