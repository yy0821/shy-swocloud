<?php
if (!function_exists('dd'))
{
    /**
     * 打印测试方法
     * @param $message
     * @param null $description
     */
    function dd($message, $description = null)
    {
        \SwoCloud\Console\Input::info($message, $description);
    }
}