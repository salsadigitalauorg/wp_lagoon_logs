<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticIniteb150e3b0a29d5eb0f303d866aa78ef0
{
    public static $files = array (
        '5e73ffc188f5a63fbd263c4490731358' => __DIR__ . '/..' . '/inpsyde/wonolog/inc/bootstrap.php',
    );

    public static $prefixLengthsPsr4 = array (
        'w' => 
        array (
            'wp_lagoon_logs\\lagoon_logs\\' => 27,
        ),
        'P' => 
        array (
            'Psr\\Log\\' => 8,
        ),
        'M' => 
        array (
            'Monolog\\' => 8,
        ),
        'I' => 
        array (
            'Inpsyde\\Wonolog\\' => 16,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'wp_lagoon_logs\\lagoon_logs\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
        'Psr\\Log\\' => 
        array (
            0 => __DIR__ . '/..' . '/psr/log/Psr/Log',
        ),
        'Monolog\\' => 
        array (
            0 => __DIR__ . '/..' . '/monolog/monolog/src/Monolog',
        ),
        'Inpsyde\\Wonolog\\' => 
        array (
            0 => __DIR__ . '/..' . '/inpsyde/wonolog/src',
        ),
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticIniteb150e3b0a29d5eb0f303d866aa78ef0::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticIniteb150e3b0a29d5eb0f303d866aa78ef0::$prefixDirsPsr4;

        }, null, ClassLoader::class);
    }
}