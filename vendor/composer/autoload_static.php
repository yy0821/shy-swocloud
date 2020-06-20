<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitb7a38792db299cf551ea4c797adf7f26
{
    public static $files = array (
        'e5db511a6c4c4c74eb191289abd97bdb' => __DIR__ . '/../..' . '/src/Supper/Helper.php',
    );

    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'SwoCloud\\' => 9,
        ),
        'F' => 
        array (
            'Firebase\\JWT\\' => 13,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'SwoCloud\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Firebase\\JWT\\' => 
        array (
            0 => __DIR__ . '/..' . '/firebase/php-jwt/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitb7a38792db299cf551ea4c797adf7f26::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitb7a38792db299cf551ea4c797adf7f26::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
