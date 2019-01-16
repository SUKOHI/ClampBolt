<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitaf0a203909fb5b3784f17e98c189df2a
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
        'Sukohi\\ClampBolt\\Commands\\AttachmentClearCommand' => __DIR__ . '/../..' . '/src/commands/AttachmentClearCommand.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitaf0a203909fb5b3784f17e98c189df2a::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitaf0a203909fb5b3784f17e98c189df2a::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitaf0a203909fb5b3784f17e98c189df2a::$classMap;

        }, null, ClassLoader::class);
    }
}
