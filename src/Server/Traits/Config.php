<?php

namespace SwoCloud\Server\Traits;

/**
 * 加载配置类
 * @package SwoCloud\Server\Traits
 */
trait Config
{
    public $configPath;
    public $items;

    /**
     * 加载配置
     * @param $path
     */
    public function loadConfig($path)
    {
        $this->configPath = $path.'/config';
        $this->items = $this->loadPHP($this->configPath);
    }

    /**
     * 递归加载配置文件
     * @param $path
     * @return null
     */
    public function loadPHP($path)
    {
        $files = scandir($path);
        $data = null;
        foreach ($files as $key => $file){
            if ($file === '.' || $file === '..'){
                continue;
            }
            if (is_dir($file)){
                $this->loadPHP($file);
            }else{
                $filename = stristr($file,'.php',true);
                $data[$filename] = include $this->configPath."/".$file;
            }
        }
        return $data;
    }

    /**
     * 获取配置
     * @param $keys (server.host)
     * @return mixed
     */
    public function getLoadConfig($keys)
    {
        $data = $this->items;
        foreach (explode('.', $keys) as $key => $value) {
            $data = $data[$value];
        }
        return $data;
    }
}