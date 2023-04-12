<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInit3314c8190c6a2482c6fb3aaed4bfffdd
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'ScssPhp\\ScssPhp\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'ScssPhp\\ScssPhp\\' => 
        array (
            0 => __DIR__ . '/..' . '/scssphp/scssphp/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInit3314c8190c6a2482c6fb3aaed4bfffdd::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInit3314c8190c6a2482c6fb3aaed4bfffdd::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}
