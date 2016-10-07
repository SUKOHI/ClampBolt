<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitf80b04d5e43f224b6d2f02626db59f1c
{
    public static $prefixLengthsPsr4 = array (
        'S' => 
        array (
            'Sukohi\\ClampBolt\\' => 17,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Sukohi\\ClampBolt\\' => 
        array (
            0 => __DIR__ . '/../..' . '/src',
        ),
    );

    public static $classMap = array (
        'Sukohi\\ClampBolt\\App\\Attachment' => __DIR__ . '/../..' . '/src/Attachment.php',
        'Sukohi\\ClampBolt\\ClampBoltServiceProvider' => __DIR__ . '/../..' . '/src/ClampBoltServiceProvider.php',
        'Sukohi\\ClampBolt\\ClampBoltTrait' => __DIR__ . '/../..' . '/src/ClampBoltTrait.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitf80b04d5e43f224b6d2f02626db59f1c::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitf80b04d5e43f224b6d2f02626db59f1c::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitf80b04d5e43f224b6d2f02626db59f1c::$classMap;

        }, null, ClassLoader::class);
    }
}