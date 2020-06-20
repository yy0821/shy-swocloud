<?php

namespace SwoCloud\Supper;

/**
 * 算法类
 * @package SwoCloud\Supper
 */
class Arithmetic
{
    protected static $roundListLast = 0;

    /**
     * 轮巡算法
     * @param array $list
     * @return mixed
     */
    public static function round(array $list)
    {
        $index = self::$roundListLast;
        $url = $list[$index];
        if ($index+1 > count($list)-1){
            self::$roundListLast = 0;
        }else{
            self::$roundListLast++;
        }
        return $url;
    }
}